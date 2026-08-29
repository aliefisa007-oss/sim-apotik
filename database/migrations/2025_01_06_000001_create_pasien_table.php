<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('pasien', function (Blueprint $table) {
            $table->id();

            // No rekam medis nullable & unique-when-present — banyak pasien
            // walk-in di apotek tidak punya RM formal (beda dari pasien RS).
            $table->string('no_rm')->nullable()->unique();

            $table->string('nama_pasien');
            $table->date('tanggal_lahir')->nullable();
            $table->enum('jenis_kelamin', ['L', 'P'])->nullable();
            $table->text('alamat')->nullable();
            $table->string('no_telepon')->nullable();

            // Catatan alergi bebas-teks untuk kewaspadaan apoteker saat
            // verifikasi resep — BUKAN validasi otomatis/interaksi obat
            // (di luar scope Phase 6, lihat catatan ResepService).
            $table->text('alergi')->nullable();

            $table->boolean('is_active')->default(true);
            $table->timestamps();

            $table->index('nama_pasien');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('pasien');
    }
};
