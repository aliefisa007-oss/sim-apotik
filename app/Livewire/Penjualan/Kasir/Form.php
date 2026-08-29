<?php

namespace App\Livewire\Penjualan\Kasir;

use App\Exceptions\ApprovalRequiredException;
use App\Exceptions\HargaJualBelumDiaturException;
use App\Exceptions\InsufficientStockException;
use App\Exceptions\ResepAlreadyDispensedException;
use App\Exceptions\ResepMismatchException;
use App\Exceptions\ResepNotVerifiedException;
use App\Models\Obat;
use App\Models\Resep;
use App\Models\User;
use App\Services\PenjualanService;
use InvalidArgumentException;
use Livewire\Component;

class Form extends Component
{
    public string $search = '';

    /** @var array<int, array{id:int,kode_obat:string,nama_obat:string,golongan:string}> */
    public array $searchResults = [];

    /**
     * Keranjang belanja. jumlah = kuantitas satuan dasar; harga_estimasi &
     * subtotal_estimasi hanya untuk tampilan (harga final tetap dihitung
     * ulang server-side per batch oleh PenjualanService::createSale saat
     * checkout — cart di sini bukan sumber kebenaran harga).
     *
     * @var array<int, array{obat_id:int, kode_obat:string, nama_obat:string, golongan:string, jumlah:int, harga_estimasi:float}>
     */
    public array $cart = [];

    public string $metodeBayar = 'tunai';
    public ?float $jumlahBayar = null;
    public ?int $apotekerApprovalId = null;

    public bool $showPaymentModal = false;

    // Resep terpilih (Phase 6). Kalau terisi, PenjualanService otomatis
    // pakai apoteker_verifikasi_id resep sebagai approval — kasir TIDAK
    // pilih apoteker manual lagi untuk item yang cocok dengan resep ini.
    public ?int $resepId = null;
    public string $resepNoTampil = '';
    public string $resepPasienTampil = '';

    public string $resepSearch = '';

    /** @var array<int, array{id:int,no_resep:string,pasien_nama:string}> */
    public array $resepResults = [];

    public function updatedSearch(): void
    {
        if (strlen($this->search) < 2) {
            $this->searchResults = [];
            return;
        }

        $this->searchResults = Obat::where('is_active', true)
            ->where(function ($q) {
                $q->where('nama_obat', 'like', "%{$this->search}%")
                    ->orWhere('nama_generik', 'like', "%{$this->search}%")
                    ->orWhere('barcode', $this->search); // exact match utamakan barcode scanner
            })
            ->limit(8)
            ->get(['id', 'kode_obat', 'nama_obat', 'golongan'])
            ->toArray();
    }

    /**
     * Dipanggil saat kasir menekan Enter pada hasil pencarian (lihat
     * x-on:keydown.enter di view) atau klik item.
     */
    public function addToCart(int $obatId): void
    {
        $obat = Obat::with('obatSatuan')->findOrFail($obatId);

        if (isset($this->cart[$obatId])) {
            $this->cart[$obatId]['jumlah']++;
        } else {
            // Estimasi harga dari batch FEFO pertama yang tersedia — hanya
            // untuk tampilan; angka final tetap dari PenjualanService.
            $batchPertama = \App\Models\BatchObat::eligibleForFefo($obatId)
                ->orderBy('tanggal_kadaluarsa')
                ->first();

            $this->cart[$obatId] = [
                'obat_id' => $obat->id,
                'kode_obat' => $obat->kode_obat,
                'nama_obat' => $obat->nama_obat,
                'golongan' => $obat->golongan,
                'jumlah' => 1,
                'harga_estimasi' => $batchPertama ? (float) ($batchPertama->harga_jual ?? 0) : 0,
            ];
        }

        $this->search = '';
        $this->searchResults = [];
    }

    public function updatedResepSearch(): void
    {
        if (strlen($this->resepSearch) < 2) {
            $this->resepResults = [];
            return;
        }

        $this->resepResults = Resep::with('pasien')
            ->where('status', Resep::STATUS_TERVERIFIKASI)
            ->where(function ($q) {
                $q->where('no_resep', 'like', "%{$this->resepSearch}%")
                    ->orWhereHas('pasien', fn ($q2) => $q2->where('nama_pasien', 'like', "%{$this->resepSearch}%"));
            })
            ->limit(8)
            ->get()
            ->map(fn ($r) => ['id' => $r->id, 'no_resep' => $r->no_resep, 'pasien_nama' => $r->pasien->nama_pasien])
            ->toArray();
    }

    /**
     * Mengisi keranjang dari sisa detail_resep (jumlah_diresepkan -
     * jumlah_terlayani) — kasir masih bisa ubah jumlah turun (mis. stok
     * kurang) tapi tidak lebih dari sisa, divalidasi ulang server-side
     * saat checkout (§ResepMismatchException).
     */
    public function selectResep(int $resepId): void
    {
        $resep = Resep::with('detail.obat', 'pasien')->findOrFail($resepId);

        $this->resepId = $resep->id;
        $this->resepNoTampil = $resep->no_resep;
        $this->resepPasienTampil = $resep->pasien->nama_pasien;

        foreach ($resep->detail as $line) {
            $sisa = $line->sisaDiresepkan();
            if ($sisa <= 0) {
                continue;
            }

            $obat = $line->obat;
            $batchPertama = \App\Models\BatchObat::eligibleForFefo($obat->id)
                ->orderBy('tanggal_kadaluarsa')
                ->first();

            $this->cart[$obat->id] = [
                'obat_id' => $obat->id,
                'kode_obat' => $obat->kode_obat,
                'nama_obat' => $obat->nama_obat,
                'golongan' => $obat->golongan,
                'jumlah' => $sisa,
                'harga_estimasi' => $batchPertama ? (float) ($batchPertama->harga_jual ?? 0) : 0,
            ];
        }

        $this->resepSearch = '';
        $this->resepResults = [];
    }

    public function clearResep(): void
    {
        $this->resepId = null;
        $this->resepNoTampil = '';
        $this->resepPasienTampil = '';
    }

    public function updateJumlah(int $obatId, int $jumlah): void
    {
        if ($jumlah <= 0) {
            $this->removeFromCart($obatId);
            return;
        }

        $this->cart[$obatId]['jumlah'] = $jumlah;
    }

    public function removeFromCart(int $obatId): void
    {
        unset($this->cart[$obatId]);
    }

    public function getTotalEstimasiProperty(): float
    {
        return collect($this->cart)->sum(fn ($item) => $item['jumlah'] * $item['harga_estimasi']);
    }

    public function getKembalianEstimasiProperty(): ?float
    {
        if ($this->metodeBayar !== 'tunai' || $this->jumlahBayar === null) {
            return null;
        }

        return max(0, $this->jumlahBayar - $this->totalEstimasi);
    }

    public function getButuhApprovalProperty(): bool
    {
        return collect($this->cart)->contains(fn ($item) => in_array($item['golongan'], [
            Obat::GOLONGAN_KERAS, Obat::GOLONGAN_NARKOTIKA, Obat::GOLONGAN_PSIKOTROPIKA,
        ], true));
    }

    public function openPayment(): void
    {
        if (empty($this->cart)) {
            $this->addError('cart', 'Keranjang masih kosong.');
            return;
        }

        $this->showPaymentModal = true;
    }

    /** Ctrl+Enter / F10 di view memanggil ini — bukan Enter biasa (§33). */
    public function checkout(PenjualanService $service): void
    {
        $items = collect($this->cart)
            ->map(fn ($item) => ['obat_id' => $item['obat_id'], 'jumlah' => $item['jumlah']])
            ->values()
            ->toArray();

        try {
            $transaksi = $service->createSale(
                items: $items,
                metodeBayar: $this->metodeBayar,
                kasirId: auth()->id(),
                jumlahBayar: $this->jumlahBayar,
                apotekerApprovalId: $this->apotekerApprovalId,
                resepId: $this->resepId,
            );
        } catch (
            ApprovalRequiredException|
            HargaJualBelumDiaturException|
            InsufficientStockException|
            InvalidArgumentException|
            ResepNotVerifiedException|
            ResepAlreadyDispensedException|
            ResepMismatchException $e
        ) {
            // Pesan sudah aman ditampilkan ke user (§36) — dilempar langsung
            // dari exception, tidak ada detail SQL yang bocor.
            $this->addError('checkout', $e->getMessage());
            return;
        }

        $this->redirectRoute('penjualan.struk', $transaksi, navigate: true);
    }

    public function render()
    {
        return view('livewire.penjualan.kasir.form', [
            'apotekerOptions' => $this->butuhApproval
                ? User::role('apoteker')->get() // asumsi scope role() tersedia (lihat catatan)
                : collect(),
        ]);
    }
}
