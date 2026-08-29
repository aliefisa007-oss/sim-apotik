<?php

namespace App\Exceptions;

use RuntimeException;

/**
 * Dilempar saat total stok aktif (lintas batch) tidak cukup untuk memenuhi
 * permintaan. Message-nya sudah aman ditampilkan ke user (§36) — jangan
 * bungkus lagi dengan detail SQL di layer atasnya.
 */
class InsufficientStockException extends RuntimeException
{
    public static function forObat(int $obatId, int $diminta, int $tersedia): self
    {
        return new self(
            "Stok obat tidak mencukupi. Diminta: {$diminta}, tersedia: {$tersedia}.",
            0,
        );
    }
}
