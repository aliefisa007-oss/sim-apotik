<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // Intentionally no price columns here. Pricing is batch-based and
        // historized starting Phase 2/3 (batch_obat / HJA) — a master price
        // on obat would invite reading a stale/wrong value by mistake.
        Schema::create('obat', function (Blueprint $table) {
            $table->id();
            $table->string('kode_obat')->unique();
            $table->string('nama_obat');
            $table->string('nama_generik')->nullable();
            $table->foreignId('kategori_id')->constrained('kategori_obat');
            $table->enum('golongan', [
                'bebas',
                'bebas_terbatas',
                'keras',
                'narkotika',
                'psikotropika',
            ]);
            $table->foreignId('satuan_dasar_id')->constrained('satuan');
            $table->string('barcode')->nullable()->unique();
            $table->boolean('butuh_resep')->default(false);
            $table->text('deskripsi')->nullable();
            $table->boolean('is_active')->default(true);
            $table->timestamps();

            $table->index('kategori_id');
            $table->index('golongan');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('obat');
    }
};
