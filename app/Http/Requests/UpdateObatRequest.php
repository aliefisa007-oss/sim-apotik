<?php

namespace App\Http\Requests;

use App\Models\Obat;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class UpdateObatRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()?->can('update', $this->route('obat')) ?? false;
    }

    public function rules(): array
    {
        /** @var Obat $obat */
        $obat = $this->route('obat');

        return [
            'nama_obat' => ['required', 'string', 'max:255'],
            'nama_generik' => ['nullable', 'string', 'max:255'],
            'kategori_id' => ['required', 'exists:kategori_obat,id'],
            'golongan' => ['required', Rule::in(Obat::GOLONGAN_OPTIONS)],
            'barcode' => ['nullable', 'string', 'max:64', Rule::unique('obat', 'barcode')->ignore($obat->id)],
            'butuh_resep' => ['boolean'],
            'deskripsi' => ['nullable', 'string'],
            'is_active' => ['boolean'],

            'satuan' => ['required', 'array', 'min:1'],
            'satuan.*.satuan_id' => ['required', 'distinct', 'exists:satuan,id'],
            'satuan.*.konversi_ke_satuan_dasar' => ['required', 'integer', 'min:1'],
            'satuan.*.is_satuan_dasar' => ['boolean'],
            'satuan.*.is_satuan_jual_default' => ['boolean'],
        ];
    }
}
