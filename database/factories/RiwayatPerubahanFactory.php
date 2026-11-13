<?php

namespace Database\Factories;

use App\Models\Observasi;
use App\Models\RiwayatPerubahan;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<RiwayatPerubahan>
 */
class RiwayatPerubahanFactory extends Factory
{
    protected $model = RiwayatPerubahan::class;

    /**
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'observasi_id' => Observasi::factory(),
            'dilakukan_oleh' => fn (array $atribut) => Observasi::find($atribut['observasi_id'])->guru_id,
            'tindak_lanjut_id' => null,
            // Nilai aksi: tambah, ubah, hapus.
            'aksi' => fake()->randomElement(['tambah', 'ubah', 'hapus']),
            'keterangan' => fake('id_ID')->sentence(),
        ];
    }
}
