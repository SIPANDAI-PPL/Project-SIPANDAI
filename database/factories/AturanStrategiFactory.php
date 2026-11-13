<?php

namespace Database\Factories;

use App\Enums\KategoriRespons;
use App\Models\AturanStrategi;
use App\Models\StrategiPembelajaran;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<AturanStrategi>
 */
class AturanStrategiFactory extends Factory
{
    protected $model = AturanStrategi::class;

    /**
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'aspek_id' => null,
            'strategi_asal_id' => StrategiPembelajaran::factory(),
            'respons_pemicu' => KategoriRespons::KurangTertarik,
            'minimal_kejadian' => 3,
            'strategi_alternatif_id' => StrategiPembelajaran::factory(),
            'keterangan' => fake('id_ID')->sentence(),
            'aktif' => true,
        ];
    }
}
