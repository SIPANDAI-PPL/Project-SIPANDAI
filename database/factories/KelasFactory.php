<?php

namespace Database\Factories;

use App\Models\Kelas;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Kelas>
 */
class KelasFactory extends Factory
{
    protected $model = Kelas::class;

    /**
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'guru_id' => User::factory(),
            'nama_kelas' => fake()->randomElement(['Melati', 'Mawar', 'Anggrek', 'Cendrawasih', 'Merpati']),
            'kelompok' => fake()->randomElement(['TK A', 'TK B']),
            'tahun_ajaran' => '2026/2027',
        ];
    }
}
