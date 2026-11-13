<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('evaluasi_tindak_lanjut', function (Blueprint $table) {
            $table->id();
            $table->foreignId('tindak_lanjut_id')->constrained('tindak_lanjut')->restrictOnDelete();
            $table->date('tanggal');
            $table->string('kesimpulan', 15);
            $table->text('catatan')->nullable();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('evaluasi_tindak_lanjut');
    }
};
