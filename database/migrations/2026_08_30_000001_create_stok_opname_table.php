<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('stok_opname', function (Blueprint $table) {
            $table->id();

            // Format OPN-YYYYMM-0001, sequential per bulan — cocok untuk
            // full count bulanan (satu sesi per bulan pada praktiknya,
            // tapi tidak dipaksa lewat constraint supaya tetap fleksibel
            // kalau suatu saat perlu opname dadakan di bulan yang sama).
            $table->string('kode_opname')->unique();

            $table->date('tanggal_mulai');
            $table->date('tanggal_selesai')->nullable();

            // berjalan -> selesai (memicu penyesuaian stok riil via
            // StockService) atau dibatalkan (sesi keliru dibuat, belum
            // sempat diselesaikan, TIDAK berdampak ke stok karena
            // penyesuaian riil baru terjadi saat selesaikan()).
            $table->enum('status', ['berjalan', 'selesai', 'dibatalkan'])->default('berjalan');

            $table->foreignId('dibuat_oleh')->constrained('users');

            // Selesaikan opname (yang memicu penyesuaian stok riil)
            // sengaja dibatasi Owner/Admin — dual control, konsisten
            // dengan pola void transaksi penjualan (TransaksiPenjualanPolicy).
            // Staf gudang yang menghitung fisik BUKAN yang menyetujui
            // penyesuaian akhirnya.
            $table->foreignId('diselesaikan_oleh')->nullable()->constrained('users');

            $table->text('catatan')->nullable();

            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('stok_opname');
    }
};
