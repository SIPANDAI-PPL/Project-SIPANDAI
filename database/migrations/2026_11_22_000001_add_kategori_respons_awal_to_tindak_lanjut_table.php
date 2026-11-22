<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

// PBI-17: respons awal anak saat tindak lanjut dicatat (nilai App\Enums\KategoriRespons).
return new class extends Migration
{
    public function up(): void
    {
        // Dijaga agar migrate tidak gagal di database lokal yang sudah punya kolom ini
        // dari percobaan sebelumnya.
        if (Schema::hasColumn('tindak_lanjut', 'kategori_respons_awal')) {
            return;
        }

        Schema::table('tindak_lanjut', function (Blueprint $table) {
            $table->string('kategori_respons_awal', 30)->after('status');
        });
    }

    public function down(): void
    {
        if (! Schema::hasColumn('tindak_lanjut', 'kategori_respons_awal')) {
            return;
        }

        Schema::table('tindak_lanjut', function (Blueprint $table) {
            $table->dropColumn('kategori_respons_awal');
        });
    }
};
