<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('penerimaan_barang', function (Blueprint $table) {
            $table->id();
            // Nullable: penerimaan boleh terjadi tanpa PO (mis. stok awal,
            // koreksi darurat) — sama seperti StokMasuk manual di Phase 2,
            // tapi penerimaan_barang punya jejak dokumen faktur supplier.
            $table->foreignId('po_id')->nullable()->constrained('purchase_order');
            $table->date('tanggal_terima');
            $table->string('no_faktur_supplier')->nullable();
            $table->foreignId('user_id')->constrained('users');
            $table->timestamps();

            $table->index('po_id');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('penerimaan_barang');
    }
};
