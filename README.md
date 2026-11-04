# SIPANDAI

Sistem Pendukung Keputusan untuk observasi dan tindak lanjut perkembangan anak TK.
Proyek mata kuliah Proyek Perangkat Lunak (PPL) – Kelompok A-C, SI4801.

## Tech stack

| Bagian | Teknologi |
| --- | --- |
| Backend | PHP 8.3, Laravel 12 |
| Database | MySQL 8 |
| Frontend | Blade, Tailwind CSS, Vite |
| Testing | PHPUnit (unit dan feature), Laravel Dusk (browser) |
| Lingkungan | Docker (Laravel Sail) |

PHP, Composer, Node.js, dan MySQL berjalan di dalam container. Kamu tidak perlu memasangnya di laptop.

## Prasyarat

- Docker Desktop (status **Engine running**)
- Git for Windows
- VS Code
- Akun GitHub yang sudah tergabung di organisasi `SIPANDAI-PPL`

Atur Git sekali saja. Pakai email yang sama dengan akun Jira, supaya commit tertaut ke issue:

    git config --global core.autocrlf false
    git config --global user.name "Nama Kamu"
    git config --global user.email "email-akun-jira@contoh.com"

## Setup pertama kali (Windows, PowerShell)

1. Clone repo dan masuk ke foldernya. Hindari path yang mengandung `#` atau spasi.

       git clone https://github.com/SIPANDAI-PPL/Project-SIPANDAI.git
       cd Project-SIPANDAI

2. Install dependensi PHP lewat container:

       docker run --rm -e COMPOSER_PROCESS_TIMEOUT=0 -v "${PWD}:/opt" -w /opt laravelsail/php83-composer:latest composer install

3. Buat file `.env`:

       copy .env.example .env

4. Buat shortcut `sail` di PowerShell (sekali saja):

       if (!(Test-Path $PROFILE)) { New-Item -Path $PROFILE -ItemType File -Force }
       Add-Content $PROFILE 'function sail { docker compose exec laravel.test @args }'
       . $PROFILE

   Kalau muncul error "running scripts is disabled", jalankan
   `Set-ExecutionPolicy -Scope CurrentUser RemoteSigned`, lalu ulangi baris terakhir.

5. Nyalakan container. Proses pertama butuh sekitar 5–15 menit.

       docker compose up -d

6. Siapkan aplikasi:

       sail php artisan key:generate
       sail php artisan migrate
       sail npm install
       sail npm run build

7. Buka http://localhost. Halaman Laravel harus muncul.

8. Siapkan Dusk: salin `.env` menjadi `.env.dusk.local`, lalu ubah tiga baris ini:

       APP_URL=http://laravel.test
       DB_DATABASE=testing
       DUSK_DRIVER_URL=http://selenium:4444/wd/hub

## Perintah sehari-hari

| Kebutuhan | Perintah |
| --- | --- |
| Nyalakan container | `docker compose up -d` |
| Matikan container | `docker compose down` |
| Migrasi database | `sail php artisan migrate` |
| Isi data dummy | `sail php artisan db:seed` |
| Jalankan PHPUnit | `sail php artisan test` |
| Jalankan Dusk | `sail php artisan dusk` |
| Mode development frontend | `sail npm run dev` |
| Tambah package PHP | `sail composer require nama/package` |

## Alur kerja tim

1. Ambil PBI dari board Jira, lalu pindahkan ke **In Progress**.
2. Ambil `main` terbaru, lalu buat branch dengan awalan issue key:

       git checkout main
       git pull
       git checkout -b PBI-1-kelola-akun-guru

3. Setiap pesan commit diawali issue key:

       PBI-1 Tambah migration kolom peran dan aktif pada users

4. Commit terakhir memakai smart commit resolve:

       PBI-1 #resolve Selesaikan kelola akun guru dan reset kata sandi

5. Push branch, lalu buat pull request ke `main`. Tunggu sampai CI (`tests`) hijau.
6. Minta review dari 1 anggota tim, lalu pindahkan kartu ke **In Review**.
7. Lengkapi lampiran di issue Jira: prompt AI, screenshot hasil prompting, dan screenshot test lulus.

## Aturan penting

- Jangan commit `.env` dan `.env.dusk.local`.
- Pakai **data dummy** dari seeder atau factory. Jangan pernah memasukkan data anak asli ke repo, seeder, atau prompt AI.
- Setiap fitur wajib punya test sesuai test case di deskripsi PBI.
- Lokasi file test: `tests/Feature/...` untuk PHPUnit dan `tests/Browser/...` untuk Dusk.

## Masalah umum

| Masalah | Solusi |
| --- | --- |
| `composer` timeout saat install | Pastikan memakai `-e COMPOSER_PROCESS_TIMEOUT=0` |
| `EACCES: permission denied` saat `npm install` | Pastikan shortcut `sail` tidak memakai `-u sail` |
| Error `tempnam()` di browser | Pastikan `SUPERVISOR_PHP_USER: 'root'` ada di `compose.yaml`, lalu jalankan `docker compose up -d` dan `sail php artisan optimize:clear` |
| Port 80 atau 3306 sudah dipakai | Matikan aplikasi lain yang memakai port itu (XAMPP, Laragon, MySQL lokal) |