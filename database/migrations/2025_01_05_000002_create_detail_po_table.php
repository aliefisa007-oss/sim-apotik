<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('detail_po', function (Blueprint $table) {
            $table->id();
            $table->foreignId('po_id')->constrained('purchase_order')->cascadeOnDelete();
            $table->foreignId('obat_id')->constrained('obat');
            $table->unsignedInteger('jumlah_order');
            $table->unsignedInteger('jumlah_diterima')->default(0);
            $table->decimal('harga_satuan', 15, 2);
            $table->timestamps();

            $table->index('po_id');
            $table->index('obat_id');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('detail_po');
    }
};
