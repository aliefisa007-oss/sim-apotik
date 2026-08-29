<?php

namespace App\Exceptions;

use RuntimeException;

/**
 * Dilempar saat item golongan-restricted di keranjang kasir TIDAK sesuai
 * dengan detail_resep yang terverifikasi — mencegah approval resep
 * "dipinjam" untuk menjual obat lain yang tidak diresepkan (§19 extended
 * ke Phase 6).
 */
class ResepMismatchException extends RuntimeException
{
    public static function obatTidakDiresepkan(string $namaObat): self
    {
        return new self("{$namaObat} membutuhkan approval apoteker tetapi tidak ada di resep yang dipilih.");
    }

    public static function melebihiSisaResep(string $namaObat, int $sisa): self
    {
        return new self("Jumlah {$namaObat} melebihi sisa yang diresepkan (sisa: {$sisa}).");
    }
}
