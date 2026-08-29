<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('batch_obat', function (Blueprint $table) {
            $table->id();
            $table->foreignId('obat_id')->constrained('obat');
            $table->foreignId('supplier_id')->constrained('suppliers');
            $table->string('no_batch');
            $table->date('tanggal_produksi')->nullable();
            $table->date('tanggal_kadaluarsa');
            $table->decimal('harga_beli', 15, 2);
            // Nullable: harga_jual per-batch dihitung oleh HJAService di Phase 3.
            // Belum ada modul HJA di Phase 2, jadi kolom ini boleh kosong dulu.
            $table->decimal('harga_jual', 15, 2)->nullable();
            // Kuantitas selalu dalam satuan_dasar milik obat (lihat obat_satuan),
            // supaya FEFO/StockService tidak perlu peduli konversi satuan.
            $table->unsignedInteger('stok_awal');
            $table->unsignedInteger('stok_saat_ini');
            $table->enum('status', ['aktif', 'habis', 'expired', 'ditarik'])->default('aktif');
            $table->timestamps();

            // Satu no_batch harus unik per obat agar traceability benar —
            // penerimaan ulang batch yang sama menambah stok baris ini,
            // bukan membuat baris baru (lihat StockService::receiveStock).
            $table->unique(['obat_id', 'no_batch']);
            $table->index('obat_id');
            $table->index('tanggal_kadaluarsa');
            $table->index('status');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('batch_obat');
    }
};
