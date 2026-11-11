<?php

namespace Database\Factories;

use App\Enums\KategoriRespons;
use App\Models\Aktivitas;
use App\Models\Anak;
use App\Models\AspekPerkembangan;
use App\Models\Observasi;
use App\Models\StrategiPembelajaran;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Observasi>
 */
class ObservasiFactory extends Factory
{
    protected $model = Observasi::class;

    /**
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'anak_id' => Anak::factory(),
            // Guru observasi mengikuti guru pengampu kelas anak (NFR-04).
            'guru_id' => fn (array $atribut) => Anak::find($atribut['anak_id'])->kelas->guru_id,
            'aktivitas_id' => Aktivitas::factory(),
            'strategi_id' => StrategiPembelajaran::factory(),
            'aspek_id' => AspekPerkembangan::factory(),
            'rpp_id' => null,
            'tindak_lanjut_id' => null,
            'tanggal' => fake()->dateTimeBetween('-1 month')->format('Y-m-d'),
            'kategori_respons' => fake()->randomElement(KategoriRespons::cases()),
            'catatan_pendukung' => fake('id_ID')->sentence(),
        ];
    }
}
