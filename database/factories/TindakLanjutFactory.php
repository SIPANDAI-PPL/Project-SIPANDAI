<?php

namespace Database\Factories;

use App\Models\AlternatifStrategi;
use App\Models\TindakLanjut;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<TindakLanjut>
 */
class TindakLanjutFactory extends Factory
{
    protected $model = TindakLanjut::class;

    /**
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'alternatif_id' => AlternatifStrategi::factory()->state(['status' => 'dipilih']),
            'rencana' => fake('id_ID')->sentence(),
            'alasan_dugaan_penyebab' => fake('id_ID')->sentence(),
            'tanggal_mulai' => fake()->dateTimeBetween('-1 month')->format('Y-m-d'),
            // Nilai status: direncanakan, berjalan, selesai.
            'status' => 'direncanakan',
        ];
    }
}
