<?php

namespace App\Services;

use App\Models\DetailPenerimaan;
use App\Models\DetailPo;
use App\Models\PenerimaanBarang;
use App\Models\PurchaseOrder;
use Illuminate\Support\Facades\DB;
use InvalidArgumentException;

/**
 * Menjembatani dokumen PO/faktur supplier dengan stok fisik. Tidak menulis
 * stok/kartu_stok sendiri — semuanya didelegasikan ke StockService (reuse
 * Phase 2, §77 "jangan duplikasi") supaya penerimaan lewat PO dan Stok
 * Masuk manual selalu berperilaku identik.
 */
class PenerimaanBarangService
{
    public function __construct(private readonly StockService $stockService) {}

    /**
     * @param array{po_id: ?int, supplier_id: int, tanggal_terima: string, no_faktur_supplier: ?string} $data
     * @param array<int, array{obat_id: int, no_batch: string, tanggal_produksi: ?string, tanggal_kadaluarsa: string, harga_beli: float, jumlah: int}> $items
     */
    public function receive(array $data, array $items, int $userId): PenerimaanBarang
    {
        if (empty($items)) {
            throw new InvalidArgumentException('Penerimaan harus memiliki minimal 1 item.');
        }

        return DB::transaction(function () use ($data, $items, $userId) {
            $po = $data['po_id'] ? PurchaseOrder::lockForUpdate()->findOrFail($data['po_id']) : null;

            if ($po && in_array($po->status, [PurchaseOrder::STATUS_SELESAI, PurchaseOrder::STATUS_BATAL], true)) {
                throw new InvalidArgumentException("PO berstatus '{$po->status}' tidak bisa menerima barang lagi.");
            }

            $penerimaan = PenerimaanBarang::create([
                'po_id' => $po?->id,
                'tanggal_terima' => $data['tanggal_terima'],
                'no_faktur_supplier' => $data['no_faktur_supplier'] ?? null,
                'user_id' => $userId,
            ]);

            foreach ($items as $item) {
                $batch = $this->stockService->receiveStock(
                    data: [
                        'obat_id' => $item['obat_id'],
                        'supplier_id' => $data['supplier_id'],
                        'no_batch' => $item['no_batch'],
                        'tanggal_produksi' => $item['tanggal_produksi'] ?? null,
                        'tanggal_kadaluarsa' => $item['tanggal_kadaluarsa'],
                        'harga_beli' => $item['harga_beli'],
                        'jumlah' => $item['jumlah'],
                    ],
                    userId: $userId,
                    referensiType: PenerimaanBarang::class,
                    referensiId: $penerimaan->id,
                    keterangan: $data['no_faktur_supplier'] ? "Faktur {$data['no_faktur_supplier']}" : null,
                );

                DetailPenerimaan::create([
                    'penerimaan_id' => $penerimaan->id,
                    'obat_id' => $item['obat_id'],
                    'no_batch' => $item['no_batch'],
                    'tanggal_produksi' => $item['tanggal_produksi'] ?? null,
                    'tanggal_kadaluarsa' => $item['tanggal_kadaluarsa'],
                    'harga_beli' => $item['harga_beli'],
                    'jumlah' => $item['jumlah'],
                    'batch_id' => $batch->id,
                ]);

                if ($po) {
                    $this->applyToPoLine($po, $item['obat_id'], $item['jumlah']);
                }
            }

            if ($po) {
                $this->refreshPoStatus($po);
            }

            return $penerimaan->fresh('detail.obat', 'detail.batch');
        });
    }

    private function applyToPoLine(PurchaseOrder $po, int $obatId, int $jumlahDiterima): void
    {
        $detailPo = DetailPo::where('po_id', $po->id)
            ->where('obat_id', $obatId)
            ->lockForUpdate()
            ->first();

        if (!$detailPo) {
            // Obat diterima tapi tidak ada di baris PO — bisa terjadi kalau
            // supplier mengirim item tambahan. Diperbolehkan (tidak block),
            // tapi tidak ikut memengaruhi status "selesai" PO karena bukan
            // bagian dari rencana pembelian.
            return;
        }

        $detailPo->update([
            'jumlah_diterima' => $detailPo->jumlah_diterima + $jumlahDiterima,
        ]);
    }

    private function refreshPoStatus(PurchaseOrder $po): void
    {
        $po->load('detail');

        $semuaTerpenuhi = $po->detail->every(fn (DetailPo $d) => $d->jumlah_diterima >= $d->jumlah_order);
        $adaYangDiterima = $po->detail->contains(fn (DetailPo $d) => $d->jumlah_diterima > 0);

        $status = match (true) {
            $semuaTerpenuhi => PurchaseOrder::STATUS_SELESAI,
            $adaYangDiterima => PurchaseOrder::STATUS_DITERIMA_SEBAGIAN,
            default => $po->status,
        };

        if ($status !== $po->status) {
            $po->update(['status' => $status]);
        }
    }
}
