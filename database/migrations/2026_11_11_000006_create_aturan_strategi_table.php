<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('aturan_strategi', function (Blueprint $table) {
            $table->id();
            $table->foreignId('aspek_id')->nullable()->constrained('aspek_perkembangan')->restrictOnDelete();
            $table->foreignId('strategi_asal_id')->constrained('strategi_pembelajaran')->restrictOnDelete();
            $table->string('respons_pemicu', 40);
            $table->integer('minimal_kejadian');
            $table->foreignId('strategi_alternatif_id')->constrained('strategi_pembelajaran')->restrictOnDelete();
            $table->text('keterangan')->nullable();
            $table->boolean('aktif')->default(true);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('aturan_strategi');
    }
};
