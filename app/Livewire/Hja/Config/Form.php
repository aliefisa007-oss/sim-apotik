<?php

namespace App\Livewire\Hja\Config;

use App\Models\HjaConfig;
use Livewire\Component;

class Form extends Component
{
    public float $default_tax_percent = 11.0;
    public bool $harga_beli_termasuk_pajak_default = false;
    public string $default_metode = 'markup';
    public float $default_markup_percent = 20.0;
    public float $default_margin_percent = 20.0;
    public string $rounding_method = 'round_up';
    public int $rounding_increment = 500;

    public function mount(): void
    {
        $this->authorize('update', HjaConfig::class);

        $config = HjaConfig::current();
        $this->default_tax_percent = (float) $config->default_tax_percent;
        $this->harga_beli_termasuk_pajak_default = $config->harga_beli_termasuk_pajak_default;
        $this->default_metode = $config->default_metode;
        $this->default_markup_percent = (float) $config->default_markup_percent;
        $this->default_margin_percent = (float) $config->default_margin_percent;
        $this->rounding_method = $config->rounding_method;
        $this->rounding_increment = $config->rounding_increment;
    }

    protected function rules(): array
    {
        return [
            'default_tax_percent' => ['required', 'numeric', 'min:0', 'max:100'],
            'harga_beli_termasuk_pajak_default' => ['boolean'],
            'default_metode' => ['required', 'in:markup,margin'],
            'default_markup_percent' => ['required', 'numeric', 'min:0'],
            'default_margin_percent' => ['required', 'numeric', 'min:0', 'max:99.99'],
            'rounding_method' => ['required', 'in:round,round_up,round_down'],
            'rounding_increment' => ['required', 'integer', 'min:1'],
        ];
    }

    public function save(): void
    {
        $this->validate();

        HjaConfig::current()->update([
            'default_tax_percent' => $this->default_tax_percent,
            'harga_beli_termasuk_pajak_default' => $this->harga_beli_termasuk_pajak_default,
            'default_metode' => $this->default_metode,
            'default_markup_percent' => $this->default_markup_percent,
            'default_margin_percent' => $this->default_margin_percent,
            'rounding_method' => $this->rounding_method,
            'rounding_increment' => $this->rounding_increment,
        ]);

        session()->flash('success', 'Konfigurasi HJA berhasil disimpan.');
    }

    public function render()
    {
        return view('livewire.hja.config.form');
    }
}
