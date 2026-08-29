<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('kartu_stok', function (Blueprint $table) {
            $table->id();
            $table->foreignId('obat_id')->constrained('obat');
            $table->foreignId('batch_id')->constrained('batch_obat');
            $table->enum('jenis_transaksi', [
                'masuk_pembelian',
                'keluar_jual',
                'penyesuaian',
                'expired_writeoff',
                'retur',
            ]);
            // SIGNED: positif = stok bertambah, negatif = stok berkurang.
            // Konvensi ini dipilih supaya saldo_sesudah = saldo_sebelum + jumlah
            // berlaku seragam untuk semua jenis_transaksi tanpa percabangan.
            $table->integer('jumlah');
            $table->unsignedInteger('saldo_sebelum');
            $table->unsignedInteger('saldo_sesudah');
            $table->unsignedBigInteger('referensi_id')->nullable();
            $table->string('referensi_type')->nullable();
            $table->foreignId('user_id')->constrained('users');
            $table->string('keterangan')->nullable();
            $table->timestamps();

            $table->index('obat_id');
            $table->index('batch_id');
            $table->index('jenis_transaksi');
            $table->index(['referensi_type', 'referensi_id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('kartu_stok');
    }
};
