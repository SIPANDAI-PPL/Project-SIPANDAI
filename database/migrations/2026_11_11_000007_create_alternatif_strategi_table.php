<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('alternatif_strategi', function (Blueprint $table) {
            $table->id();
            $table->foreignId('anak_id')->constrained('anak')->restrictOnDelete();
            $table->foreignId('strategi_id')->constrained('strategi_pembelajaran')->restrictOnDelete();
            $table->foreignId('aturan_id')->nullable()->constrained('aturan_strategi')->restrictOnDelete();
            $table->text('dasar_saran')->nullable();
            $table->string('status', 15)->default('disarankan');
            $table->timestamp('dibuat_pada')->nullable();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('alternatif_strategi');
    }
};
