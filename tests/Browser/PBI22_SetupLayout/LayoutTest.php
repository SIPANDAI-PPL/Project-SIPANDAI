<?php

namespace Tests\Browser\PBI22_SetupLayout;

use App\Models\User;
use Laravel\Dusk\Browser;
use Tests\Browser\Concerns\DataUnik;
use Tests\DuskTestCase;

// PBI-22 Setup Layout: layout bersama (header dan sidebar)
// Header dan sidebar tidak menulis ke database. Test Positif di file ini hanya mengecek tampilan;
// test Negatif dan Error mengecek bahwa data pengguna tidak berubah.
class LayoutTest extends DuskTestCase
{
    // Tanpa DatabaseMigrations: data hasil test tetap tersimpan di database sipandai_dusk.
    use DataUnik;

    private User $admin;

    protected function setUp(): void
    {
        parent::setUp();

        $kode = $this->kodeUnik();

        // Pre Condition: admin dibuat lewat factory (langsung tersimpan di database).
        $this->admin = User::factory()->admin()->create([
            'nama' => "Admin SIPANDAI {$kode}",
            'nama_pengguna' => "admin{$kode}",
            'email' => "admin.{$kode}@tkceria.sch.id",
        ]);
    }

    private function buatGuru(): User
    {
        $kode = $this->kodeUnik();

        return User::factory()->create([
            'nama' => "Ayu Rizki {$kode}",
            'nama_pengguna' => "ayu{$kode}",
            'email' => "ayu.{$kode}@tkceria.sch.id",
        ]);
    }

    // Positif | Tombol hamburger menutup sidebar di desktop dan pilihan tetap tersimpan
    public function test_hamburger_menutup_sidebar_desktop_dan_pilihan_tersimpan(): void
    {
        $this->browse(function (Browser $browser) {
            $browser->resize(1920, 1080)
                ->loginAs($this->admin)
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

    // Positif | Tombol hamburger membuka dan menutup sidebar di layar kecil
    public function test_hamburger_membuka_sidebar_di_layar_kecil(): void
    {
        $this->browse(function (Browser $browser) {
            $browser->resize(400, 800)
                ->loginAs($this->admin)
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

    // Positif | Tombol lihat kata sandi di halaman login
    public function test_tombol_lihat_kata_sandi_di_halaman_login(): void
    {
        $this->browse(function (Browser $browser) {
            $browser->logout()
                ->visit('/login')
                ->assertSee('Masuk ke SIPANDAI')
                ->assertAttribute('#kata_sandi', 'type', 'password')
                ->click('[data-lihat-sandi="kata_sandi"]')
                ->assertAttribute('#kata_sandi', 'type', 'text')
                ->assertAttribute('[data-lihat-sandi="kata_sandi"]', 'aria-label', 'Sembunyikan kata sandi')
                ->click('[data-lihat-sandi="kata_sandi"]')
                ->assertAttribute('#kata_sandi', 'type', 'password');
        });
    }

    // Negatif | Guru tidak melihat menu khusus admin di sidebar
    public function test_guru_tidak_melihat_menu_khusus_admin(): void
    {
        $guru = $this->buatGuru();

        $this->browse(function (Browser $browser) use ($guru) {
            $browser->resize(1920, 1080)
                ->loginAs($guru)
                ->visitRoute('guru.dashboard')
                ->waitFor('@sidebar')
                ->assertSeeIn('@sidebar', 'Aktivitas & Strategi')
                ->assertDontSeeIn('@sidebar', 'Kelola Akun Guru')
                ->assertDontSeeIn('@sidebar', 'Aturan Strategi')
                ->assertDontSeeIn('@sidebar', 'Kelola Kelas & Penugasan');
        });

        // Peran guru tidak berubah walaupun halaman dibuka.
        $this->assertDatabaseHas('users', ['id' => $guru->id, 'peran' => 'guru', 'aktif' => true]);
    }

    // Error | Tamu yang membuka dashboard admin diarahkan ke halaman login
    public function test_tamu_membuka_dashboard_admin_diarahkan_ke_login(): void
    {
        $jumlahUserAwal = User::count();

        $this->browse(function (Browser $browser) {
            $browser->logout()
                ->visitRoute('admin.dashboard')
                ->waitForLocation('/login')
                ->assertPathIs('/login')
                ->assertMissing('@sidebar');
        });

        $this->assertSame($jumlahUserAwal, User::count());
    }
}
