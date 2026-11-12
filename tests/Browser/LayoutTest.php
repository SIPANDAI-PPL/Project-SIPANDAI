<?php

namespace Tests\Browser;

use App\Models\User;
use Illuminate\Foundation\Testing\DatabaseMigrations;
use Laravel\Dusk\Browser;
use Tests\DuskTestCase;

// Fitur: PBI-22 Layout bersama (header + sidebar)
class LayoutTest extends DuskTestCase
{
    use DatabaseMigrations;

    public function test_hamburger_menutup_sidebar_desktop_dan_pilihan_tersimpan(): void
    {
        $admin = User::factory()->admin()->create();

        $this->browse(function (Browser $browser) use ($admin) {
            $browser->resize(1920, 1080)
                ->loginAs($admin)
                ->visitRoute('admin.dashboard')
                ->assertVisible('@tombol-sidebar')
                ->assertVisible('@sidebar')
                ->assertAttribute('@tombol-sidebar', 'aria-expanded', 'true')
                ->click('@tombol-sidebar')
                ->waitUntilMissing('@sidebar')
                ->assertAttribute('@tombol-sidebar', 'aria-expanded', 'false')
                // Pilihan tetap berlaku setelah pindah halaman.
                ->visitRoute('admin.guru.index')
                ->assertMissing('@sidebar')
                ->click('@tombol-sidebar')
                ->waitFor('@sidebar')
                ->refresh()
                ->assertVisible('@sidebar');
        });
    }

    public function test_hamburger_membuka_sidebar_di_layar_kecil(): void
    {
        $admin = User::factory()->admin()->create();

        $this->browse(function (Browser $browser) use ($admin) {
            $browser->resize(400, 800)
                ->loginAs($admin)
                ->visitRoute('admin.dashboard')
                ->assertVisible('@tombol-sidebar')
                ->assertMissing('@sidebar')
                ->click('@tombol-sidebar')
                ->waitFor('@sidebar')
                ->assertSeeIn('@sidebar', 'Kelola Akun Guru')
                ->click('@tombol-sidebar')
                ->waitUntilMissing('@sidebar')
                ->resize(1920, 1080);
        });
    }

    public function test_tombol_lihat_kata_sandi_di_halaman_login(): void
    {
        $this->browse(function (Browser $browser) {
            $browser->visit('/login')
                ->assertSee('Masuk ke SIPANDAI')
                ->assertAttribute('#kata_sandi', 'type', 'password')
                ->click('[data-lihat-sandi="kata_sandi"]')
                ->assertAttribute('#kata_sandi', 'type', 'text')
                ->assertAttribute('[data-lihat-sandi="kata_sandi"]', 'aria-label', 'Sembunyikan kata sandi')
                ->click('[data-lihat-sandi="kata_sandi"]')
                ->assertAttribute('#kata_sandi', 'type', 'password');
        });
    }
}
