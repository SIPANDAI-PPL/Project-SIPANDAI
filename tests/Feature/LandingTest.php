<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

// Fitur: PBI-22 Landing page
class LandingTest extends TestCase
{
    use RefreshDatabase;

    public function test_tamu_melihat_hero_dan_link_ke_login(): void
    {
        $this->get(route('beranda'))
            ->assertOk()
            ->assertSee('Catat hari ini,')
            ->assertSee('Ajar lebih tepat besok.')
            ->assertSee('Sistem Pendukung Pembelajaran untuk Guru TK')
            ->assertSee('Belum punya akun? Hubungi admin sekolah.')
            ->assertSee('href="'.route('login').'"', false)
            ->assertSee('alt="Maskot SIPANDAI"', false)
            ->assertDontSee('href="'.route('dashboard').'"', false);
    }

    public function test_semua_bagian_tampil_berurutan(): void
    {
        $this->get(route('beranda'))
            ->assertOk()
            ->assertSeeInOrder([
                'id="masalah"', 'Catatan observasi sering tercecer',
                'id="manfaat"', 'Manfaat untuk guru',
                'id="fitur"', 'Fitur SIPANDAI',
                'id="cara-kerja"', 'Cara kerja',
                'id="mulai"', 'Siap mulai?',
                '<footer', 'SIPANDAI – Sistem Pendukung Penyesuaian Pembelajaran Anak Usia Dini',
            ], false)
            ->assertSee('Tugas Besar Proyek Perangkat Lunak – Kelompok A-C SI4801, Telkom University')
            ->assertSee('Foto: Irwan Zahuri, Nasirun Khan (Pexels)');
    }

    public function test_bagian_manfaat_fitur_dan_cara_kerja_berisi_poin_yang_ditetapkan(): void
    {
        $this->get(route('beranda'))
            ->assertSeeInOrder([
                'Catatan rapi dan seragam',
                'Pola respons tiap anak terlihat',
                'Saran strategi, guru yang memutuskan',
                'Data anak terjaga',
            ])
            ->assertSeeInOrder([
                'Pencatatan observasi per anak',
                'Riwayat observasi',
                'Saran alternatif strategi',
                'Tindak lanjut dan evaluasi strategi',
                'Unggah RPP dan laporan',
                'Kelola data pembelajaran',
                'Kelola akun guru',
            ])
            ->assertSeeInOrder(['Catat observasi', 'Pantau pola respons', 'Terapkan dan evaluasi strategi']);
    }

    public function test_link_anchor_header_mengarah_ke_bagian(): void
    {
        $this->get(route('beranda'))
            ->assertSee('href="#manfaat"', false)
            ->assertSee('href="#fitur"', false)
            ->assertSee('href="#cara-kerja"', false);
    }

    public function test_foto_memakai_lazy_load_dan_alt(): void
    {
        $html = $this->get(route('beranda'))->getContent();

        foreach (['guru-1.webp', 'guru-2.webp', 'anak-1.webp'] as $foto) {
            $this->assertMatchesRegularExpression('#<img[^>]*'.preg_quote($foto, '#').'[^>]*loading="lazy"[^>]*alt="[^"]+"#s', $html);
            $this->assertFileExists(public_path("images/landing/{$foto}"));
        }
    }

    public function test_tidak_ada_pendaftaran_mandiri(): void
    {
        $html = $this->get(route('beranda'))->assertOk()->getContent();

        foreach (['Daftar', 'Sign up', 'Sign Up', 'Buat akun'] as $teks) {
            $this->assertStringNotContainsStringIgnoringCase($teks, $html);
        }
    }

    public function test_pengguna_yang_sudah_masuk_melihat_link_dashboard(): void
    {
        $this->actingAs(User::factory()->create())
            ->get(route('beranda'))
            ->assertOk()
            ->assertSee('Ke Dashboard')
            ->assertSee('Buka Dashboard')
            ->assertSee('href="'.route('dashboard').'"', false)
            ->assertDontSee('href="'.route('login').'"', false)
            ->assertDontSee('Belum punya akun? Hubungi admin sekolah.');
    }
}
