<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\View\View;

class KelolaObservasiController extends Controller
{
    // Fitur: PBI-8 Ubah dan Hapus Observasi beserta Riwayat Perubahan

    public function show(Request $request, string $id): View
    {
        $observasi = $this->dataObservasi($id);
        $observasi['dihapus'] = $request->query('demo') === 'dihapus';

        if ($observasi['dihapus']) {
            array_unshift($observasi['riwayat'], [
                'waktu' => '19 Jul 2024, 08.45',
                'oleh' => 'Admin SIPANDAI',
                'aksi' => 'DELETE',
                'alasan' => 'Data duplikat dengan observasi OBS-2024-0151.',
                'perubahan' => [
                    ['field' => 'Status Data', 'sebelum' => 'Aktif', 'sesudah' => 'Dihapus', 'tipe' => 'teks'],
                ],
            ]);
        }

        return view('observasi.kelola.detail', ['observasi' => $observasi]);
    }

    public function edit(string $id): View
    {
        return view('observasi.kelola.ubah', ['observasi' => $this->dataObservasi($id)]);
    }

    /**
     * Data dummy untuk tahap tampilan saja (belum memakai database).
     */
    private function dataObservasi(string $id): array
    {
        return [
            'kode' => $id,
            'dibuat_pada' => '16 Jul 2024, 09.12',
            'terakhir_diubah_pada' => '18 Jul 2024, 14.30',
            'terakhir_diubah_oleh' => 'Mia Stacia',
            'tanggal' => '2024-07-16',
            'tanggal_tampil' => '16 Jul 2024',
            'tanggal_panjang' => '16 Juli 2024',
            'anak' => [
                'nama' => 'Dedek Piplup',
                'inisial' => 'DP',
                'nis' => '20240012',
                'kelas' => 'Kelas A',
                'rombel' => 'Kelas A - Kelompok Belajar Melati',
            ],
            'aktivitas' => 'Bermain Bebas di Sentra Balok',
            'indikator' => [
                [
                    'nomor' => '01',
                    'judul' => 'Nilai Agama & Moral',
                    'deskripsi' => 'Mengenal nilai agama dan berdoa sebelum dan sesudah kegiatan.',
                    'nilai' => 'BSH',
                    'nilai_awal' => 'BSH',
                ],
                [
                    'nomor' => '02',
                    'judul' => 'Fisik Motorik Kasar',
                    'deskripsi' => 'Keseimbangan tubuh saat berjalan di atas papan titian.',
                    'nilai' => 'BSB',
                    'nilai_awal' => 'BSB',
                ],
                [
                    'nomor' => '03',
                    'judul' => 'Fisik Motorik Halus',
                    'deskripsi' => 'Koordinasi tangan-mata saat menyusun balok dengan rapi.',
                    'nilai' => 'BSH',
                    'nilai_awal' => 'MB',
                ],
                [
                    'nomor' => '04',
                    'judul' => 'Kognitif',
                    'deskripsi' => 'Mengenal konsep angka 1–10 dan bentuk geometri.',
                    'nilai' => 'BSH',
                    'nilai_awal' => 'BSH',
                ],
                [
                    'nomor' => '05',
                    'judul' => 'Bahasa & Komunikasi',
                    'deskripsi' => 'Menceritakan kembali pengalaman dengan kalimat lengkap.',
                    'nilai' => 'BSB',
                    'nilai_awal' => 'MB',
                ],
                [
                    'nomor' => '06',
                    'judul' => 'Sosial Emosional',
                    'deskripsi' => 'Mau berbagi dan bekerja sama dengan teman.',
                    'nilai' => 'MB',
                    'nilai_awal' => 'MB',
                ],
            ],
            'catatan' => [
                'isi' => 'Anak sangat antusias menyusun balok tinggi bersama temannya. Koordinasi tangan dan mata menunjukkan peningkatan yang signifikan.',
                'maks' => 500,
            ],
            'rencana' => [
                'isi' => 'Lanjutkan stimulasi merangkai bentuk balok geometri yang lebih kompleks.',
                'maks' => 300,
            ],
            'alasan' => 'Salah memilih kategori pada indikator motorik halus dan bahasa saat input awal.',
            'riwayat' => [
                [
                    'waktu' => '18 Jul 2024, 14.30',
                    'oleh' => 'Mia Stacia',
                    'aksi' => 'UPDATE',
                    'alasan' => 'Salah memilih kategori pada indikator motorik halus dan bahasa saat input awal.',
                    'perubahan' => [
                        ['field' => 'Fisik Motorik Halus', 'sebelum' => 'MB', 'sesudah' => 'BSH', 'tipe' => 'kategori'],
                        ['field' => 'Bahasa & Komunikasi', 'sebelum' => 'MB', 'sesudah' => 'BSB', 'tipe' => 'kategori'],
                    ],
                ],
                [
                    'waktu' => '17 Jul 2024, 10.15',
                    'oleh' => 'Mia Stacia',
                    'aksi' => 'UPDATE',
                    'alasan' => 'Menyesuaikan tanggal pengamatan dengan jurnal kegiatan kelas.',
                    'perubahan' => [
                        ['field' => 'Tanggal Pengamatan', 'sebelum' => '15 Jul 2024', 'sesudah' => '16 Jul 2024', 'tipe' => 'teks'],
                        ['field' => 'Catatan Anekdot', 'sebelum' => 'Anak bermain balok bersama teman.', 'sesudah' => 'Anak sangat antusias menyusun balok tinggi bersama temannya.', 'tipe' => 'teks'],
                    ],
                ],
                [
                    'waktu' => '16 Jul 2024, 09.12',
                    'oleh' => 'Mia Stacia',
                    'aksi' => 'DIBUAT',
                    'alasan' => 'Observasi awal dicatat dari kegiatan bermain bebas di sentra balok.',
                    'perubahan' => [],
                ],
            ],
        ];
    }
}
