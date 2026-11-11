<?php

namespace Database\Factories;

use App\Models\EvaluasiTindakLanjut;
use App\Models\TindakLanjut;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<EvaluasiTindakLanjut>
 */
class EvaluasiTindakLanjutFactory extends Factory
{
    protected $model = EvaluasiTindakLanjut::class;

    /**
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'tindak_lanjut_id' => TindakLanjut::factory(),
            'tanggal' => fake()->dateTimeBetween('-1 month')->format('Y-m-d'),
            // Nilai kesimpulan: dilanjutkan, diubah, dihentikan.
            'kesimpulan' => fake()->randomElement(['dilanjutkan', 'diubah', 'dihentikan']),
            'catatan' => fake('id_ID')->sentence(),
        ];
    }
}
