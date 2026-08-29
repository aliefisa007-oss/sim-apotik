<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Dibutuhkan untuk Laporan Stok (Phase 7) — sebelumnya tidak ada
     * ambang batas "stok menipis" per obat. Default 10 adalah SENSIBLE
     * DEFAULT, bukan aturan farmasi — TO BE VERIFIED / diubah per obat
     * oleh owner/admin lewat form obat (field sudah ditambahkan di
     * Livewire Form).
     */
    public function up(): void
    {
        Schema::table('obat', function (Blueprint $table) {
            $table->unsignedInteger('stok_minimum')->default(10)->after('is_active');
        });
    }

    public function down(): void
    {
        Schema::table('obat', function (Blueprint $table) {
            $table->dropColumn('stok_minimum');
        });
    }
};
