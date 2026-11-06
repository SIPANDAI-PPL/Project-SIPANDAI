<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    use WithoutModelEvents;

    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        User::factory()->admin()->create([
            'nama' => 'Admin SIPANDAI',
            'nama_pengguna' => 'admin',
            'email' => 'admin@tkceria.sch.id',
        ]);

        User::factory()->create([
            'nama' => 'Mia Stacia',
            'nama_pengguna' => 'miastacia',
            'email' => 'mia.stacia@tkceria.sch.id',
            'nip' => '199203152021022001',
        ]);

        User::factory()->create([
            'nama' => 'Ayu Rizki',
            'nama_pengguna' => 'ayurizki',
            'email' => 'ayu.rizki@tkceria.sch.id',
            'nip' => '199408222022032002',
        ]);

        User::factory()->nonaktif()->create([
            'nama' => 'Dina Nugraha',
            'nama_pengguna' => 'dinanugraha',
            'email' => 'dina.nugraha@tkceria.sch.id',
            'nip' => '199011052019012003',
        ]);

        User::factory()->create([
            'nama' => 'Andi Pratama',
            'nama_pengguna' => 'andipratama',
            'email' => 'andi.pratama@tkceria.sch.id',
            'nip' => '199512012023011004',
        ]);
    }
}
