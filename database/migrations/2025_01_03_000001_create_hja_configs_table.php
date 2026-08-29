<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // Singleton table (1 baris). Owner/Admin mengubah baris ini lewat
        // UI Pengaturan HJA. Nilai pajak default 11% adalah konfigurasi
        // teknis, BUKAN klaim kepastian regulasi — REGULATORY CONFIGURATION
        // / TO BE VERIFIED, disesuaikan Alief sesuai aturan PPN yang berlaku.
        Schema::create('hja_configs', function (Blueprint $table) {
            $table->id();
            $table->decimal('default_tax_percent', 5, 2)->default(11.00);
            $table->boolean('harga_beli_termasuk_pajak_default')->default(false);
            $table->enum('default_metode', ['markup', 'margin'])->default('markup');
            $table->decimal('default_markup_percent', 5, 2)->default(20.00);
            $table->decimal('default_margin_percent', 5, 2)->default(20.00);
            $table->enum('rounding_method', ['round', 'round_up', 'round_down'])->default('round_up');
            $table->unsignedInteger('rounding_increment')->default(500);
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('hja_configs');
    }
};
