<?php

namespace Database\Factories;

use App\Models\Kelas;
use App\Models\Rpp;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Rpp>
 */
class RppFactory extends Factory
{
    protected $model = Rpp::class;

    /**
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        $tema = fake()->randomElement(['Diriku', 'Keluargaku', 'Binatang', 'Tanaman']);
        $tipe = fake()->randomElement(['pdf', 'docx']);
        $namaFile = 'rpp-tema-'.strtolower($tema).'.'.$tipe;

        return [
            'kelas_id' => Kelas::factory(),
            // Guru RPP mengikuti guru pengampu kelas.
            'guru_id' => fn (array $atribut) => Kelas::find($atribut['kelas_id'])->guru_id,
            'judul' => 'RPP Tema '.$tema,
            // Nilai jenis: harian, mingguan.
            'jenis' => fake()->randomElement(['harian', 'mingguan']),
            'tanggal_mulai' => '2026-11-02',
            'tanggal_selesai' => '2026-11-06',
            'nama_file' => $namaFile,
            'path_file' => 'rpp/'.$namaFile,
            // Nilai tipe_file: pdf, docx.
            'tipe_file' => $tipe,
            // Ukuran file dalam byte.
            'ukuran_file' => fake()->numberBetween(50_000, 2_000_000),
            'catatan' => fake('id_ID')->sentence(),
        ];
    }
}
