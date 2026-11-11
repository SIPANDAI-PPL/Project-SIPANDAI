<?php

namespace Tests\Browser\Auth;

use App\Models\User;
use Illuminate\Foundation\Testing\DatabaseMigrations;
use Laravel\Dusk\Browser;
use Tests\DuskTestCase;

class GantiKataSandiTest extends DuskTestCase
{
    use DatabaseMigrations;

    public function test_tc_01_09_guru_login_dengan_kata_sandi_sementara_lalu_menggantinya(): void
    {
        User::factory()->wajibGantiSandi()->create([
            'nama' => 'Ayu Rizki',
            'nama_pengguna' => 'ayurizki',
            'kata_sandi' => 'Sementara#2026',
        ]);

        $this->browse(function (Browser $browser) {
            $browser->visit('/login')
                ->type('login', 'ayurizki')
                ->type('kata_sandi', 'Sementara#2026')
                ->press('Masuk')
                ->assertPathIs('/ganti-kata-sandi')
                ->type('kata_sandi_baru', 'GuruAyu#2026')
                ->type('kata_sandi_baru_confirmation', 'GuruAyu#2026')
                ->press('Simpan kata sandi')
                ->assertPathIs('/guru/dashboard')
                ->assertSee('Kata sandi berhasil diganti.')
                ->assertSee('Selamat datang, Ayu Rizki')
                ->press('Keluar')
                ->assertPathIs('/login')
                ->type('login', 'ayurizki')
                ->type('kata_sandi', 'GuruAyu#2026')
                ->press('Masuk')
                ->assertPathIs('/guru/dashboard');
        });
    }
}
