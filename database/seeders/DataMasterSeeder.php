<?php

namespace Database\Seeders;

use App\Enums\KategoriRespons;
use App\Models\Aktivitas;
use App\Models\AspekPerkembangan;
use App\Models\AturanStrategi;
use App\Models\StrategiPembelajaran;
use Illuminate\Database\Seeder;

class DataMasterSeeder extends Seeder
{
    public function run(): void
    {
        foreach ([
            'Nilai Agama dan Moral',
            'Fisik Motorik',
            'Kognitif',
            'Bahasa',
            'Sosial Emosional',
            'Seni',
        ] as $nama) {
            AspekPerkembangan::create(['nama' => $nama]);
        }

        $strategi = [];

        foreach (['Bercerita Lisan', 'Media Visual', 'Permainan Gerak', 'Kerja Kelompok'] as $nama) {
            $strategi[$nama] = StrategiPembelajaran::create(['nama' => $nama]);
        }

        foreach ([
            'Menyusun balok',
            'Mewarnai gambar',
            'Bernyanyi bersama',
            'Menempel kertas',
            'Bermain peran',
        ] as $nama) {
            Aktivitas::create(['nama' => $nama]);
        }

        AturanStrategi::create([
            'aspek_id' => null,
            'strategi_asal_id' => $strategi['Bercerita Lisan']->id,
            'respons_pemicu' => KategoriRespons::KurangTertarik,
            'minimal_kejadian' => 3,
            'strategi_alternatif_id' => $strategi['Media Visual']->id,
            'aktif' => true,
        ]);
    }
}
