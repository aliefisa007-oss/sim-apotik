<?php

namespace App\Http\Requests;

use App\Models\Supplier;
use Illuminate\Foundation\Http\FormRequest;

class StoreSupplierRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()?->can('create', Supplier::class) ?? false;
    }

    public function rules(): array
    {
        return [
            'nama_pbf' => ['required', 'string', 'max:255'],
            'no_izin_pbf' => ['nullable', 'string', 'max:100', 'unique:suppliers,no_izin_pbf'],
            'alamat' => ['nullable', 'string', 'max:500'],
            'kontak' => ['nullable', 'string', 'max:100'],
            'is_active' => ['boolean'],
        ];
    }
}
