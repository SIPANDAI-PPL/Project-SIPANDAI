<?php

/*
|--------------------------------------------------------------------------
| Menu sidebar per peran (PBI-22 Layout Bersama)
|--------------------------------------------------------------------------
| Satu-satunya tempat daftar menu sidebar. Dipakai oleh komponen
| resources/views/components/layouts/app.blade.php.
|
| Setiap item:
| - label: teks menu.
| - rute:  nama rute tujuan. Item hanya tampil jika Route::has(rute) bernilai true,
|          jadi menu PBI lain otomatis muncul setelah rutenya masuk main.
| - aktif: pola request()->routeIs() untuk menandai item aktif (string atau array).
*/

return [
    'admin' => [
        ['label' => 'Dashboard', 'rute' => 'admin.dashboard', 'aktif' => 'admin.dashboard'],
        ['label' => 'Kelola Akun Guru', 'rute' => 'admin.guru.index', 'aktif' => 'admin.guru.*'],
        ['label' => 'Data Master', 'rute' => 'data-master.aktivitas.index', 'aktif' => 'data-master.*'],
        ['label' => 'Aturan Strategi', 'rute' => 'admin.aturan-strategi.index', 'aktif' => 'admin.aturan-strategi.*'],
        // PBI-3. Nama rute mengikuti branch PBI-3 saat ini; sesuaikan jika rute diganti saat review.
        ['label' => 'Kelola Kelas & Penugasan', 'rute' => 'classes.index', 'aktif' => 'classes.*'],
    ],

    'guru' => [
        ['label' => 'Dashboard', 'rute' => 'guru.dashboard', 'aktif' => 'guru.dashboard'],
        // PBI-5
        ['label' => 'Data Anak', 'rute' => 'guru.anak.index', 'aktif' => 'guru.anak.*'],
        ['label' => 'Aktivitas & Strategi', 'rute' => 'data-master.aktivitas.index', 'aktif' => 'data-master.*'],
        // PBI-7
        ['label' => 'Observasi', 'rute' => 'guru.observasi.index', 'aktif' => 'guru.observasi.*'],
        // PBI-11 Riwayat Observasi tidak masuk sidebar: halaman ini per anak
        // (butuh parameter anak), jadi dibuka dari Data Anak.
        // PBI-15
        ['label' => 'Alternatif Strategi', 'rute' => 'alternatif-strategi.index', 'aktif' => 'alternatif-strategi.*'],
    ],
];
