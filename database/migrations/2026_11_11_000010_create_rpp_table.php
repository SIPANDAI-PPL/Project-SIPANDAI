<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('rpp', function (Blueprint $table) {
            $table->id();
            $table->foreignId('guru_id')->constrained('users')->restrictOnDelete();
            $table->foreignId('kelas_id')->constrained('kelas')->restrictOnDelete();
            $table->string('judul', 150);
            $table->string('jenis', 10);
            $table->date('tanggal_mulai');
            $table->date('tanggal_selesai');
            $table->string('nama_file', 255);
            $table->string('path_file', 255);
            $table->string('tipe_file', 10);
            $table->integer('ukuran_file');
            $table->text('catatan')->nullable();
            $table->timestamp('diunggah_pada')->nullable();
            $table->timestamp('diubah_pada')->nullable();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('rpp');
    }
};
