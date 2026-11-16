<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('tindak_lanjut', function (Blueprint $table) {
            $table->id();
            $table->foreignId('alternatif_id')->unique()->constrained('alternatif_strategi')->restrictOnDelete();
            $table->text('rencana')->nullable();
            $table->text('alasan_dugaan_penyebab')->nullable();
            $table->date('tanggal_mulai')->nullable();
            $table->string('status', 15);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('tindak_lanjut');
    }
};
