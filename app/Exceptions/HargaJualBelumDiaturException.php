<?php

namespace App\Exceptions;

use RuntimeException;

class HargaJualBelumDiaturException extends RuntimeException
{
    public static function forBatch(string $noBatch): self
    {
        return new self("Batch {$noBatch} belum memiliki harga jual. Atur HJA batch ini terlebih dahulu sebelum dijual.");
    }
}
