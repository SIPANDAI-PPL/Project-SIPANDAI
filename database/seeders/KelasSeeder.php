<?php

namespace Database\Seeders;

use App\Models\Kelas;
use App\Models\User;
use Illuminate\Database\Seeder;

class KelasSeeder extends Seeder
{
    /**
     * Dua kelas dummy. Guru dinanugraha dan andipratama sengaja tidak punya kelas.
     */
    public function run(): void
    {
        Kelas::create([
            'guru_id' => User::where('nama_pengguna', 'miastacia')->value('id'),
            'nama_kelas' => 'Melati',
            'kelompok' => 'TK A',
            'tahun_ajaran' => '2026/2027',
        ]);

        Kelas::create([
            'guru_id' => User::where('nama_pengguna', 'ayurizki')->value('id'),
            'nama_kelas' => 'Cendrawasih',
            'kelompok' => 'TK B',
            'tahun_ajaran' => '2026/2027',
        ]);
    }
}
