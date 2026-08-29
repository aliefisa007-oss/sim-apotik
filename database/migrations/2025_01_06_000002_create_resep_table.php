<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('resep', function (Blueprint $table) {
            $table->id();
            $table->string('no_resep')->unique();

            $table->foreignId('pasien_id')->constrained('pasien');

            // Belum ada master data dokter/klinik rujukan (di luar scope
            // Phase 6) — nama & no. SIP dokter disimpan sebagai teks bebas.
            // no_sip_dokter nullable: TO BE VERIFIED apakah wajib diisi
            // untuk semua golongan atau hanya narkotika/psikotropika.
            $table->string('nama_dokter');
            $table->string('no_sip_dokter')->nullable();
            $table->date('tanggal_resep');

            $table->enum('status', [
                'menunggu_verifikasi',
                'terverifikasi',
                'ditolak',
                'selesai',
            ])->default('menunggu_verifikasi');

            $table->foreignId('apoteker_verifikasi_id')->nullable()->constrained('users');
            $table->text('catatan_verifikasi')->nullable();
            $table->timestamp('verified_at')->nullable();

            // User yang menginput resep ke sistem (kasir/admin/apoteker) —
            // beda dari apoteker_verifikasi_id (yang memverifikasi).
            $table->foreignId('created_by')->constrained('users');

            $table->timestamps();

            $table->index('status');
            $table->index('tanggal_resep');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('resep');
    }
};
