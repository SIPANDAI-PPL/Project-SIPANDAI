<?php

namespace Tests\Browser\PBI01_KelolaAkunGuru;

use App\Models\User;
use Laravel\Dusk\Browser;
use Tests\Browser\Concerns\DataUnik;
use Tests\DuskTestCase;

// PBI-01 Kelola Akun Guru dan Reset Kata Sandi
// TS-01-05 Hak akses dan login akun guru
class LoginTest extends DuskTestCase
{
    // Tanpa DatabaseMigrations: data hasil test tetap tersimpan di database sipandai_dusk.
    use DataUnik;

    // Fondasi login/logout (dipakai semua PBI)
    public function test_login_lalu_logout_lewat_browser(): void
    {
        $kode = $this->kodeUnik();

        // Pre Condition: akun guru aktif dibuat lewat factory.
        $guru = User::factory()->create([
            'nama' => "Mia Stacia {$kode}",
            'nama_pengguna' => "mia{$kode}",
            'email' => "mia.{$kode}@tkceria.sch.id",
            'kata_sandi' => 'GuruMia#2026',
        ]);

        $this->browse(function (Browser $browser) use ($guru) {
            $browser->logout()
                ->visit('/login')
                ->type('login', $guru->nama_pengguna)
                ->type('kata_sandi', 'GuruMia#2026')
                ->press('Masuk')
                ->waitForLocation('/guru/dashboard')
                ->assertPathIs('/guru/dashboard')
                ->assertSee("Selamat datang, {$guru->nama}")
                ->press('Keluar')
                ->waitForLocation('/login')
                ->assertPathIs('/login');
        });
    }

    // TC-01-15 | Error | Guru nonaktif mencoba login
    public function test_tc_01_15_akun_nonaktif_tidak_dapat_login(): void
    {
        $kode = $this->kodeUnik();

        // Pre Condition: akun guru nonaktif dibuat lewat factory.
        $guru = User::factory()->nonaktif()->create([
            'nama' => "Dina Nugraha {$kode}",
            'nama_pengguna' => "dina{$kode}",
            'email' => "dina.{$kode}@tkceria.sch.id",
            'kata_sandi' => 'GuruDina#2026',
        ]);

        $this->browse(function (Browser $browser) use ($guru) {
            $browser->logout()
                ->visit('/login')
                ->type('login', $guru->nama_pengguna)
                ->type('kata_sandi', 'GuruDina#2026')
                ->press('Masuk')
                // Path tetap /login, jadi tunggu pesan dari halaman hasil redirect.
                ->waitForText('Akun tidak aktif, hubungi admin.')
                ->assertPathIs('/login')
                ->assertSee('Akun tidak aktif, hubungi admin.');
        });

        // Expected Result - Database: akun tetap nonaktif.
        $this->assertDatabaseHas('users', ['id' => $guru->id, 'aktif' => 0]);
    }
}
