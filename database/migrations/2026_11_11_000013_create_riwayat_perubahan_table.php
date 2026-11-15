<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('riwayat_perubahan', function (Blueprint $table) {
            $table->id();
            $table->foreignId('dilakukan_oleh')->constrained('users')->restrictOnDelete();
            $table->foreignId('observasi_id')->nullable()->constrained('observasi')->restrictOnDelete();
            $table->foreignId('tindak_lanjut_id')->nullable()->constrained('tindak_lanjut')->restrictOnDelete();
            $table->string('aksi', 10);
            $table->text('keterangan')->nullable();
            $table->timestamp('waktu')->useCurrent();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('riwayat_perubahan');
    }
};
