<?php

namespace App\Http\Requests;

use App\Models\KategoriObat;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Validator;

class StoreKategoriObatRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()?->can('create', KategoriObat::class) ?? false;
    }

    public function rules(): array
    {
        return [
            'nama' => ['required', 'string', 'max:255'],
            'parent_id' => ['nullable', 'exists:kategori_obat,id'],
            'is_active' => ['boolean'],
        ];
    }

    public function withValidator(Validator $validator): void
    {
        $validator->after(function (Validator $validator) {
            $kategoriId = $this->route('kategori_obat')?->id;
            $parentId = $this->input('parent_id');

            if (!$kategoriId || !$parentId) {
                return;
            }

            if ($parentId === $kategoriId) {
                $validator->errors()->add('parent_id', 'Kategori tidak boleh menjadi induk dirinya sendiri.');
                return;
            }

            // Walk up the ancestor chain to prevent a cycle.
            $ancestor = KategoriObat::find($parentId);
            $visited = [];
            while ($ancestor) {
                if (in_array($ancestor->id, $visited, true)) {
                    break; // already-corrupt data; stop, don't loop forever
                }
                $visited[] = $ancestor->id;

                if ($ancestor->id === $kategoriId) {
                    $validator->errors()->add('parent_id', 'Perubahan ini akan membuat siklus kategori.');
                    return;
                }
                $ancestor = $ancestor->parent;
            }
        });
    }
}
