<?php

namespace App\Http\Requests;

use App\Models\Obat;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class StoreObatRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()?->can('create', Obat::class) ?? false;
    }

    public function rules(): array
    {
        return [
            'nama_obat' => ['required', 'string', 'max:255'],
            'nama_generik' => ['nullable', 'string', 'max:255'],
            'kategori_id' => ['required', 'exists:kategori_obat,id'],
            'golongan' => ['required', Rule::in(Obat::GOLONGAN_OPTIONS)],
            'barcode' => ['nullable', 'string', 'max:64', 'unique:obat,barcode'],
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

    public function messages(): array
    {
        return [
            'satuan.required' => 'Minimal satu satuan harus ditambahkan.',
            'satuan.*.satuan_id.distinct' => 'Satuan tidak boleh duplikat untuk satu obat.',
        ];
    }
}
