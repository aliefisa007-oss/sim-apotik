<?php

namespace App\Exceptions;

use RuntimeException;

class ApprovalRequiredException extends RuntimeException
{
    public static function forObat(string $namaObat): self
    {
        return new self("Transaksi memuat {$namaObat} yang membutuhkan approval apoteker. Pilih apoteker penanggung jawab sebelum melanjutkan.");
    }

    public static function invalidApoteker(): self
    {
        return new self('User yang dipilih sebagai apoteker approval tidak memiliki role apoteker.');
    }
}
