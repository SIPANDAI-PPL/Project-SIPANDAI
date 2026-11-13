<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('laporan', function (Blueprint $table) {
            $table->id();
            $table->foreignId('guru_id')->constrained('users')->restrictOnDelete();
            $table->string('jenis', 10);
            $table->foreignId('anak_id')->nullable()->constrained('anak')->restrictOnDelete();
            $table->foreignId('kelas_id')->nullable()->constrained('kelas')->restrictOnDelete();
            $table->date('periode_mulai');
            $table->date('periode_selesai');
            $table->timestamp('dibuat_pada')->nullable();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('laporan');
    }
};
