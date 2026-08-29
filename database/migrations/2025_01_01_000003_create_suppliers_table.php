<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('suppliers', function (Blueprint $table) {
            $table->id();
            $table->string('nama_pbf');
            // Nullable/unique-if-present. Whether this should be mandatory
            // is a REGULATORY CONFIGURATION / TO BE VERIFIED item.
            $table->string('no_izin_pbf')->nullable()->unique();
            $table->string('alamat')->nullable();
            $table->string('kontak')->nullable();
            $table->boolean('is_active')->default(true);
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('suppliers');
    }
};
