<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Kolom usia sekarang disimpan dalam tahun penuh.
     * Hitung ulang semua baris lama (yang masih kosong atau berisi bulan).
     */
    public function up(): void
    {
        $hariIni = Carbon::today();

        DB::table('anak')->whereNotNull('tanggal_lahir')->orderBy('id')
            ->each(function (object $anak) use ($hariIni) {
                $lahir = Carbon::parse($anak->tanggal_lahir)->startOfDay();
                $tahun = $lahir->greaterThan($hariIni) ? 0 : intdiv((int) $lahir->diffInMonths($hariIni), 12);

                DB::table('anak')->where('id', $anak->id)->update(['usia' => $tahun]);
            });
    }

    /**
     * Data lama tidak dikembalikan ke bulan; kolom cukup dikosongkan.
     */
    public function down(): void
    {
        DB::table('anak')->update(['usia' => null]);
    }
};
