<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // NOTE: satuan is a name lookup only. Conversion factors are
        // medicine-specific and live in obat_satuan (see migration 000005),
        // not here — one "box" is not the same quantity for every obat.
        Schema::create('satuan', function (Blueprint $table) {
            $table->id();
            $table->string('nama')->unique();
            $table->boolean('is_active')->default(true);
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('satuan');
    }
};
