<?php

namespace Database\Factories;

use App\Models\Anak;
use App\Models\Kelas;
use App\Models\Laporan;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Laporan>
 */
class LaporanFactory extends Factory
{
    protected $model = Laporan::class;

    /**
     * Bawaan: laporan kelas. Pakai state untukAnak() untuk laporan per anak.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            // Nilai jenis: anak, kelas.
            'jenis' => 'kelas',
            'anak_id' => null,
            'kelas_id' => Kelas::factory(),
            'guru_id' => fn (array $atribut) => Kelas::find($atribut['kelas_id'])->guru_id,
            'periode_mulai' => '2026-11-01',
            'periode_selesai' => '2026-11-30',
        ];
    }

    public function untukAnak(): static
    {
        return $this->state(fn () => [
            'jenis' => 'anak',
            'anak_id' => Anak::factory(),
            'kelas_id' => null,
            'guru_id' => fn (array $atribut) => Anak::find($atribut['anak_id'])->kelas->guru_id,
        ]);
    }
}
