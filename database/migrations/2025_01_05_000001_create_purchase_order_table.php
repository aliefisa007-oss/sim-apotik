<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('purchase_order', function (Blueprint $table) {
            $table->id();
            $table->string('no_po')->unique();
            $table->foreignId('supplier_id')->constrained('suppliers');
            $table->date('tanggal_po');
            $table->enum('status', ['draft', 'dikirim', 'diterima_sebagian', 'selesai', 'batal'])->default('draft');
            $table->decimal('total', 15, 2)->default(0);
            $table->foreignId('user_id')->constrained('users');
            $table->timestamps();

            $table->index('supplier_id');
            $table->index('status');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('purchase_order');
    }
};
