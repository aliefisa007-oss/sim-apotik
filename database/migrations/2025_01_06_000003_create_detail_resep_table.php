<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('detail_resep', function (Blueprint $table) {
            $table->id();
            $table->foreignId('resep_id')->constrained('resep')->cascadeOnDelete();
            $table->foreignId('obat_id')->constrained('obat');

            $table->unsignedInteger('jumlah_diresepkan');

            // Kumulatif jumlah yang sudah didispensing lewat PenjualanService.
            // Dibiarkan < jumlah_diresepkan kalau stok tidak cukup saat itu
            // (dispensing sebagian) — resep tetap 'terverifikasi', baru
            // berubah 'selesai' kalau SEMUA baris terpenuhi (lihat
            // Resep::isFullyDispensed() & ResepService).
            $table->unsignedInteger('jumlah_terlayani')->default(0);

            $table->string('aturan_pakai')->nullable();
            $table->text('catatan')->nullable();

            $table->timestamps();

            $table->index('resep_id');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('detail_resep');
    }
};
