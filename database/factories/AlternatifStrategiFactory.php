<?php

namespace Database\Factories;

use App\Models\AlternatifStrategi;
use App\Models\Anak;
use App\Models\StrategiPembelajaran;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<AlternatifStrategi>
 */
class AlternatifStrategiFactory extends Factory
{
    protected $model = AlternatifStrategi::class;

    /**
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'anak_id' => Anak::factory(),
            'strategi_id' => StrategiPembelajaran::factory(),
            'aturan_id' => null,
            'dasar_saran' => fake('id_ID')->sentence(),
            // Nilai status: disarankan, dipilih, diabaikan.
            'status' => 'disarankan',
        ];
    }
}
