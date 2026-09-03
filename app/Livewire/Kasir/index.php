<?php

namespace App\Livewire\Kasir;

use App\Models\Obat;
use App\Models\BatchObat;
use App\Services\PenjualanService;
use Livewire\Component;
use Livewire\Attributes\Computed;

class Index extends Component
{
    // ==== Search & product list ====
    public string $search = '';

    // ==== Cart (session-independent, kept in component state) ====
    public array $cart = []; // [ obat_id => ['nama', 'batch_id', 'qty', 'harga', 'stok_tersedia', 'kadaluarsa'] ]

    public string $metodeBayar = 'tunai';
    public ?float $jumlahBayar = null;

    /**
     * ================================================================
     * N+1 FIX #1: Product search
     * ----------------------------------------------------------------
     * Original problem (typical pattern that causes N+1):
     *   Obat::where('nama', 'like', "%$search%")->get()
     *   then in blade: foreach ($obats as $obat) { $obat->batchObat->sum('stok') }
     *   -> triggers 1 query per row.
     *
     * Fix: eager-load only the columns needed, and pre-aggregate stock
     * with a single subquery/withSum instead of N lazy-loaded relations.
     * ================================================================
     */
    #[Computed]
    public function hasilPencarian()
    {
        if (strlen($this->search) < 2) {
            return collect();
        }

        return Obat::query()
            ->select(['id', 'nama', 'kode', 'satuan_dasar_id', 'harga_default'])
            ->with(['satuanDasar:id,nama']) // eager load, avoid N+1 on ->satuanDasar->nama in blade
            ->withSum(['batchObat as stok_tersedia' => function ($q) {
                // TO BE VERIFIED: apakah batch expired tetap dihitung sbg stok tersedia
                // di layar kasir, atau harus dikecualikan otomatis di sini juga
                // (FEFOService sudah exclude saat deduksi, tapi tampilan kasir
                // idealnya konsisten dgn apa yg benar2 bisa dijual).
                $q->where('tanggal_kadaluarsa', '>', now());
            }], 'jumlah')
            ->where(function ($q) {
                $q->where('nama', 'like', "%{$this->search}%")
                  ->orWhere('kode', 'like', "%{$this->search}%");
            })
            ->orderBy('nama')
            ->limit(20) // cap result set, don't dump entire obat table into DOM
            ->get();
    }

    /**
     * ================================================================
     * N+1 FIX #2: Adding to cart
     * ----------------------------------------------------------------
     * Batch selection must still respect FEFO. Instead of lazy-loading
     * $obat->batchObat inside a loop, fetch the FEFO-eligible batch in
     * one targeted query when the item is actually added.
     * ================================================================
     */
    public function tambahKeCart(int $obatId): void
    {
        $obat = Obat::select(['id', 'nama', 'satuan_dasar_id', 'harga_default'])
            ->findOrFail($obatId);

        // FEFO: batch dgn kadaluarsa terdekat yg masih punya stok > 0
        $batch = BatchObat::select(['id', 'obat_id', 'no_batch', 'jumlah', 'harga_jual', 'tanggal_kadaluarsa'])
            ->where('obat_id', $obatId)
            ->where('jumlah', '>', 0)
            ->where('tanggal_kadaluarsa', '>', now())
            ->orderBy('tanggal_kadaluarsa')
            ->first();

        if (!$batch) {
            $this->dispatch('toast', type: 'error', message: "Stok {$obat->nama} habis.");
            return;
        }

        $key = $obat->id;

        if (isset($this->cart[$key])) {
            if ($this->cart[$key]['qty'] + 1 > $batch->jumlah) {
                $this->dispatch('toast', type: 'error', message: "Sisa stok hanya {$batch->jumlah}.");
                return;
            }
            $this->cart[$key]['qty']++;
        } else {
            $this->cart[$key] = [
                'obat_id'   => $obat->id,
                'nama'      => $obat->nama,
                'batch_id'  => $batch->id,
                'no_batch'  => $batch->no_batch,
                'qty'       => 1,
                'harga'     => $batch->harga_jual,
                'stok_tersedia' => $batch->jumlah,
                'kadaluarsa'    => $batch->tanggal_kadaluarsa->format('d/m/Y'),
            ];
        }

        // Reset search after add so kasir langsung bisa cari obat berikutnya
        $this->search = '';
    }

    public function ubahQty(int $obatId, int $delta): void
    {
        if (!isset($this->cart[$obatId])) return;

        $newQty = $this->cart[$obatId]['qty'] + $delta;

        if ($newQty <= 0) {
            unset($this->cart[$obatId]);
            return;
        }

        if ($newQty > $this->cart[$obatId]['stok_tersedia']) {
            $this->dispatch('toast', type: 'error', message: 'Melebihi stok tersedia.');
            return;
        }

        $this->cart[$obatId]['qty'] = $newQty;
    }

    public function hapusDariCart(int $obatId): void
    {
        unset($this->cart[$obatId]);
    }

    #[Computed]
    public function totalBelanja(): float
    {
        return collect($this->cart)->sum(fn ($item) => $item['harga'] * $item['qty']);
    }

    #[Computed]
    public function kembalian(): float
    {
        return max(0, ($this->jumlahBayar ?? 0) - $this->totalBelanja);
    }

    public function proses(PenjualanService $service): void
    {
        if (empty($this->cart)) {
            $this->dispatch('toast', type: 'error', message: 'Keranjang kosong.');
            return;
        }

        if (($this->jumlahBayar ?? 0) < $this->totalBelanja) {
            $this->dispatch('toast', type: 'error', message: 'Jumlah bayar kurang.');
            return;
        }

        $penjualan = $service->prosesPenjualanKasir(
            items: collect($this->cart)->values()->all(),
            metodeBayar: $this->metodeBayar,
            jumlahBayar: $this->jumlahBayar,
        );

        $this->cart = [];
        $this->jumlahBayar = null;

        $this->redirect(route('kasir.struk', $penjualan->id), navigate: true);
    }

    public function render()
    {
        return view('livewire.kasir.index');
    }
}
