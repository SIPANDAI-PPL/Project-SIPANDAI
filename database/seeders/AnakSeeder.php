<?php

namespace Database\Seeders;

use App\Models\Anak;
use App\Models\Kelas;
use Illuminate\Database\Seeder;

class AnakSeeder extends Seeder
{
    /**
     * Lima anak dummy per kelas. Nama dibuat Faker, bukan data anak asli.
     */
    public function run(): void
    {
        foreach (Kelas::all() as $kelas) {
            Anak::factory()->count(5)->create(['kelas_id' => $kelas->id]);
        }
    }
}
