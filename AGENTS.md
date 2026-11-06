# Panduan Agent – SIPANDAI

## Stack

- PHP 8.3, Laravel 12, MySQL 8, Blade + Tailwind + Vite.

- Test: PHPUnit (tests/Feature, tests/Unit) dan Laravel Dusk (tests/Browser).

- Semua perintah PHP, Composer, dan npm dijalankan di container Docker:

  - `docker compose exec laravel.test php artisan ...`

  - `docker compose exec laravel.test composer ...`

  - `docker compose exec laravel.test npm ...`

  - Jangan jalankan `php`, `composer`, atau `npm` langsung di Windows.

## Aturan kode

- UI, pesan validasi, dan pesan error berbahasa Indonesia sederhana.

- Nama tabel dan kolom mengikuti ERD proposal (bahasa Indonesia), contoh: `users.kata_sandi`, `users.peran`, `users.aktif`.

- Model `User` memakai kolom `kata_sandi`: set `protected $authPasswordName = 'kata_sandi';`.

- Kategori respons hanya dari enum `App\Enums\KategoriRespons`.

- Guru hanya boleh mengakses data anak di kelas yang diampunya (NFR-04).

- Data yang sudah dipakai tidak dihapus permanen; gunakan kolom `aktif`.

## Struktur folder (per fitur, bukan per PBI)

- Controller dikelompokkan per area: `app/Http/Controllers/Admin/`, `app/Http/Controllers/DataMaster/`, `app/Http/Controllers/Guru/`, `app/Http/Controllers/Auth/`.

- Nama folder dan class memakai PascalCase tanpa spasi atau tanda hubung, contoh: `Admin/KelolaAkunGuruController.php`.

- Model tetap di `app/Models/`, migration tetap di `database/migrations/`.

- View per fitur memakai kebab-case: `resources/views/admin/akun-guru/`, `resources/views/data-master/`.

- Form Request di `app/Http/Requests/<Area>/`, contoh: `Admin/SimpanAkunGuruRequest.php`.

- Test mengikuti area controller: `tests/Feature/Admin/KelolaAkunGuruTest.php`.

- Nama method test memuat ID test case, contoh: `test_tc_01_04_menambah_akun_guru_valid`.

- Baris pertama di dalam class controller berisi komentar fitur, contoh: `// Fitur: PBI-1 Kelola Akun Guru dan Reset Kata Sandi`.

## Alur kerja

- Setiap tugas mengacu ke satu issue Jira (`PBI-x`). Baca kriteria penerimaan dan test case di issue itu.

- Satu branch per PBI. Nama branch disalin dari panel Development di issue Jira (Create branch), contoh `PBI-1-kelola-akun-guru-dan-reset-kata-sandi`. Branch dibuat dari main terbaru. Pesan commit wajib diawali issue key, contoh `PBI-1 Tambah validasi email unik`.

- Tulis test lebih dulu sesuai ID test case, lalu implementasi.

- Pastikan `docker compose exec laravel.test php artisan test` lulus sebelum selesai.

- Jangan commit atau push. Pengguna yang melakukan commit.

- Jangan mengubah `.env`, `compose.yaml`, atau `.github/workflows` tanpa diminta.

## Data

- Hanya pakai data dummy dari factory/seeder. Jangan pernah memakai data anak asli.
