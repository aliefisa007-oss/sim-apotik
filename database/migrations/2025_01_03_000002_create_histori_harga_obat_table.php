<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('histori_harga_obat', function (Blueprint $table) {
            $table->id();
            $table->foreignId('obat_id')->constrained('obat');
            $table->foreignId('batch_id')->constrained('batch_obat');

            $table->decimal('harga_lama', 15, 2)->nullable();
            $table->decimal('harga_baru', 15, 2);

            // Breakdown lengkap kalkulasi — disimpan agar harga_baru selalu
            // bisa diaudit ulang tanpa menebak parameter yang dipakai (§15).
            $table->decimal('harga_faktur', 15, 2);
            $table->decimal('diskon_persen', 5, 2)->default(0);
            $table->decimal('harga_netto', 15, 2);
            $table->decimal('tax_percent', 5, 2);
            $table->boolean('harga_termasuk_pajak');
            $table->decimal('cost_basis', 15, 2);
            $table->enum('metode_hja', ['markup', 'margin']);
            $table->decimal('persen_markup_margin', 5, 2);
            $table->decimal('harga_sebelum_pembulatan', 15, 2);
            $table->string('rounding_method');
            $table->unsignedInteger('rounding_increment');
            $table->decimal('rounding_difference', 15, 2);

            $table->string('alasan')->nullable();
            $table->foreignId('user_id')->constrained('users');
            $table->timestamps();

            $table->index('obat_id');
            $table->index('batch_id');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('histori_harga_obat');
    }
};
