<?php

namespace App\Services;

use App\Models\Pasien;

class PasienService
{
    public function create(array $data): Pasien
    {
        return Pasien::create($data);
    }

    public function update(Pasien $pasien, array $data): Pasien
    {
        $pasien->update($data);

        return $pasien->fresh();
    }

    public function deactivate(Pasien $pasien): Pasien
    {
        $pasien->update(['is_active' => false]);

        return $pasien;
    }
}
