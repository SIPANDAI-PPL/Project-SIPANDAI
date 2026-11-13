<?php

namespace Database\Factories;

use App\Models\AspekPerkembangan;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<AspekPerkembangan>
 */
class AspekPerkembanganFactory extends Factory
{
    protected $model = AspekPerkembangan::class;

    /**
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'nama' => fake()->randomElement([
                'Nilai Agama dan Moral', 'Fisik Motorik', 'Kognitif', 'Bahasa', 'Sosial Emosional', 'Seni',
            ]),
            'deskripsi' => fake('id_ID')->sentence(),
        ];
    }
}
