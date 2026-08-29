<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('obat_satuan', function (Blueprint $table) {
            $table->id();
            $table->foreignId('obat_id')->constrained('obat')->cascadeOnDelete();
            $table->foreignId('satuan_id')->constrained('satuan');
            // How many satuan_dasar units this satuan equals for THIS obat.
            // e.g. for obat A: box -> 100 (tablet), strip -> 10 (tablet).
            $table->unsignedInteger('konversi_ke_satuan_dasar');
            $table->boolean('is_satuan_dasar')->default(false);
            $table->boolean('is_satuan_jual_default')->default(false);
            $table->timestamps();

            $table->unique(['obat_id', 'satuan_id']);
            $table->index('obat_id');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('obat_satuan');
    }
};
