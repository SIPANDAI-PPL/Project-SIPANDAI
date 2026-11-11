<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('observasi', function (Blueprint $table) {
            $table->id();
            $table->foreignId('anak_id')->constrained('anak')->restrictOnDelete();
            $table->foreignId('guru_id')->constrained('users')->restrictOnDelete();
            $table->foreignId('aktivitas_id')->constrained('aktivitas')->restrictOnDelete();
            $table->foreignId('strategi_id')->constrained('strategi_pembelajaran')->restrictOnDelete();
            $table->foreignId('aspek_id')->constrained('aspek_perkembangan')->restrictOnDelete();
            $table->foreignId('rpp_id')->nullable()->constrained('rpp')->restrictOnDelete();
            $table->foreignId('tindak_lanjut_id')->nullable()->constrained('tindak_lanjut')->restrictOnDelete();
            $table->date('tanggal');
            $table->string('kategori_respons', 40);
            $table->text('catatan_pendukung')->nullable();
            $table->timestamp('dibuat_pada')->nullable();
            $table->timestamp('diubah_pada')->nullable();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('observasi');
    }
};
