<?php

namespace App\Services;

use App\Models\BatchObat;
use App\Models\KartuStok;
use App\Models\Obat;
use App\Models\Supplier;
use Illuminate\Support\Facades\DB;
use InvalidArgumentException;

/**
 * Satu-satunya layer yang boleh mengubah BatchObat::stok_saat_ini atau
 * menulis KartuStok. Jangan mutasi stok langsung dari Controller/Livewire.
 *
 * Setiap method mengunci baris batch (lockForUpdate) di dalam DB::transaction
 * untuk mencegah race condition ketika dua kasir/proses menyentuh batch yang
 * sama bersamaan (§8).
 */
class StockService
{
    /**
     * Terima stok masuk untuk sebuah batch. Jika no_batch untuk obat ini
     * sudah ada, tambahkan ke stok batch tersebut (bukan membuat baris baru)
     * — ini menjaga traceability no_batch tetap 1:1 per obat.
     *
     * @param array{
     *   obat_id: int, supplier_id: int, no_batch: string,
     *   tanggal_produksi: ?string, tanggal_kadaluarsa: string,
     *   harga_beli: float, jumlah: int
     * } $data
     */
    public function receiveStock(array $data, int $userId, ?string $referensiType = null, ?int $referensiId = null, ?string $keterangan = null, string $jenisTransaksi = KartuStok::JENIS_MASUK_PEMBELIAN): BatchObat
    {
        if ($data['jumlah'] <= 0) {
            throw new InvalidArgumentException('Jumlah stok masuk harus lebih dari 0.');
        }

        return DB::transaction(function () use ($data, $userId, $referensiType, $referensiId, $keterangan, $jenisTransaksi) {
            $batch = BatchObat::where('obat_id', $data['obat_id'])
                ->where('no_batch', $data['no_batch'])
                ->lockForUpdate()
                ->first();

            $saldoSebelum = $batch->stok_saat_ini ?? 0;

            if ($batch) {
                // KEPUTUSAN BISNIS (dikonfirmasi Alief): jika no_batch yang sama
                // diterima lagi dengan harga_beli berbeda, harga_beli di-update
                // mengikuti harga pembelian TERAKHIR (bukan rata-rata tertimbang,
                // bukan baris baru). Konsekuensinya: harga_beli batch ini hanya
                // merepresentasikan harga pembelian paling baru, bukan histori
                // penuh — kalau butuh histori harga per penerimaan, itu akan
                // datang dari tabel detail_penerimaan di Phase 5 (Purchasing),
                // bukan dari batch_obat.
                $batch->update([
                    'stok_awal' => $batch->stok_awal + $data['jumlah'],
                    'stok_saat_ini' => $batch->stok_saat_ini + $data['jumlah'],
                    'harga_beli' => $data['harga_beli'],
                    'status' => BatchObat::STATUS_AKTIF,
                ]);
            } else {
                $batch = BatchObat::create([
                    'obat_id' => $data['obat_id'],
                    'supplier_id' => $data['supplier_id'],
                    'no_batch' => $data['no_batch'],
                    'tanggal_produksi' => $data['tanggal_produksi'] ?? null,
                    'tanggal_kadaluarsa' => $data['tanggal_kadaluarsa'],
                    'harga_beli' => $data['harga_beli'],
                    'harga_jual' => null, // dihitung HJAService di Phase 3
                    'stok_awal' => $data['jumlah'],
                    'stok_saat_ini' => $data['jumlah'],
                    'status' => BatchObat::STATUS_AKTIF,
                ]);
            }

            $this->writeKartuStok(
                obatId: $data['obat_id'],
                batchId: $batch->id,
                jenis: $jenisTransaksi,
                jumlah: $data['jumlah'], // positif
                saldoSebelum: $saldoSebelum,
                userId: $userId,
                referensiType: $referensiType,
                referensiId: $referensiId,
                keterangan: $keterangan,
            );

            return $batch->fresh();
        });
    }

    /**
     * Kurangi stok dari SATU batch tertentu. Dipakai oleh FEFOService (yang
     * sudah menentukan batch mana + berapa jumlah) — jangan panggil langsung
     * dari kode penjualan tanpa lewat FEFOService, supaya urutan FEFO selalu
     * konsisten.
     */
    public function deductFromBatch(
        int $batchId,
        int $jumlah,
        string $jenisTransaksi,
        int $userId,
        ?string $referensiType = null,
        ?int $referensiId = null,
        ?string $keterangan = null,
    ): BatchObat {
        if ($jumlah <= 0) {
            throw new InvalidArgumentException('Jumlah pengurangan harus lebih dari 0.');
        }

        return DB::transaction(function () use ($batchId, $jumlah, $jenisTransaksi, $userId, $referensiType, $referensiId, $keterangan) {
            $batch = BatchObat::lockForUpdate()->findOrFail($batchId);

            if ($batch->stok_saat_ini < $jumlah) {
                throw \App\Exceptions\InsufficientStockException::forObat($batch->obat_id, $jumlah, $batch->stok_saat_ini);
            }

            $saldoSebelum = $batch->stok_saat_ini;
            $stokBaru = $batch->stok_saat_ini - $jumlah;

            $batch->update([
                'stok_saat_ini' => $stokBaru,
                'status' => $stokBaru === 0 ? BatchObat::STATUS_HABIS : $batch->status,
            ]);

            $this->writeKartuStok(
                obatId: $batch->obat_id,
                batchId: $batch->id,
                jenis: $jenisTransaksi,
                jumlah: -$jumlah, // negatif — stok berkurang
                saldoSebelum: $saldoSebelum,
                userId: $userId,
                referensiType: $referensiType,
                referensiId: $referensiId,
                keterangan: $keterangan,
            );

            return $batch->fresh();
        });
    }

    /**
     * Penyesuaian stok manual ke jumlah target (bukan delta), misalnya hasil
     * stok opname. Selisih dihitung otomatis dan wajib disertai alasan.
     */
    public function adjustStock(int $batchId, int $stokFisikBaru, int $userId, string $alasan): BatchObat
    {
        if ($stokFisikBaru < 0) {
            throw new InvalidArgumentException('Stok tidak boleh negatif.');
        }

        return DB::transaction(function () use ($batchId, $stokFisikBaru, $userId, $alasan) {
            $batch = BatchObat::lockForUpdate()->findOrFail($batchId);

            $saldoSebelum = $batch->stok_saat_ini;
            $delta = $stokFisikBaru - $saldoSebelum;

            if ($delta === 0) {
                return $batch; // tidak ada perubahan, tidak perlu catat kartu stok kosong
            }

            $batch->update([
                'stok_saat_ini' => $stokFisikBaru,
                'status' => $stokFisikBaru === 0 ? BatchObat::STATUS_HABIS : BatchObat::STATUS_AKTIF,
            ]);

            $this->writeKartuStok(
                obatId: $batch->obat_id,
                batchId: $batch->id,
                jenis: KartuStok::JENIS_PENYESUAIAN,
                jumlah: $delta,
                saldoSebelum: $saldoSebelum,
                userId: $userId,
                keterangan: $alasan,
            );

            return $batch->fresh();
        });
    }

    /**
     * Write-off seluruh sisa stok batch yang sudah expired. Dipanggil oleh
     * ExpiryService (scheduler) — tidak dimaksudkan dipanggil manual dari UI
     * kecuali untuk kasus koreksi oleh Owner/Admin.
     */
    public function writeOffExpired(int $batchId, int $userId, ?string $keterangan = null): BatchObat
    {
        return DB::transaction(function () use ($batchId, $userId, $keterangan) {
            $batch = BatchObat::lockForUpdate()->findOrFail($batchId);

            if ($batch->stok_saat_ini === 0) {
                $batch->update(['status' => BatchObat::STATUS_EXPIRED]);
                return $batch->fresh();
            }

            $saldoSebelum = $batch->stok_saat_ini;

            $batch->update([
                'stok_saat_ini' => 0,
                'status' => BatchObat::STATUS_EXPIRED,
            ]);

            $this->writeKartuStok(
                obatId: $batch->obat_id,
                batchId: $batch->id,
                jenis: KartuStok::JENIS_EXPIRED_WRITEOFF,
                jumlah: -$saldoSebelum,
                saldoSebelum: $saldoSebelum,
                userId: $userId,
                keterangan: $keterangan ?? 'Write-off otomatis: batch melewati tanggal kadaluarsa.',
            );

            return $batch->fresh();
        });
    }

    private function writeKartuStok(
        int $obatId,
        int $batchId,
        string $jenis,
        int $jumlah,
        int $saldoSebelum,
        int $userId,
        ?string $referensiType = null,
        ?int $referensiId = null,
        ?string $keterangan = null,
    ): KartuStok {
        return KartuStok::create([
            'obat_id' => $obatId,
            'batch_id' => $batchId,
            'jenis_transaksi' => $jenis,
            'jumlah' => $jumlah,
            'saldo_sebelum' => $saldoSebelum,
            'saldo_sesudah' => $saldoSebelum + $jumlah,
            'referensi_id' => $referensiId,
            'referensi_type' => $referensiType,
            'user_id' => $userId,
            'keterangan' => $keterangan,
        ]);
    }
}
