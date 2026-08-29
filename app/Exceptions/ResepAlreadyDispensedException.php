<?php

namespace App\Exceptions;

use RuntimeException;

class ResepAlreadyDispensedException extends RuntimeException
{
    public static function forResep(string $noResep): self
    {
        return new self("Resep {$noResep} sudah selesai didispensing sepenuhnya — tidak bisa dipakai untuk transaksi baru.");
    }
}
