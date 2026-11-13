<?php

namespace Tests\Browser\Auth;

use App\Models\User;
use Illuminate\Foundation\Testing\DatabaseMigrations;
use Laravel\Dusk\Browser;
use Tests\DuskTestCase;

class LoginTest extends DuskTestCase
{
    use DatabaseMigrations;

    public function test_login_lalu_logout_lewat_browser(): void
    {
        User::factory()->create([
            'nama' => 'Mia Stacia',
            'nama_pengguna' => 'miastacia',
            'kata_sandi' => 'GuruMia#2026',
        ]);

        $this->browse(function (Browser $browser) {
            $browser->visit('/login')
                ->type('login', 'miastacia')
                ->type('kata_sandi', 'GuruMia#2026')
                ->press('Masuk')
                ->waitForLocation('/guru/dashboard')
                ->assertPathIs('/guru/dashboard')
                ->assertSee('Selamat datang, Mia Stacia')
                ->press('Keluar')
                ->waitForLocation('/login')
                ->assertPathIs('/login');
        });
    }

    public function test_akun_nonaktif_melihat_pesan_lewat_browser(): void
    {
        User::factory()->nonaktif()->create([
            'nama_pengguna' => 'dinanugraha',
            'kata_sandi' => 'GuruDina#2026',
        ]);

        $this->browse(function (Browser $browser) {
            $browser->visit('/login')
                ->type('login', 'dinanugraha')
                ->type('kata_sandi', 'GuruDina#2026')
                ->press('Masuk')
                // Path tetap /login, jadi tunggu pesan dari halaman hasil redirect.
                ->waitForText('Akun tidak aktif, hubungi admin.')
                ->assertPathIs('/login')
                ->assertSee('Akun tidak aktif, hubungi admin.');
        });
    }
}
