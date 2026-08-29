<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('transaksi_penjualan', function (Blueprint $table) {
            $table->id();
            $table->string('no_transaksi')->unique();

            // resep_id & pasien_id: tabel resep/pasien belum ada sampai
            // Phase 6, jadi FK constraint SENGAJA belum dipasang di sini.
            // Ditambahkan lewat migration Phase 6 begitu tabelnya ada.
            $table->unsignedBigInteger('resep_id')->nullable();
            $table->unsignedBigInteger('pasien_id')->nullable();

            $table->foreignId('kasir_id')->constrained('users');
            // Wajib diisi HANYA jika transaksi memuat obat golongan
            // keras/narkotika/psikotropika — divalidasi di PenjualanService,
            // bukan hanya di UI (§19).
            $table->foreignId('apoteker_approval_id')->nullable()->constrained('users');

            $table->decimal('total', 15, 2);
            $table->enum('metode_bayar', ['tunai', 'debit', 'kredit', 'qris', 'transfer']);
            $table->decimal('jumlah_bayar', 15, 2)->nullable();
            $table->decimal('kembalian', 15, 2)->nullable();

            // 'dibatalkan' dipakai untuk void — TIDAK PERNAH DELETE baris
            // transaksi yang sudah memengaruhi stok (§71).
            $table->enum('status', ['selesai', 'dibatalkan'])->default('selesai');
            $table->string('alasan_pembatalan')->nullable();

            $table->timestamps();

            $table->index('kasir_id');
            $table->index('status');
            $table->index('created_at');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('transaksi_penjualan');
    }
};
