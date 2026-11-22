<?php

namespace Tests\Browser\PBI04_AspekAturanStrategi;

use App\Models\AspekPerkembangan;
use App\Models\AturanStrategi;
use App\Models\StrategiPembelajaran;
use App\Models\User;
use Laravel\Dusk\Browser;
use Tests\Browser\Concerns\DataUnik;
use Tests\DuskTestCase;

// PBI-04 Kelola Aspek Perkembangan dan Aturan Alternatif Strategi
// Menambah aspek, menambah aturan, validasi aturan, dan hak akses halaman aturan
class AturanStrategiTest extends DuskTestCase
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

    // TC-04-02 | Positif | Admin menambah aspek perkembangan lewat modal
    public function test_tc_04_02_admin_menambah_aspek_lewat_modal(): void
    {
        $kode = $this->kodeUnik();
        $jumlahAwal = AspekPerkembangan::count();

        $this->browse(function (Browser $browser) use ($kode) {
            $browser->loginAs($this->admin)
                ->visitRoute('data-master.aspek.index')
                ->assertSee('Daftar Aspek Perkembangan')
                ->click('@tombol-tambah-aspek')
                ->waitFor('@modal-tambah')
                ->within('@modal-tambah', function (Browser $modal) use ($kode) {
                    $modal->type('nama', "Kemampuan literasi awal {$kode}")
                        ->type('deskripsi', 'Mengenal huruf dan bunyi.')
                        ->click('@simpan-tambah');
                })
                ->waitForTextIn('@pesan-status', "Aspek Kemampuan literasi awal {$kode} berhasil ditambahkan.")
                ->assertPathIs('/data-master/aspek')
                // Daftar aspek terus bertambah setiap run, jadi disaring dengan kode unik test ini.
                ->visitRoute('data-master.aspek.index', ['q' => $kode])
                ->assertSee("Kemampuan literasi awal {$kode}");
        });

        $this->assertSame($jumlahAwal + 1, AspekPerkembangan::count());
        $this->assertDatabaseHas('aspek_perkembangan', [
            'nama' => "Kemampuan literasi awal {$kode}",
            'deskripsi' => 'Mengenal huruf dan bunyi.',
        ]);
    }

    // TC-04-07 | Positif | Admin menambah aturan strategi dan melihat pratinjau kalimat
    public function test_tc_04_07_admin_menambah_aturan_dengan_pratinjau_kalimat(): void
    {
        $kode = $this->kodeUnik();
        $fisik = AspekPerkembangan::factory()->create(['nama' => "Fisik-motorik {$kode}"]);
        $mediaVisual = StrategiPembelajaran::factory()->create(['nama' => "Media visual {$kode}"]);
        $permainanGerak = StrategiPembelajaran::factory()->create(['nama' => "Permainan gerak {$kode}"]);
        $jumlahAwal = AturanStrategi::count();

        $this->browse(function (Browser $browser) use ($kode, $fisik, $mediaVisual, $permainanGerak) {
            $browser->loginAs($this->admin)
                ->visitRoute('admin.aturan-strategi.index')
                ->click('@tombol-tambah-aturan')
                ->waitFor('@drawer-aturan')
                ->within('@drawer-aturan', function (Browser $drawer) use ($kode, $fisik, $mediaVisual, $permainanGerak) {
                    $drawer->select('respons_pemicu', 'kurang_tertarik')
                        ->select('strategi_asal_id', (string) $permainanGerak->id)
                        ->type('minimal_kejadian', '3')
                        ->select('aspek_id', (string) $fisik->id)
                        ->select('strategi_alternatif_id', (string) $mediaVisual->id)
                        ->assertSeeIn('@pratinjau-aturan', 'Kurang tertarik')
                        ->assertSeeIn('@pratinjau-aturan', "Permainan gerak {$kode}")
                        ->assertSeeIn('@pratinjau-aturan', "Media visual {$kode}")
                        ->assertSeeIn('@pratinjau-aturan', "Aspek: Fisik-motorik {$kode}")
                        ->click('@simpan-aturan');
                })
                ->waitForTextIn('@pesan-status', 'Aturan strategi berhasil ditambahkan.')
                ->assertPathIs('/admin/aturan-strategi')
                // Daftar aturan disaring dengan nama strategi milik test ini.
                ->visitRoute('admin.aturan-strategi.index', ['q' => $kode])
                ->assertSee("Permainan gerak {$kode}")
                ->assertSee("Media visual {$kode}");
        });

        $this->assertSame($jumlahAwal + 1, AturanStrategi::count());
        $this->assertDatabaseHas('aturan_strategi', [
            'respons_pemicu' => 'kurang_tertarik',
            'minimal_kejadian' => 3,
            'strategi_asal_id' => $permainanGerak->id,
            'strategi_alternatif_id' => $mediaVisual->id,
            'aspek_id' => $fisik->id,
            'aktif' => true,
        ]);
    }

    // TC-04-16 | Negatif | Strategi asal sama dengan strategi alternatif ditolak
    public function test_tc_04_16_strategi_asal_sama_dengan_alternatif_ditolak_lewat_browser(): void
    {
        $kode = $this->kodeUnik();
        $mediaVisual = StrategiPembelajaran::factory()->create(['nama' => "Media visual {$kode}"]);
        StrategiPembelajaran::factory()->create(['nama' => "Kelompok kecil {$kode}"]);
        $jumlahAwal = AturanStrategi::count();

        $pesan = 'Strategi asal dan strategi alternatif tidak boleh sama.';

        $this->browse(function (Browser $browser) use ($mediaVisual, $pesan) {
            $browser->loginAs($this->admin)
                ->visitRoute('admin.aturan-strategi.index')
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

        $this->assertSame($jumlahAwal, AturanStrategi::count());
        $this->assertDatabaseMissing('aturan_strategi', ['strategi_asal_id' => $mediaVisual->id]);
    }

    // TC-04-21 | Error | Guru tidak dapat membuka halaman aturan strategi
    public function test_tc_04_21_guru_tidak_dapat_mengakses_aturan_strategi(): void
    {
        $kode = $this->kodeUnik();
        $guru = User::factory()->create([
            'nama' => "Ayu Rizki {$kode}",
            'nama_pengguna' => "ayu{$kode}",
            'email' => "ayu.{$kode}@tkceria.sch.id",
        ]);
        $jumlahAwal = AturanStrategi::count();

        $this->browse(function (Browser $browser) use ($guru) {
            $browser->loginAs($guru)
                ->visitRoute('guru.dashboard')
                // Menu Aturan Strategi tidak tampil untuk guru.
                ->assertDontSeeLink('Aturan Strategi')
                ->visitRoute('admin.aturan-strategi.index')
                ->assertSee('403')
                ->assertMissing('@tombol-tambah-aturan');
        });

        $this->assertSame($jumlahAwal, AturanStrategi::count());
    }
}
