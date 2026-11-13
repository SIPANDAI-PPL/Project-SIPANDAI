<?php

namespace Tests\Browser;

use App\Models\AspekPerkembangan;
use App\Models\StrategiPembelajaran;
use App\Models\User;
use Illuminate\Foundation\Testing\DatabaseMigrations;
use Laravel\Dusk\Browser;
use Tests\DuskTestCase;

class AturanStrategiTest extends DuskTestCase
{
    use DatabaseMigrations;

    public function test_tc_04_02_admin_menambah_aspek_lewat_modal(): void
    {
        $admin = User::factory()->admin()->create(['nama' => 'Admin SIPANDAI']);
        AspekPerkembangan::factory()->create(['nama' => 'Bahasa']);

        $this->browse(function (Browser $browser) use ($admin) {
            $browser->loginAs($admin)
                ->visit('/data-master/aspek')
                ->assertSee('Daftar Aspek Perkembangan')
                ->click('@tombol-tambah-aspek')
                ->waitFor('@modal-tambah')
                ->within('@modal-tambah', function (Browser $modal) {
                    $modal->type('nama', 'Kemampuan literasi awal')
                        ->type('deskripsi', 'Mengenal huruf dan bunyi.')
                        ->click('@simpan-tambah');
                })
                ->waitForText('Aspek Kemampuan literasi awal berhasil ditambahkan.')
                ->assertPathIs('/data-master/aspek')
                ->assertSee('Kemampuan literasi awal');
        });

        $this->assertDatabaseHas('aspek_perkembangan', ['nama' => 'Kemampuan literasi awal']);
    }

    public function test_tc_04_07_admin_menambah_aturan_dengan_pratinjau_kalimat(): void
    {
        $admin = User::factory()->admin()->create(['nama' => 'Admin SIPANDAI']);
        $fisik = AspekPerkembangan::factory()->create(['nama' => 'Fisik-motorik']);
        $mediaVisual = StrategiPembelajaran::factory()->create(['nama' => 'Media visual']);
        $permainanGerak = StrategiPembelajaran::factory()->create(['nama' => 'Permainan gerak']);

        $this->browse(function (Browser $browser) use ($admin, $fisik, $mediaVisual, $permainanGerak) {
            $browser->loginAs($admin)
                ->visit('/admin/aturan-strategi')
                ->click('@tombol-tambah-aturan')
                ->waitFor('@drawer-aturan')
                ->within('@drawer-aturan', function (Browser $drawer) use ($fisik, $mediaVisual, $permainanGerak) {
                    $drawer->select('respons_pemicu', 'kurang_tertarik')
                        ->select('strategi_asal_id', (string) $permainanGerak->id)
                        ->type('minimal_kejadian', '3')
                        ->select('aspek_id', (string) $fisik->id)
                        ->select('strategi_alternatif_id', (string) $mediaVisual->id)
                        ->assertSeeIn('@pratinjau-aturan', 'Kurang tertarik')
                        ->assertSeeIn('@pratinjau-aturan', 'Permainan gerak')
                        ->assertSeeIn('@pratinjau-aturan', 'Media visual')
                        ->assertSeeIn('@pratinjau-aturan', 'Aspek: Fisik-motorik')
                        ->click('@simpan-aturan');
                })
                ->waitForText('Aturan strategi berhasil ditambahkan.')
                ->assertPathIs('/admin/aturan-strategi')
                ->assertSee('1 Aturan');
        });

        $this->assertDatabaseHas('aturan_strategi', [
            'respons_pemicu' => 'kurang_tertarik',
            'minimal_kejadian' => 3,
            'strategi_asal_id' => $permainanGerak->id,
            'strategi_alternatif_id' => $mediaVisual->id,
            'aspek_id' => $fisik->id,
            'aktif' => true,
        ]);
    }

    public function test_tc_04_16_strategi_asal_sama_dengan_alternatif_ditolak_lewat_browser(): void
    {
        $admin = User::factory()->admin()->create(['nama' => 'Admin SIPANDAI']);
        $mediaVisual = StrategiPembelajaran::factory()->create(['nama' => 'Media visual']);
        StrategiPembelajaran::factory()->create(['nama' => 'Kelompok kecil']);

        $pesan = 'Strategi asal dan strategi alternatif tidak boleh sama.';

        $this->browse(function (Browser $browser) use ($admin, $mediaVisual, $pesan) {
            $browser->loginAs($admin)
                ->visit('/admin/aturan-strategi')
                ->click('@tombol-tambah-aturan')
                ->waitFor('@drawer-aturan')
                ->within('@drawer-aturan', function (Browser $drawer) use ($mediaVisual, $pesan) {
                    $drawer->select('respons_pemicu', 'kurang_tertarik')
                        ->select('strategi_asal_id', (string) $mediaVisual->id)
                        ->select('strategi_alternatif_id', (string) $mediaVisual->id)
                        // Pesan muncul langsung di form sebelum disimpan.
                        ->assertSeeIn('@salah-strategi-alternatif', $pesan);
                })
                ->waitForReload(function (Browser $browser) {
                    $browser->click('@simpan-aturan');
                })
                // Server menolak; drawer terbuka lagi dengan pesan yang sama.
                ->waitFor('@drawer-aturan')
                ->assertSeeIn('@salah-strategi-alternatif', $pesan);
        });

        $this->assertDatabaseCount('aturan_strategi', 0);
    }
}
