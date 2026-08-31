<?php

namespace App\Livewire\Hja\Kalkulator;

use App\Models\BatchObat;
use App\Models\HjaConfig;
use App\Services\HJAService;
use Livewire\Component;

class Form extends Component
{
    public BatchObat $batch;

    public float $diskon_persen = 0;
    public float $tax_percent = 11;
    public bool $harga_termasuk_pajak = false;
    public string $metode = 'markup';
    public float $persen_markup_margin = 20;
    public string $rounding_method = 'round_up';
    public int $rounding_increment = 500;
    public string $alasan = '';

    public function mount(BatchObat $batch): void
    {
        $this->authorize('update', $batch);

        $this->batch = $batch;

        $config = HjaConfig::current();
        $this->tax_percent = (float) $config->default_tax_percent;
        $this->harga_termasuk_pajak = $config->harga_beli_termasuk_pajak_default;
        $this->metode = $config->default_metode;
        $this->persen_markup_margin = (float) ($this->metode === 'margin' ? $config->default_margin_percent : $config->default_markup_percent);
        $this->rounding_method = $config->rounding_method;
        $this->rounding_increment = $config->rounding_increment;
    }

    public function updatedMetode(): void
    {
        $config = HjaConfig::current();
        $this->persen_markup_margin = (float) ($this->metode === 'margin' ? $config->default_margin_percent : $config->default_markup_percent);
    }

    /**
     * Breakdown live — dihitung ulang setiap render dari input saat ini.
     * Tidak menyimpan apa pun; murni untuk preview sebelum user menekan Simpan.
     *
     * Method biasa (bukan computed property) — dipanggil sebagai
     * $this->breakdown() di Blade untuk menghindari bug wrapper
     * computed property Livewire pada versi ini.
     */
    public function breakdown(): ?array
    {
        $diskonPersen = $this->diskon_persen;
        $taxPercent = $this->tax_percent;
        $hargaTermasukPajak = $this->harga_termasuk_pajak;
        $metode = $this->metode;
        $persenMarkupMargin = $this->persen_markup_margin;
        $roundingMethod = $this->rounding_method;
        $roundingIncrement = $this->rounding_increment;

        try {
            return app(HJAService::class)->calculate([
                'harga_faktur' => (float) $this->batch->harga_beli,
                'diskon_persen' => $diskonPersen,
                'tax_percent' => $taxPercent,
                'harga_termasuk_pajak' => $hargaTermasukPajak,
                'metode' => $metode,
                'persen_markup_margin' => $persenMarkupMargin,
                'rounding_method' => $roundingMethod,
                'rounding_increment' => $roundingIncrement,
            ]);
        } catch (\InvalidArgumentException $e) {
            $this->addError('preview', $e->getMessage());
            return null;
        }
    }

    public function save(HJAService $service): void
    {
        try {
            $service->setHargaJual($this->batch, [
                'diskon_persen' => $this->diskon_persen,
                'tax_percent' => $this->tax_percent,
                'harga_termasuk_pajak' => $this->harga_termasuk_pajak,
                'metode' => $this->metode,
                'persen_markup_margin' => $this->persen_markup_margin,
                'rounding_method' => $this->rounding_method,
                'rounding_increment' => $this->rounding_increment,
            ], auth()->id(), $this->alasan ?: null);
        } catch (\InvalidArgumentException $e) {
            $this->addError('preview', $e->getMessage());
            return;
        }

        session()->flash('success', "Harga jual batch {$this->batch->no_batch} berhasil diperbarui.");
        $this->batch->refresh();
    }

    public function render()
    {
        return view('livewire.hja.kalkulator.form');
    }
}