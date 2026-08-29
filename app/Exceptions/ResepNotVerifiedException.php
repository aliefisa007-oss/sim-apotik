<?php

namespace App\Exceptions;

use RuntimeException;

class ResepNotVerifiedException extends RuntimeException
{
    public static function forResep(string $noResep, string $status): self
    {
        $statusLabel = str_replace('_', ' ', $status);

        return new self("Resep {$noResep} berstatus '{$statusLabel}' — harus diverifikasi apoteker dahulu sebelum bisa dipakai untuk transaksi.");
    }
}
