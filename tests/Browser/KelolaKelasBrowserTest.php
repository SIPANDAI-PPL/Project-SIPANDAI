<?php

namespace Tests\Browser;

use App\Models\Kelas;
use App\Models\User;
use Illuminate\Foundation\Testing\DatabaseMigrations;
use Laravel\Dusk\Browser;
use Tests\DuskTestCase;

/**
 * PBI-3 Kelola Kelas, Tahun Ajaran, dan Penugasan Guru Pengampu.
 * Skenario TS.Kls.03.01 – TS.Kls.03.06 (Feature Test + Dusk).
 *
 * Jalankan dengan GUI: php artisan dusk --browse --filter=KelolaKelasBrowserTest
 * lalu buka noVNC Selenium (port 7900). Jeda visual hanya aktif di mode --browse.
 */
class KelolaKelasBrowserTest extends DuskTestCase
{
    use DatabaseMigrations;

    private User $admin;

    private User $guruMia;

    private User $guruAyu;

    protected function setUp(): void
    {
        parent::setUp();

        $this->admin = User::factory()->admin()->create([
            'nama' => 'Admin SIPANDAI',
            'nama_pengguna' => 'admin',
            'kata_sandi' => 'Admin#2026',
        ]);

        $this->guruMia = User::factory()->create([
            'nama' => 'Mia Stacia',
            'nama_pengguna' => 'miastacia',
            'kata_sandi' => 'Guru#2026',
        ]);

        $this->guruAyu = User::factory()->create(['nama' => 'Ayu Rizki', 'nama_pengguna' => 'ayurizki']);

        User::factory()->nonaktif()->create(['nama' => 'Guru Cuti', 'nama_pengguna' => 'gurucuti']);
    }

    /**
     * Masuk lewat form login, lalu buka menu Kelola Kelas & Penugasan di sidebar.
     */
    private function masukDanBukaKelolaKelas(Browser $browser): Browser
    {
        return $browser->visit('/login')
            ->tap(fn (Browser $b) => $this->visualPause($b))
            ->type('login', 'admin')
            ->tap(fn (Browser $b) => $this->visualPause($b))
            ->type('kata_sandi', 'Admin#2026')
            ->tap(fn (Browser $b) => $this->visualPause($b))
            ->press('Masuk')
            ->waitForLocation('/admin/dashboard')
            ->tap(fn (Browser $b) => $this->visualPause($b))
            ->clickLink('Kelola Kelas & Penugasan')
            ->waitForLocation('/classes')
            ->assertPathIs('/classes')
            ->tap(fn (Browser $b) => $this->visualPause($b));
    }

    private function buatKelas(array $atribut = []): Kelas
    {
        return Kelas::factory()->create(array_merge([
            'guru_id' => $this->guruMia->id,
            'nama_kelas' => 'Melati',
            'kelompok' => 'TK A',
            'tahun_ajaran' => '2026/2027',
        ], $atribut));
    }

    public function test_ts_kls_03_01_admin_melihat_halaman_kelola_kelas(): void
    {
        $melati = $this->buatKelas();
        $cendrawasih = $this->buatKelas([
            'guru_id' => $this->guruAyu->id,
            'nama_kelas' => 'Cendrawasih',
            'kelompok' => 'TK B',
        ]);

        $this->browse(function (Browser $browser) use ($melati, $cendrawasih) {
            $this->masukDanBukaKelolaKelas($browser)
                ->assertSee('Kelola Kelas dan Penugasan Guru')
                // Menu sidebar Kelola Kelas & Penugasan ditandai aktif.
                ->assertPresent('a[aria-current="page"][href$="/classes"]')
                ->select('@filter-tahun', '2026/2027')
                ->waitForTextIn('@tabel-kelas', 'Melati')
                ->tap(fn (Browser $b) => $this->visualPause($b))
                ->assertSeeIn('@baris-kelas-'.$melati->id, 'Melati')
                ->assertSeeIn('@baris-kelas-'.$melati->id, 'TK A')
                ->assertSeeIn('@baris-kelas-'.$melati->id, 'Mia Stacia')
                ->assertSeeIn('@baris-kelas-'.$cendrawasih->id, 'Cendrawasih')
                ->assertSeeIn('@baris-kelas-'.$cendrawasih->id, 'TK B')
                ->assertSeeIn('@baris-kelas-'.$cendrawasih->id, 'Ayu Rizki');
        });
    }

    public function test_ts_kls_03_02_guru_tidak_dapat_membuka_halaman_kelola_kelas(): void
    {
        $this->buatKelas();

        $this->browse(function (Browser $browser) {
            $browser->visit('/login')
                ->tap(fn (Browser $b) => $this->visualPause($b))
                ->type('login', 'miastacia')
                ->type('kata_sandi', 'Guru#2026')
                ->tap(fn (Browser $b) => $this->visualPause($b))
                ->press('Masuk')
                ->waitForLocation('/guru/dashboard')
                ->tap(fn (Browser $b) => $this->visualPause($b))
                // Menu kelola kelas tidak tampil untuk guru.
                ->assertDontSeeLink('Kelola Kelas & Penugasan')
                ->visit('/classes')
                ->tap(fn (Browser $b) => $this->visualPause($b))
                ->assertSee('403')
                ->assertDontSee('Kelola Kelas dan Penugasan Guru');
        });
    }

    public function test_ts_kls_03_03_admin_menambah_kelas_dan_tahun_ajaran_valid(): void
    {
        $this->browse(function (Browser $browser) {
            $this->masukDanBukaKelolaKelas($browser)
                ->click('@tombol-tambah-kelas')
                ->waitFor('#drawer-kelas[open]')
                ->tap(fn (Browser $b) => $this->visualPause($b))
                ->within('@drawer-kelas', function (Browser $drawer) {
                    $drawer->assertSee('Tambah Kelas')
                        // Guru nonaktif tidak bisa dipilih.
                        ->assertDontSeeIn('@pilih-guru', 'Guru Cuti')
                        ->type('nama_kelas', 'Kelas Ceria')
                        ->tap(fn (Browser $b) => $this->visualPause($b))
                        ->select('kelompok', 'TK A')
                        ->tap(fn (Browser $b) => $this->visualPause($b))
                        ->clear('tahun_ajaran')
                        ->type('tahun_ajaran', '2026/2027')
                        ->tap(fn (Browser $b) => $this->visualPause($b))
                        ->select('guru_id', (string) $this->guruMia->id)
                        ->tap(fn (Browser $b) => $this->visualPause($b))
                        ->click('@simpan-kelas');
                })
                ->waitForTextIn('@pesan-status', 'Kelas Kelas Ceria berhasil disimpan.')
                ->tap(fn (Browser $b) => $this->visualPause($b))
                ->assertQueryStringHas('tahun_ajaran', '2026/2027')
                ->assertSeeIn('@tabel-kelas', 'Kelas Ceria')
                ->assertSeeIn('@tabel-kelas', 'TK A')
                ->assertSeeIn('@tabel-kelas', 'Mia Stacia');
        });

        $this->assertDatabaseHas('kelas', [
            'nama_kelas' => 'Kelas Ceria',
            'kelompok' => 'TK A',
            'tahun_ajaran' => '2026/2027',
            'guru_id' => $this->guruMia->id,
        ]);
    }

    public function test_ts_kls_03_04_admin_menugaskan_guru_pengampu_pada_kelas(): void
    {
        $kelas = $this->buatKelas();

        $this->browse(function (Browser $browser) use ($kelas) {
            $this->masukDanBukaKelolaKelas($browser)
                ->select('@filter-tahun', '2026/2027')
                ->waitFor('@baris-kelas-'.$kelas->id)
                ->assertSeeIn('@baris-kelas-'.$kelas->id, 'Mia Stacia')
                ->tap(fn (Browser $b) => $this->visualPause($b))
                ->click('@ganti-guru-'.$kelas->id)
                ->waitFor('#modal-guru[open]')
                ->tap(fn (Browser $b) => $this->visualPause($b))
                ->within('@modal-guru', function (Browser $modal) {
                    $modal->assertSee('Ganti Guru Pengampu')
                        ->assertSee('Melati')
                        ->assertDontSeeIn('@pilih-guru-pengampu', 'Guru Cuti')
                        ->select('guru_id', (string) $this->guruAyu->id)
                        ->tap(fn (Browser $b) => $this->visualPause($b))
                        ->click('@simpan-guru');
                })
                ->waitForTextIn('@pesan-status', 'Guru pengampu Melati berhasil diperbarui.')
                ->tap(fn (Browser $b) => $this->visualPause($b))
                ->assertSeeIn('@baris-kelas-'.$kelas->id, 'Ayu Rizki')
                ->assertDontSeeIn('@baris-kelas-'.$kelas->id, 'Mia Stacia');
        });

        $this->assertDatabaseHas('kelas', ['id' => $kelas->id, 'guru_id' => $this->guruAyu->id]);
    }

    public function test_ts_kls_03_05_validasi_gagal_jika_form_kelas_kosong(): void
    {
        $this->browse(function (Browser $browser) {
            $this->masukDanBukaKelolaKelas($browser)
                ->click('@tombol-tambah-kelas')
                ->waitFor('#drawer-kelas[open]')
                ->tap(fn (Browser $b) => $this->visualPause($b))
                ->within('@drawer-kelas', function (Browser $drawer) {
                    $drawer->clear('tahun_ajaran')
                        ->tap(fn (Browser $b) => $this->visualPause($b))
                        ->click('@simpan-kelas');
                })
                // Drawer terbuka lagi otomatis berisi galat validasi.
                ->waitFor('#drawer-kelas[open]')
                ->tap(fn (Browser $b) => $this->visualPause($b, 1000))
                ->within('@drawer-kelas', function (Browser $drawer) {
                    $drawer->assertSeeIn('@galat-nama_kelas', 'Nama kelas wajib diisi.')
                        ->assertSeeIn('@galat-kelompok', 'Kelompok wajib dipilih.')
                        ->assertSeeIn('@galat-tahun_ajaran', 'Tahun ajaran wajib diisi.')
                        ->assertSeeIn('@galat-guru_id', 'Pilih satu guru pengampu.');
                })
                ->assertMissing('@pesan-status');
        });

        $this->assertDatabaseCount('kelas', 0);
    }

    public function test_ts_kls_03_06_admin_mengubah_data_kelas_dan_tahun_ajaran(): void
    {
        $kelas = $this->buatKelas();

        $this->browse(function (Browser $browser) use ($kelas) {
            $this->masukDanBukaKelolaKelas($browser)
                ->select('@filter-tahun', '2026/2027')
                ->waitFor('@baris-kelas-'.$kelas->id)
                ->tap(fn (Browser $b) => $this->visualPause($b))
                ->click('@ubah-kelas-'.$kelas->id)
                ->waitFor('#drawer-kelas[open]')
                ->tap(fn (Browser $b) => $this->visualPause($b))
                ->within('@drawer-kelas', function (Browser $drawer) {
                    $drawer->assertSee('Ubah Kelas')
                        ->assertInputValue('nama_kelas', 'Melati')
                        // Guru pengampu diganti lewat modal terpisah, bukan dari form ubah.
                        ->assertMissing('@pilih-guru')
                        ->clear('nama_kelas')
                        ->type('nama_kelas', 'Melati Ceria')
                        ->tap(fn (Browser $b) => $this->visualPause($b))
                        ->select('kelompok', 'TK B')
                        ->tap(fn (Browser $b) => $this->visualPause($b))
                        ->click('@simpan-kelas');
                })
                ->waitForTextIn('@pesan-status', 'Kelas Melati Ceria berhasil diperbarui.')
                ->tap(fn (Browser $b) => $this->visualPause($b))
                ->assertSeeIn('@baris-kelas-'.$kelas->id, 'Melati Ceria')
                ->assertSeeIn('@baris-kelas-'.$kelas->id, 'TK B')
                ->assertSeeIn('@baris-kelas-'.$kelas->id, 'Mia Stacia');
        });

        $this->assertDatabaseHas('kelas', [
            'id' => $kelas->id,
            'nama_kelas' => 'Melati Ceria',
            'kelompok' => 'TK B',
            'tahun_ajaran' => '2026/2027',
            'guru_id' => $this->guruMia->id,
        ]);
    }
}
