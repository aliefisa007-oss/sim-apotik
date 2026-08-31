<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('detail_stok_opname', function (Blueprint $table) {
            $table->id();

            $table->foreignId('stok_opname_id')->constrained('stok_opname')->cascadeOnDelete();
            $table->foreignId('batch_obat_id')->constrained('batch_obat');

            // Snapshot stok sistem SAAT SESI DIBUAT — bukan dihitung ulang
            // real-time saat laporan dibuka, supaya angka pembanding tetap
            // konsisten meskipun ada transaksi lain berjalan selama proses
            // hitung fisik berlangsung.
            $table->integer('stok_sistem');

            // Null selama belum dihitung — dipakai untuk validasi "semua
            // item sudah dihitung" sebelum sesi bisa diselesaikan.
            $table->integer('stok_fisik')->nullable();

            // Snapshot harga_beli SAAT SESI DIBUAT untuk nilai rupiah
            // selisih di laporan — supaya nilai laporan tidak bergeser
            // kalau batch di-restock harga beda setelah opname selesai
            // (masalah yang sama seperti keterbatasan HPP di
            // LaporanKeuanganService, sengaja dihindari di sini dengan
            // snapshot eksplisit).
            $table->decimal('harga_beli_saat_opname', 15, 2);

            $table->text('catatan')->nullable();

            $table->foreignId('dihitung_oleh')->nullable()->constrained('users');
            $table->timestamp('dihitung_pada')->nullable();

            $table->timestamps();

            $table->unique(['stok_opname_id', 'batch_obat_id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('detail_stok_opname');
    }
};
