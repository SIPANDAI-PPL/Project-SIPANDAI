<?php

namespace Tests\Browser\PBI04_AspekAturanStrategi;

use App\Models\AlternatifStrategi;
use App\Models\AspekPerkembangan;
use App\Models\AturanStrategi;
use App\Models\Observasi;
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

    /**
     * Teks pilihan pada sebuah select di drawer aturan, tanpa placeholder.
     *
     * @return array<int, string>
     */
    private function teksPilihan(Browser $browser, string $id): array
    {
        return $browser->script(
            "return [...document.getElementById('{$id}').options]"
            .".filter(o => o.value !== '').map(o => o.text.trim());"
        )[0];
    }

    private function bukaDrawerTambahAturan(Browser $browser): Browser
    {
        return $browser->loginAs($this->admin)
            ->visitRoute('admin.aturan-strategi.index')
            ->click('@tombol-tambah-aturan')
            ->waitFor('@drawer-aturan');
    }

    // TC-04-14 | Negatif | Nama aspek kosong dan nama aspek duplikat ditolak
    public function test_tc_04_14_nama_aspek_kosong_dan_duplikat_menampilkan_pesan_error(): void
    {
        $kode = $this->kodeUnik();
        AspekPerkembangan::factory()->create(['nama' => "Bahasa {$kode}"]);
        $jumlahAwal = AspekPerkembangan::count();

        $this->browse(function (Browser $browser) use ($kode) {
            $browser->loginAs($this->admin)
                ->visitRoute('data-master.aspek.index')
                ->click('@tombol-tambah-aspek')
                ->waitFor('@modal-tambah')
                ->within('@modal-tambah', function (Browser $modal) {
                    $modal->click('@simpan-tambah');
                })
                // Nama kosong: modal terbuka lagi dengan pesan error dari server.
                ->waitFor('@salah-nama-tambah')
                ->assertSeeIn('@salah-nama-tambah', 'Nama wajib diisi.')
                ->within('@modal-tambah', function (Browser $modal) use ($kode) {
                    // Huruf berbeda tetap dianggap nama yang sama.
                    $modal->type('nama', "bahasa {$kode}")
                        ->click('@simpan-tambah');
                })
                ->waitForTextIn('@salah-nama-tambah', 'Nama aspek sudah terdaftar.')
                ->assertSeeIn('@salah-nama-tambah', 'Nama aspek sudah terdaftar.')
                ->assertMissing('@pesan-status');
        });

        // Expected Result - Database: tidak ada aspek baru.
        $this->assertSame($jumlahAwal, AspekPerkembangan::count());
    }

    // TC-04-15 | Negatif | Menghapus aspek yang sudah dipakai observasi ditolak
    public function test_tc_04_15_hapus_aspek_yang_sudah_dipakai_menampilkan_pesan_error(): void
    {
        $kode = $this->kodeUnik();
        $aspek = AspekPerkembangan::factory()->create(['nama' => "Seni {$kode}"]);

        $this->browse(function (Browser $browser) use ($kode, $aspek) {
            $browser->loginAs($this->admin)
                ->visitRoute('data-master.aspek.index', ['q' => $kode])
                ->waitFor('@baris-aspek-'.$aspek->id)
                ->click('@menu-aksi-'.$aspek->id)
                ->waitFor('@aksi-hapus-'.$aspek->id)
                ->click('@aksi-hapus-'.$aspek->id)
                ->waitFor('@modal-hapus');

            // Selama dialog terbuka, guru mencatat observasi dengan aspek ini.
            Observasi::factory()->create(['aspek_id' => $aspek->id]);

            $browser->click('@konfirmasi-hapus')
                // Expected Result - Tampilan: server menolak dengan pesan error.
                ->waitFor('@pesan-gagal-hapus')
                ->assertSeeIn('@pesan-gagal-hapus', 'Aspek sudah dipakai pada 1 observasi dan 0 aturan sehingga tidak dapat dihapus.')
                ->assertMissing('@pesan-status');
        });

        $this->assertDatabaseHas('aspek_perkembangan', ['id' => $aspek->id]);
    }

    // TC-04-12 | Negatif | Strategi nonaktif tidak bisa dipilih di aturan
    public function test_tc_04_12_strategi_nonaktif_tidak_muncul_di_pilihan(): void
    {
        $kode = $this->kodeUnik();
        $aktif = StrategiPembelajaran::factory()->create(['nama' => "Media visual {$kode}"]);
        $nonaktif = StrategiPembelajaran::factory()->nonaktif()->create(['nama' => "Pendampingan individu {$kode}"]);

        $this->browse(function (Browser $browser) use ($aktif, $nonaktif) {
            $this->bukaDrawerTambahAturan($browser);

            foreach (['strategi_asal_id', 'strategi_alternatif_id'] as $id) {
                $pilihan = $this->teksPilihan($browser, $id);
                $this->assertContains($aktif->nama, $pilihan);
                $this->assertNotContains($nonaktif->nama, $pilihan);
            }
        });
    }

    // TC-04-17 | Negatif | Minimal kejadian kurang dari 2 ditolak
    public function test_tc_04_17_minimal_kejadian_kurang_dari_2_ditahan_form(): void
    {
        $kode = $this->kodeUnik();
        $asal = StrategiPembelajaran::factory()->create(['nama' => "Permainan gerak {$kode}"]);
        $alternatif = StrategiPembelajaran::factory()->create(['nama' => "Media visual {$kode}"]);
        $jumlahAwal = AturanStrategi::count();

        $this->browse(function (Browser $browser) use ($asal, $alternatif) {
            $this->bukaDrawerTambahAturan($browser)
                ->within('@drawer-aturan', function (Browser $drawer) use ($asal, $alternatif) {
                    $drawer->select('respons_pemicu', 'kurang_tertarik')
                        ->select('strategi_asal_id', (string) $asal->id)
                        ->clear('minimal_kejadian')
                        ->type('minimal_kejadian', '1')
                        ->select('strategi_alternatif_id', (string) $alternatif->id)
                        ->assertSee('Minimal 2 kali agar saran tidak disusun dari satu pengamatan.')
                        ->click('@simpan-aturan');
                })
                ->pause(500)
                // Expected Result - Tampilan: browser menahan form dan drawer tetap terbuka.
                ->assertVisible('@drawer-aturan')
                ->assertMissing('@pesan-status');

            $this->assertTrue((bool) $browser->script("return document.getElementById('minimal_kejadian').validity.rangeUnderflow;")[0]);
        });

        // Expected Result - Database: aturan tidak tersimpan.
        $this->assertSame($jumlahAwal, AturanStrategi::count());
    }

    // TC-04-18 | Negatif | Aturan dengan kombinasi yang sama ditolak
    public function test_tc_04_18_aturan_duplikat_menampilkan_pesan_error(): void
    {
        $kode = $this->kodeUnik();
        $asal = StrategiPembelajaran::factory()->create(['nama' => "Permainan gerak {$kode}"]);
        $alternatif = StrategiPembelajaran::factory()->create(['nama' => "Media visual {$kode}"]);
        $alternatifLain = StrategiPembelajaran::factory()->create(['nama' => "Kelompok kecil {$kode}"]);
        AturanStrategi::factory()->create([
            'strategi_asal_id' => $asal->id,
            'respons_pemicu' => 'kurang_tertarik',
            'aspek_id' => null,
            'strategi_alternatif_id' => $alternatif->id,
        ]);
        $jumlahAwal = AturanStrategi::count();

        $this->browse(function (Browser $browser) use ($asal, $alternatifLain) {
            $this->bukaDrawerTambahAturan($browser)
                ->within('@drawer-aturan', function (Browser $drawer) use ($asal, $alternatifLain) {
                    // Strategi asal, respons, dan aspek ("Semua aspek") sama dengan aturan yang sudah ada.
                    $drawer->select('respons_pemicu', 'kurang_tertarik')
                        ->select('strategi_asal_id', (string) $asal->id)
                        ->select('strategi_alternatif_id', (string) $alternatifLain->id);
                })
                ->waitForReload(function (Browser $browser) {
                    $browser->click('@simpan-aturan');
                })
                // Expected Result - Tampilan: drawer terbuka lagi dengan pesan error dari server.
                ->waitFor('@salah-aturan')
                ->assertSeeIn('@salah-aturan', 'Aturan dengan strategi asal, respons pemicu, dan aspek yang sama sudah ada.')
                ->assertMissing('@pesan-status');
        });

        $this->assertSame($jumlahAwal, AturanStrategi::count());
        $this->assertDatabaseMissing('aturan_strategi', ['strategi_alternatif_id' => $alternatifLain->id]);
    }

    // TC-04-19 | Negatif | Respons pemicu hanya empat kategori resmi
    public function test_tc_04_19_respons_pemicu_hanya_empat_kategori(): void
    {
        $this->browse(function (Browser $browser) {
            $this->bukaDrawerTambahAturan($browser);

            // Expected Result - Tampilan: tidak ada pilihan di luar empat kategori respons.
            $this->assertSame([
                'Terlibat aktif',
                'Terlibat dengan pendampingan',
                'Kurang tertarik',
                'Belum terlibat',
            ], $this->teksPilihan($browser, 'respons_pemicu'));
        });
    }

    // TC-04-20 | Negatif | Menghapus aturan yang sudah menghasilkan saran ditolak
    public function test_tc_04_20_hapus_aturan_yang_sudah_menghasilkan_saran_menampilkan_pesan_error(): void
    {
        $kode = $this->kodeUnik();
        $asal = StrategiPembelajaran::factory()->create(['nama' => "Permainan gerak {$kode}"]);
        $alternatif = StrategiPembelajaran::factory()->create(['nama' => "Media visual {$kode}"]);
        $aturanDipakai = AturanStrategi::factory()->create([
            'strategi_asal_id' => $asal->id,
            'strategi_alternatif_id' => $alternatif->id,
            'respons_pemicu' => 'kurang_tertarik',
        ]);
        AlternatifStrategi::factory()->create(['strategi_id' => $alternatif->id, 'aturan_id' => $aturanDipakai->id]);
        $aturanBaru = AturanStrategi::factory()->create([
            'strategi_asal_id' => $asal->id,
            'strategi_alternatif_id' => $alternatif->id,
            'respons_pemicu' => 'belum_terlibat',
        ]);

        $this->browse(function (Browser $browser) use ($kode, $aturanDipakai, $aturanBaru, $alternatif) {
            // Aturan yang sudah menghasilkan saran tidak punya menu Hapus.
            $browser->loginAs($this->admin)
                ->visitRoute('admin.aturan-strategi.index', ['q' => $kode])
                ->waitFor('@baris-aturan-'.$aturanDipakai->id)
                ->click('@menu-aksi-'.$aturanDipakai->id)
                ->waitFor('@aksi-nonaktifkan-'.$aturanDipakai->id)
                ->assertMissing('@aksi-hapus-'.$aturanDipakai->id)
                // Muat ulang supaya menu yang terbuka tidak menutupi tombol baris lain.
                ->refresh()
                ->waitFor('@menu-aksi-'.$aturanBaru->id)
                ->click('@menu-aksi-'.$aturanBaru->id)
                ->waitFor('@aksi-hapus-'.$aturanBaru->id)
                ->click('@aksi-hapus-'.$aturanBaru->id)
                ->waitFor('@modal-hapus');

            // Selama dialog terbuka, sistem menyusun saran dari aturan ini.
            AlternatifStrategi::factory()->create(['strategi_id' => $alternatif->id, 'aturan_id' => $aturanBaru->id]);

            $browser->click('@konfirmasi-hapus')
                // Expected Result - Tampilan: server menolak dengan pesan error.
                ->waitFor('@pesan-gagal-hapus')
                ->assertSeeIn('@pesan-gagal-hapus', 'Aturan sudah menghasilkan 1 saran dan hanya dapat dinonaktifkan.')
                ->assertMissing('@pesan-status');
        });

        $this->assertDatabaseHas('aturan_strategi', ['id' => $aturanBaru->id]);
    }

    // TC-04-22 | Error | Tamu membuka halaman aturan strategi
    public function test_tc_04_22_tamu_diarahkan_ke_login(): void
    {
        $this->browse(function (Browser $browser) {
            $browser->logout()
                ->visitRoute('admin.aturan-strategi.index')
                ->waitForLocation('/login')
                ->assertPathIs('/login')
                ->waitForText('Masuk ke SIPANDAI');
        });
    }
}
