<?php

namespace Tests\Browser\PBI22_SetupLayout;

use Laravel\Dusk\Browser;
use Tests\DuskTestCase;

// PBI-22 Setup Layout: halaman beranda (landing page)
// Halaman beranda hanya menampilkan konten statis dan tidak menulis ke database,
// jadi test di file ini tidak memakai pengecekan database.
class LandingTest extends DuskTestCase
{
    // Tanpa DatabaseMigrations: isi database sipandai_dusk tidak dihapus.

    // Positif | Tamu membuka beranda lalu menuju halaman login
    public function test_tamu_membuka_beranda_lalu_ke_halaman_login(): void
    {
        $this->browse(function (Browser $browser) {
            // Sesi browser dipakai ulang antar-test, jadi keluar dulu agar tampil sebagai tamu.
            $browser->logout()
                ->visit('/')
                ->waitForText('Ajar lebih tepat besok.')
                ->assertVisible('img[alt="Maskot SIPANDAI"]')
                ->click('@tombol-utama')
                ->waitForLocation('/login')
                ->assertSee('Masuk ke SIPANDAI');
        });
    }

    // Positif | Link anchor menggulir ke bagian Fitur dan header menjadi solid
    public function test_link_anchor_menggulir_ke_bagian_dan_header_menjadi_solid(): void
    {
        $this->browse(function (Browser $browser) {
            $browser->resize(1440, 900)
                ->visit('/')
                ->waitForText('Ajar lebih tepat besok.')
                ->assertAttributeMissing('@header-beranda', 'data-tergulir')
                ->clickLink('Fitur')
                ->waitUntil("document.getElementById('header-beranda').hasAttribute('data-tergulir')")
                ->waitForText('Pencatatan observasi per anak')
                ->waitUntil("document.getElementById('fitur').getBoundingClientRect().top < 200");

            // Judul bagian tidak tertutup header sticky.
            $atasJudul = $browser->script("return document.getElementById('judul-fitur').getBoundingClientRect().top;")[0];
            $tinggiHeader = $browser->script("return document.getElementById('header-beranda').offsetHeight;")[0];
            $this->assertGreaterThanOrEqual($tinggiHeader, $atasJudul);

            $browser->resize(1920, 1080);
        });
    }

    // Batas | Beranda tidak bisa digulir ke samping di layar selebar 375 px
    public function test_beranda_tanpa_scroll_horizontal_di_layar_kecil(): void
    {
        $this->browse(function (Browser $browser) {
            $browser->resize(375, 800)
                ->visit('/')
                ->waitForText('Ajar lebih tepat besok.');

            $lebar = $browser->script('return [document.documentElement.scrollWidth, window.innerWidth];')[0];
            $this->assertLessThanOrEqual($lebar[1], $lebar[0]);

            $browser->resize(1920, 1080);
        });
    }
}
