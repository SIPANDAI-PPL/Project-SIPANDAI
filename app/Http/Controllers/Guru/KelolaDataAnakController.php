<?php

namespace App\Http\Controllers\Guru;

use App\Http\Controllers\Controller;

class KelolaDataAnakController extends Controller
{
    // Fitur: PBI-5 Kelola Data Dasar Anak (tampilan saja, data hardcode)

    public function index()
    {
        $kelas = $this->daftarKelas();
        $semuaAnak = $this->daftarAnak();

        return view('guru.anak.index', [
            'daftarKelas' => $kelas,
            'kelasTerpilih' => $kelas[0],
            'anakAktif' => array_values(array_filter($semuaAnak, fn ($a) => $a['aktif'])),
            'anakNonaktif' => array_values(array_filter($semuaAnak, fn ($a) => ! $a['aktif'])),
        ]);
    }

    public function show(int $anak)
    {
        $data = collect($this->daftarAnak())->firstWhere('id', $anak);

        abort_if($data === null, 404);

        return view('guru.anak.show', [
            'anak' => $data,
            'daftarKelas' => $this->daftarKelas(),
        ]);
    }

    private function daftarKelas(): array
    {
        return [
            ['id' => 1, 'label' => 'Kelompok B1 · 2026/2027 Ganjil (4 anak)', 'nama' => 'Kelompok B1 · 2026/2027 Ganjil'],
            ['id' => 2, 'label' => 'Kelompok B2 · 2026/2027 Ganjil (1 anak)', 'nama' => 'Kelompok B2 · 2026/2027 Ganjil'],
        ];
    }

    private function daftarAnak(): array
    {
        $kelas = 'Kelompok B1 · 2026/2027 Ganjil';

        return [
            ['id' => 1, 'nama' => 'Anak Contoh 1', 'panggilan' => 'Contoh 1', 'inisial' => 'AC', 'usia' => '3 th 4 bln', 'tanggal_lahir' => '2023-07-07', 'kelas' => $kelas, 'jumlah_observasi' => 14, 'kategori' => 'Kurang tertarik', 'waktu' => 'Hari ini', 'aktif' => true],
            ['id' => 2, 'nama' => 'Anak Contoh 2', 'panggilan' => 'Contoh 2', 'inisial' => 'AC', 'usia' => '3 th 9 bln', 'tanggal_lahir' => '2023-02-10', 'kelas' => $kelas, 'jumlah_observasi' => 2, 'kategori' => 'Terlibat aktif', 'waktu' => 'Kemarin', 'aktif' => true],
            ['id' => 3, 'nama' => 'Anak Contoh 3', 'panggilan' => 'Contoh 3', 'inisial' => 'AC', 'usia' => '4 th 2 bln', 'tanggal_lahir' => '2022-09-05', 'kelas' => $kelas, 'jumlah_observasi' => 5, 'kategori' => 'Terlibat dengan pendampingan', 'waktu' => '8 hari', 'aktif' => true],
            ['id' => 4, 'nama' => 'Anak Contoh 4', 'panggilan' => 'Contoh 4', 'inisial' => 'AC', 'usia' => '2 th 6 bln', 'tanggal_lahir' => '2024-05-01', 'kelas' => $kelas, 'jumlah_observasi' => 0, 'kategori' => null, 'waktu' => null, 'aktif' => true],
            ['id' => 5, 'nama' => 'Anak Contoh 5', 'panggilan' => 'Contoh 5', 'inisial' => 'AC', 'usia' => '4 th 4 bln', 'tanggal_lahir' => '2022-07-03', 'kelas' => $kelas, 'jumlah_observasi' => 3, 'kategori' => 'Terlibat aktif', 'waktu' => '12 hari', 'aktif' => false],
        ];
    }
}
