<?php

namespace Database\Factories;

use App\Models\StrategiPembelajaran;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<StrategiPembelajaran>
 */
class StrategiPembelajaranFactory extends Factory
{
    protected $model = StrategiPembelajaran::class;

    /**
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'nama' => fake()->randomElement([
                'Bercerita Lisan', 'Media Visual', 'Permainan Gerak', 'Kerja Kelompok', 'Demonstrasi',
            ]),
            'deskripsi' => fake('id_ID')->sentence(),
            'aktif' => true,
        ];
    }

    public function nonaktif(): static
    {
        return $this->state(fn () => ['aktif' => false]);
    }
}
