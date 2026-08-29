<?php

namespace App\Services;

use App\Models\Obat;
use App\Models\ObatSatuan;
use Illuminate\Support\Facades\DB;
use InvalidArgumentException;

class ObatService
{
    /**
     * Create a new obat plus its unit-conversion rows.
     *
     * @param array $data Validated obat fields (see StoreObatRequest)
     * @param array $satuanList Each item: ['satuan_id' => int, 'konversi_ke_satuan_dasar' => int,
     *                          'is_satuan_dasar' => bool, 'is_satuan_jual_default' => bool]
     */
    public function create(array $data, array $satuanList): Obat
    {
        $this->assertExactlyOneSatuanDasar($satuanList);

        return DB::transaction(function () use ($data, $satuanList) {
            $data['kode_obat'] = $this->generateKodeObat();

            $obat = Obat::create($data);

            $this->syncSatuan($obat, $satuanList);

            return $obat->fresh(['kategori', 'satuanDasar', 'obatSatuan.satuan']);
        });
    }

    public function update(Obat $obat, array $data, array $satuanList): Obat
    {
        $this->assertExactlyOneSatuanDasar($satuanList);

        return DB::transaction(function () use ($obat, $data, $satuanList) {
            // kode_obat is immutable once assigned — never regenerate on update.
            unset($data['kode_obat']);

            $obat->update($data);

            $obat->obatSatuan()->delete();
            $this->syncSatuan($obat, $satuanList);

            return $obat->fresh(['kategori', 'satuanDasar', 'obatSatuan.satuan']);
        });
    }

    public function deactivate(Obat $obat): Obat
    {
        $obat->update(['is_active' => false]);

        return $obat;
    }

    /**
     * Sequential, human-readable code: OBT-000001, OBT-000002, ...
     * Generated inside the DB transaction with a locking read to avoid
     * two concurrent creates racing to the same number.
     */
    private function generateKodeObat(): string
    {
        $last = Obat::lockForUpdate()
            ->where('kode_obat', 'like', 'OBT-%')
            ->orderByDesc('id')
            ->value('kode_obat');

        $nextNumber = $last
            ? ((int) substr($last, 4)) + 1
            : 1;

        return sprintf('OBT-%06d', $nextNumber);
    }

    private function syncSatuan(Obat $obat, array $satuanList): void
    {
        foreach ($satuanList as $row) {
            ObatSatuan::create([
                'obat_id' => $obat->id,
                'satuan_id' => $row['satuan_id'],
                'konversi_ke_satuan_dasar' => $row['konversi_ke_satuan_dasar'],
                'is_satuan_dasar' => $row['is_satuan_dasar'] ?? false,
                'is_satuan_jual_default' => $row['is_satuan_jual_default'] ?? false,
            ]);
        }
    }

    private function assertExactlyOneSatuanDasar(array $satuanList): void
    {
        $dasarCount = collect($satuanList)->where('is_satuan_dasar', true)->count();

        if ($dasarCount !== 1) {
            throw new InvalidArgumentException(
                'Obat harus memiliki tepat satu satuan dasar.'
            );
        }
    }
}
