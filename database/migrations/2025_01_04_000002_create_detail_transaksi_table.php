<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('detail_transaksi', function (Blueprint $table) {
            $table->id();
            $table->foreignId('transaksi_id')->constrained('transaksi_penjualan')->cascadeOnDelete();
            $table->foreignId('obat_id')->constrained('obat');
            // Wajib per-batch (§18): jika satu obat diambil dari 2 batch
            // (hasil alokasi FEFO), akan ada 2 baris detail_transaksi untuk
            // obat yang sama — bukan digabung jadi satu angka stok.
            $table->foreignId('batch_id')->constrained('batch_obat');
            $table->unsignedInteger('jumlah');
            // harga_satuan diambil dari batch_obat.harga_jual PADA SAAT
            // transaksi — disalin ke sini (bukan di-join live) supaya nilai
            // di struk tidak berubah kalau harga jual batch diedit setelahnya.
            $table->decimal('harga_satuan', 15, 2);
            $table->decimal('subtotal', 15, 2);
            $table->timestamps();

            $table->index('transaksi_id');
            $table->index('obat_id');
            $table->index('batch_id');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('detail_transaksi');
    }
};
