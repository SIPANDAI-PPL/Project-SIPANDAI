<?php

namespace Tests\Feature\Layout;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

// Fitur: PBI-22 Layout bersama (header + sidebar)
class LayoutTest extends TestCase
{
    use RefreshDatabase;

    public function test_admin_melihat_menu_admin(): void
    {
        $admin = User::factory()->admin()->create();

        $this->actingAs($admin)
            ->get(route('admin.dashboard'))
            ->assertOk()
            ->assertSee('SIPANDAI')
            ->assertSee($admin->nama)
            ->assertSee('href="'.route('admin.dashboard').'"', false)
            ->assertSee('href="'.route('admin.guru.index').'"', false)
            ->assertSee('href="'.route('data-master.aktivitas.index').'"', false)
            ->assertSee('href="'.route('admin.aturan-strategi.index').'"', false)
            ->assertSeeInOrder(['Dashboard', 'Kelola Akun Guru', 'Data Master', 'Aturan Strategi']);
    }

    public function test_guru_tidak_melihat_menu_khusus_admin(): void
    {
        $guru = User::factory()->create();

        $this->actingAs($guru)
            ->get(route('guru.dashboard'))
            ->assertOk()
            ->assertSee($guru->nama)
            ->assertSee('href="'.route('guru.dashboard').'"', false)
            ->assertSee('href="'.route('data-master.aktivitas.index').'"', false)
            ->assertSee('href="'.route('guru.riwayat-observasi').'"', false)
            ->assertDontSee('Kelola Akun Guru')
            ->assertDontSee('Aturan Strategi')
            ->assertDontSee('href="'.route('admin.guru.index').'"', false)
            ->assertDontSee('href="'.route('admin.aturan-strategi.index').'"', false);
    }

    public function test_header_menampilkan_peran_dan_tombol_keluar(): void
    {
        $this->actingAs(User::factory()->admin()->create())
            ->get(route('admin.dashboard'))
            ->assertSee('dusk="peran-pengguna">Admin<', false)
            ->assertSee('action="'.route('logout').'"', false)
            ->assertSee('Keluar');

        $this->actingAs(User::factory()->create())
            ->get(route('guru.dashboard'))
            ->assertSee('dusk="peran-pengguna">Guru<', false)
            ->assertSee('action="'.route('logout').'"', false)
            ->assertSee('Keluar');
    }

    public function test_item_aktif_punya_aria_current_page(): void
    {
        $admin = User::factory()->admin()->create();
        $guru = User::factory()->create();

        $this->assertMenuAktif($admin, route('admin.aturan-strategi.index'), 'Aturan Strategi');
        $this->assertMenuAktif($admin, route('admin.guru.index'), 'Kelola Akun Guru');
        // Tab Aspek berada di bawah Data Master, jadi menu Data Master tetap aktif.
        $this->assertMenuAktif($admin, route('data-master.aspek.index'), 'Data Master');
        $this->assertMenuAktif($guru, route('guru.riwayat-observasi'), 'Riwayat Observasi');
    }

    public function test_halaman_login_dan_ganti_sandi_tidak_memakai_layout(): void
    {
        $this->get(route('login'))
            ->assertOk()
            ->assertDontSee('aria-label="Menu utama"', false);

        $this->actingAs(User::factory()->wajibGantiSandi()->create())
            ->get(route('kata-sandi.edit'))
            ->assertOk()
            ->assertDontSee('aria-label="Menu utama"', false);
    }

    private function assertMenuAktif(User $pengguna, string $url, string $label): void
    {
        $html = $this->actingAs($pengguna)->get($url)->assertOk()->getContent();

        $this->assertMatchesRegularExpression('#<nav[^>]*aria-label="Menu utama"[^>]*>(.*?)</nav>#s', $html);
        preg_match('#<nav[^>]*aria-label="Menu utama"[^>]*>(.*?)</nav>#s', $html, $cocok);
        preg_match_all('#<a\b([^>]*)>\s*([^<]*?)\s*</a>#', $cocok[1], $tautan, PREG_SET_ORDER);

        $aktif = array_values(array_filter($tautan, fn ($a) => str_contains($a[1], 'aria-current="page"')));
        $this->assertCount(1, $aktif, "Harus tepat satu menu aktif di {$url}");
        $this->assertSame($label, html_entity_decode($aktif[0][2]));
    }
}
