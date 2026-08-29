<?php

namespace App\Services;

use App\Exceptions\ApprovalRequiredException;
use App\Exceptions\HargaJualBelumDiaturException;
use App\Exceptions\ResepAlreadyDispensedException;
use App\Exceptions\ResepMismatchException;
use App\Exceptions\ResepNotVerifiedException;
use App\Models\BatchObat;
use App\Models\DetailTransaksi;
use App\Models\KartuStok;
use App\Models\Obat;
use App\Models\Resep;
use App\Models\TransaksiPenjualan;
use App\Models\User;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;
use InvalidArgumentException;

/**
 * Orkestrator penjualan. Menggabungkan FEFOService (alokasi & potong stok)
 * dan validasi approval golongan obat (§19) dalam satu transaction atomic.
 *
 * Business logic di sini dipakai identik oleh Livewire kasir (Blade) maupun
 * API nanti (§55) — jangan duplikasi logic checkout di tempat lain.
 *
 * PHASE 6: mekanisme approval (kolom apoteker_approval_id, alur "pilih
 * apoteker saat checkout") TIDAK diganti — tetap sama seperti Phase 4.
 * Yang berubah: kalau transaksi terhubung ke resep_id, apoteker_approval_id
 * di-DERIVE otomatis dari Resep::apoteker_verifikasi_id (resep yang sudah
 * diverifikasi = approval-nya sudah ada), bukan dipilih bebas lagi oleh
 * kasir. Jalur walk-in TANPA resep (mis. golongan keras dijual tanpa resep
 * tercatat di sistem) tetap memakai pilih-apoteker-bebas seperti sebelumnya
 * — ini REGULATORY ASSUMPTION, TO BE VERIFIED apakah praktik ini masih mau
 * dipertahankan atau harus diwajibkan selalu via resep.
 */
class PenjualanService
{
    public function __construct(
        private readonly FEFOService $fefoService,
        private readonly StockService $stockService,
    ) {}

    /**
     * @param array<int, array{obat_id: int, jumlah: int}> $items
     */
    public function createSale(
        array $items,
        string $metodeBayar,
        int $kasirId,
        ?float $jumlahBayar = null,
        ?int $apotekerApprovalId = null,
        ?int $resepId = null,
        ?int $pasienId = null,
    ): TransaksiPenjualan {
        if (empty($items)) {
            throw new InvalidArgumentException('Transaksi harus memiliki minimal 1 item.');
        }

        return DB::transaction(function () use ($items, $metodeBayar, $kasirId, $jumlahBayar, $apotekerApprovalId, $resepId, $pasienId) {
            $obatList = Obat::whereIn('id', collect($items)->pluck('obat_id'))->get()->keyBy('id');

            $resep = null;
            if ($resepId) {
                // lockForUpdate: cegah dua transaksi konkuren dispensing dari
                // resep yang sama sampai sisa jumlah_diresepkan jadi negatif.
                $resep = Resep::with('detail.obat')->lockForUpdate()->findOrFail($resepId);
                $this->assertResepUsable($resep);
                $pasienId = $pasienId ?? $resep->pasien_id;
                $apotekerApprovalId = $resep->apoteker_verifikasi_id;
            }

            $this->assertApprovalIfNeeded($obatList, $items, $apotekerApprovalId, $resep);

            // Header dibuat dulu (total=0 sementara) supaya setiap baris
            // kartu_stok yang ditulis FEFOService punya referensi transaksi
            // yang benar sejak awal — bukan di-patch belakangan.
            $transaksi = TransaksiPenjualan::create([
                'no_transaksi' => $this->generateNoTransaksi(),
                'resep_id' => $resepId,
                'pasien_id' => $pasienId,
                'kasir_id' => $kasirId,
                'apoteker_approval_id' => $apotekerApprovalId,
                'total' => 0,
                'metode_bayar' => $metodeBayar,
                'jumlah_bayar' => $jumlahBayar,
                'kembalian' => null,
                'status' => TransaksiPenjualan::STATUS_SELESAI,
            ]);

            $total = 0.0;
            $dispensedByObat = [];

            foreach ($items as $item) {
                $allocations = $this->fefoService->deduct(
                    obatId: $item['obat_id'],
                    jumlahDiminta: $item['jumlah'],
                    jenisTransaksi: KartuStok::JENIS_KELUAR_JUAL,
                    userId: $kasirId,
                    referensiType: TransaksiPenjualan::class,
                    referensiId: $transaksi->id,
                );

                foreach ($allocations as $allocation) {
                    $batch = BatchObat::findOrFail($allocation['batch_id']);

                    if ($batch->harga_jual === null) {
                        throw HargaJualBelumDiaturException::forBatch($batch->no_batch);
                    }

                    $subtotal = $allocation['jumlah'] * (float) $batch->harga_jual;
                    $total += $subtotal;

                    DetailTransaksi::create([
                        'transaksi_id' => $transaksi->id,
                        'obat_id' => $item['obat_id'],
                        'batch_id' => $batch->id,
                        'jumlah' => $allocation['jumlah'],
                        'harga_satuan' => $batch->harga_jual,
                        'subtotal' => $subtotal,
                    ]);
                }

                $dispensedByObat[$item['obat_id']] = ($dispensedByObat[$item['obat_id']] ?? 0) + $item['jumlah'];
            }

            if ($metodeBayar === 'tunai') {
                if ($jumlahBayar === null || $jumlahBayar < $total) {
                    throw new InvalidArgumentException('Jumlah bayar tunai tidak mencukupi total transaksi.');
                }
            }

            $transaksi->update([
                'total' => $total,
                'kembalian' => $jumlahBayar !== null ? max(0, $jumlahBayar - $total) : null,
            ]);

            if ($resep) {
                $this->applyResepDispensing($resep, $dispensedByObat);
            }

            return $transaksi->fresh(['detail.obat', 'detail.batch', 'kasir', 'resep']);
        });
    }

    /**
     * Void/pembatalan transaksi — TIDAK PERNAH DELETE (§71). Stok
     * dikembalikan ke batch ASAL masing-masing (bukan batch FEFO
     * "saat ini"), dicatat sebagai kartu_stok jenis retur.
     *
     * PHASE 6: kalau transaksi ini terhubung ke resep, jumlah_terlayani di
     * detail_resep juga dikembalikan (dan status resep yang sudah 'selesai'
     * turun lagi ke 'terverifikasi') — supaya resep bisa dipakai dispensing
     * ulang setelah void, bukan macet di status 'selesai' padahal stoknya
     * sudah balik.
     */
    public function voidSale(TransaksiPenjualan $transaksi, int $userId, string $alasan): TransaksiPenjualan
    {
        if ($transaksi->status === TransaksiPenjualan::STATUS_DIBATALKAN) {
            throw new InvalidArgumentException('Transaksi ini sudah dibatalkan sebelumnya.');
        }

        return DB::transaction(function () use ($transaksi, $userId, $alasan) {
            $dispensedByObat = [];

            foreach ($transaksi->detail as $detail) {
                $this->stockService->receiveStock(
                    data: [
                        'obat_id' => $detail->obat_id,
                        'supplier_id' => $detail->batch->supplier_id,
                        'no_batch' => $detail->batch->no_batch,
                        'tanggal_produksi' => $detail->batch->tanggal_produksi?->toDateString(),
                        'tanggal_kadaluarsa' => $detail->batch->tanggal_kadaluarsa->toDateString(),
                        'harga_beli' => $detail->batch->harga_beli,
                        'jumlah' => $detail->jumlah,
                    ],
                    userId: $userId,
                    referensiType: TransaksiPenjualan::class,
                    referensiId: $transaksi->id,
                    keterangan: "Pembatalan transaksi {$transaksi->no_transaksi}: {$alasan}",
                    jenisTransaksi: KartuStok::JENIS_RETUR,
                );

                $dispensedByObat[$detail->obat_id] = ($dispensedByObat[$detail->obat_id] ?? 0) + $detail->jumlah;
            }

            $transaksi->update([
                'status' => TransaksiPenjualan::STATUS_DIBATALKAN,
                'alasan_pembatalan' => $alasan,
            ]);

            if ($transaksi->resep_id) {
                $resep = Resep::with('detail')->lockForUpdate()->find($transaksi->resep_id);
                if ($resep) {
                    $this->reverseResepDispensing($resep, $dispensedByObat);
                }
            }

            return $transaksi->fresh('detail');
        });
    }

    /**
     * @param Collection<int, Obat> $obatList
     * @param array<int, array{obat_id: int, jumlah: int}> $items
     */
    private function assertApprovalIfNeeded(Collection $obatList, array $items, ?int $apotekerApprovalId, ?Resep $resep): void
    {
        $restrictedItems = collect($items)->filter(function ($item) use ($obatList) {
            $obat = $obatList->get($item['obat_id']);

            return $obat && $obat->requiresApprovalGolongan();
        })->values();

        if ($restrictedItems->isEmpty()) {
            return;
        }

        // Kalau resep dipakai, setiap item golongan-restricted WAJIB ada di
        // detail_resep dan tidak melebihi sisa — mencegah approval resep
        // "dipinjam" untuk item lain yang tidak diresepkan.
        if ($resep) {
            foreach ($restrictedItems as $item) {
                $obat = $obatList->get($item['obat_id']);
                $detailLine = $resep->detail->firstWhere('obat_id', $item['obat_id']);

                if (!$detailLine) {
                    throw ResepMismatchException::obatTidakDiresepkan($obat->nama_obat);
                }

                if ($item['jumlah'] > $detailLine->sisaDiresepkan()) {
                    throw ResepMismatchException::melebihiSisaResep($obat->nama_obat, $detailLine->sisaDiresepkan());
                }
            }
        }

        if (!$apotekerApprovalId) {
            $namaObatPertama = $obatList->get($restrictedItems->first()['obat_id'])->nama_obat;
            throw ApprovalRequiredException::forObat($namaObatPertama);
        }

        $apoteker = User::find($apotekerApprovalId);
        if (!$apoteker || !$apoteker->hasRole('apoteker')) {
            throw ApprovalRequiredException::invalidApoteker();
        }
    }

    private function assertResepUsable(Resep $resep): void
    {
        if ($resep->status === Resep::STATUS_SELESAI) {
            throw ResepAlreadyDispensedException::forResep($resep->no_resep);
        }

        if ($resep->status !== Resep::STATUS_TERVERIFIKASI) {
            throw ResepNotVerifiedException::forResep($resep->no_resep, $resep->status);
        }
    }

    /**
     * @param array<int, int> $dispensedByObat obat_id => jumlah terjual di transaksi ini
     */
    private function applyResepDispensing(Resep $resep, array $dispensedByObat): void
    {
        foreach ($resep->detail as $line) {
            $jumlah = $dispensedByObat[$line->obat_id] ?? 0;

            if ($jumlah > 0) {
                $line->update(['jumlah_terlayani' => $line->jumlah_terlayani + $jumlah]);
            }
        }

        $resep->load('detail');

        if ($resep->isFullyDispensed()) {
            $resep->update(['status' => Resep::STATUS_SELESAI]);
        }
    }

    /**
     * @param array<int, int> $dispensedByObat obat_id => jumlah yang dibatalkan
     */
    private function reverseResepDispensing(Resep $resep, array $dispensedByObat): void
    {
        foreach ($resep->detail as $line) {
            $jumlah = $dispensedByObat[$line->obat_id] ?? 0;

            if ($jumlah > 0) {
                $line->update(['jumlah_terlayani' => max(0, $line->jumlah_terlayani - $jumlah)]);
            }
        }

        if ($resep->status === Resep::STATUS_SELESAI) {
            $resep->update(['status' => Resep::STATUS_TERVERIFIKASI]);
        }
    }

    private function generateNoTransaksi(): string
    {
        $today = now()->format('Ymd');
        $prefix = "TRX-{$today}-";

        $last = TransaksiPenjualan::where('no_transaksi', 'like', "{$prefix}%")
            ->lockForUpdate()
            ->orderByDesc('id')
            ->value('no_transaksi');

        $nextNumber = $last ? ((int) substr($last, -4)) + 1 : 1;

        return $prefix . sprintf('%04d', $nextNumber);
    }
}
