<?php

namespace App\Http\Requests;

use App\Models\Satuan;
use Illuminate\Foundation\Http\FormRequest;

class StoreSatuanRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()?->can('create', Satuan::class) ?? false;
    }

    public function rules(): array
    {
        return [
            'nama' => ['required', 'string', 'max:100', 'unique:satuan,nama'],
            'is_active' => ['boolean'],
        ];
    }
}
