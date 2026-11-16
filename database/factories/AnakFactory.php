<?php

namespace Database\Factories;

use App\Models\Anak;
use App\Models\Kelas;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Anak>
 */
class AnakFactory extends Factory
{
    protected $model = Anak::class;

    /**
     * Nama anak selalu dummy dari Faker, bukan data anak asli.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'kelas_id' => Kelas::factory(),
            'nama' => fake('id_ID')->name(),
            'nama_panggilan' => fake('id_ID')->firstName(),
            'tanggal_lahir' => fake()->dateTimeBetween('-6 years', '-4 years')->format('Y-m-d'),
            'aktif' => true,
        ];
    }

    public function nonaktif(): static
    {
        return $this->state(fn () => ['aktif' => false]);
    }
}
