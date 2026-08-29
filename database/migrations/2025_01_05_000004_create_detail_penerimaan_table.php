<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('detail_penerimaan', function (Blueprint $table) {
            $table->id();
            $table->foreignId('penerimaan_id')->constrained('penerimaan_barang')->cascadeOnDelete();
            $table->foreignId('obat_id')->constrained('obat');

            // Info batch DIINPUT di sini (baris penerimaan), lalu diproses
            // lewat StockService::receiveStock() yang membuat/menambah baris
            // batch_obat. batch_id di bawah diisi SETELAH proses itu selesai
            // — bukan dipilih manual, supaya konsisten dengan aturan 1
            // no_batch = 1 baris per obat (lihat migration batch_obat).
            $table->string('no_batch');
            $table->date('tanggal_produksi')->nullable();
            $table->date('tanggal_kadaluarsa');
            $table->decimal('harga_beli', 15, 2);
            $table->unsignedInteger('jumlah');
            $table->foreignId('batch_id')->nullable()->constrained('batch_obat');

            $table->timestamps();

            $table->index('penerimaan_id');
            $table->index('obat_id');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('detail_penerimaan');
    }
};
