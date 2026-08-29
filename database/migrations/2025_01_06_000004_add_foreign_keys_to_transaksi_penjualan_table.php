<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * resep_id & pasien_id sudah ada di transaksi_penjualan sejak Phase 4
     * (nullable, TANPA FK karena tabelnya belum ada). Sekarang tabel resep
     * & pasien sudah dibuat (Phase 6), FK dipasang di sini sesuai rencana
     * yang dicatat di migration Phase 4 — bukan mengubah struktur kolom.
     */
    public function up(): void
    {
        Schema::table('transaksi_penjualan', function (Blueprint $table) {
            $table->foreign('resep_id')->references('id')->on('resep');
            $table->foreign('pasien_id')->references('id')->on('pasien');

            $table->index('resep_id');
            $table->index('pasien_id');
        });
    }

    public function down(): void
    {
        Schema::table('transaksi_penjualan', function (Blueprint $table) {
            $table->dropForeign(['resep_id']);
            $table->dropForeign(['pasien_id']);
            $table->dropIndex(['resep_id']);
            $table->dropIndex(['pasien_id']);
        });
    }
};
