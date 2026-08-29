<?php

namespace App\Services;

use App\Models\DetailPo;
use App\Models\PurchaseOrder;
use Illuminate\Support\Facades\DB;
use InvalidArgumentException;

class PurchaseOrderService
{
    /**
     * @param array{supplier_id: int, tanggal_po: string} $data
     * @param array<int, array{obat_id: int, jumlah_order: int, harga_satuan: float}> $items
     */
    public function create(array $data, array $items, int $userId): PurchaseOrder
    {
        if (empty($items)) {
            throw new InvalidArgumentException('PO harus memiliki minimal 1 item.');
        }

        return DB::transaction(function () use ($data, $items, $userId) {
            $total = collect($items)->sum(fn ($item) => $item['jumlah_order'] * $item['harga_satuan']);

            $po = PurchaseOrder::create([
                'no_po' => $this->generateNoPo(),
                'supplier_id' => $data['supplier_id'],
                'tanggal_po' => $data['tanggal_po'],
                'status' => PurchaseOrder::STATUS_DRAFT,
                'total' => $total,
                'user_id' => $userId,
            ]);

            foreach ($items as $item) {
                DetailPo::create([
                    'po_id' => $po->id,
                    'obat_id' => $item['obat_id'],
                    'jumlah_order' => $item['jumlah_order'],
                    'jumlah_diterima' => 0,
                    'harga_satuan' => $item['harga_satuan'],
                ]);
            }

            return $po->fresh('detail.obat');
        });
    }

    public function kirim(PurchaseOrder $po): PurchaseOrder
    {
        $this->assertStatus($po, PurchaseOrder::STATUS_DRAFT, 'dikirim');

        $po->update(['status' => PurchaseOrder::STATUS_DIKIRIM]);

        return $po->fresh();
    }

    /**
     * PO hanya boleh dibatalkan sebelum ada penerimaan barang apa pun —
     * begitu status sudah diterima_sebagian/selesai, stok sudah bergerak
     * dan pembatalan dokumen PO tidak lagi berarti apa-apa untuk stok
     * (retur harus lewat mekanisme retur, bukan batal PO — §72).
     */
    public function batalkan(PurchaseOrder $po): PurchaseOrder
    {
        if (in_array($po->status, [PurchaseOrder::STATUS_DITERIMA_SEBAGIAN, PurchaseOrder::STATUS_SELESAI], true)) {
            throw new InvalidArgumentException('PO yang sudah menerima barang tidak bisa dibatalkan. Gunakan mekanisme retur jika perlu.');
        }

        $po->update(['status' => PurchaseOrder::STATUS_BATAL]);

        return $po->fresh();
    }

    private function assertStatus(PurchaseOrder $po, string $expected, string $actionLabel): void
    {
        if ($po->status !== $expected) {
            throw new InvalidArgumentException("PO berstatus '{$po->status}' tidak bisa di-{$actionLabel}.");
        }
    }

    private function generateNoPo(): string
    {
        $today = now()->format('Ymd');
        $prefix = "PO-{$today}-";

        $last = PurchaseOrder::where('no_po', 'like', "{$prefix}%")
            ->lockForUpdate()
            ->orderByDesc('id')
            ->value('no_po');

        $nextNumber = $last ? ((int) substr($last, -4)) + 1 : 1;

        return $prefix . sprintf('%04d', $nextNumber);
    }
}
