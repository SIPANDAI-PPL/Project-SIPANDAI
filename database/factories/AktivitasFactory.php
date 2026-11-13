<?php

namespace Database\Factories;

use App\Models\Aktivitas;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Aktivitas>
 */
class AktivitasFactory extends Factory
{
    protected $model = Aktivitas::class;

    /**
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'nama' => fake()->randomElement([
                'Menyusun balok', 'Mewarnai gambar', 'Bernyanyi bersama', 'Menempel kertas', 'Bermain peran',
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
