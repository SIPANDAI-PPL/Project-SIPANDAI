<?php

namespace Tests\Browser;

use App\Models\Kelas;
use App\Models\User;
use Illuminate\Foundation\Testing\DatabaseMigrations;
use Laravel\Dusk\Browser;
use Tests\DuskTestCase;

class KelasTest extends DuskTestCase
{
    use DatabaseMigrations;

    public function test_tc_03_03_admin_menambah_kelas_lewat_drawer(): void
    {
        $admin = User::factory()->admin()->create(['nama' => 'Admin SIPANDAI']);
        $mia = User::factory()->create(['nama' => 'Mia Stacia']);
        User::factory()->nonaktif()->create(['nama' => 'Guru Cuti']);

        $this->browse(function (Browser $browser) use ($admin, $mia) {
            $browser->loginAs($admin)
                ->visit('/classes?tahun_ajaran=2026/2027')
                ->assertSee('Kelola Kelas dan Penugasan Guru')
                ->assertSeeIn('@kelas-kosong', 'Belum ada kelas')
                ->click('@tombol-tambah-kelas')
                ->waitFor('@drawer-kelas')
                // Form kosong: galat validasi tampil di drawer yang terbuka lagi.
                ->within('@drawer-kelas', function (Browser $drawer) {
                    $drawer->clear('tahun_ajaran')->click('@simpan-kelas');
                })
                ->waitFor('#drawer-kelas[open]')
                ->within('@drawer-kelas', function (Browser $drawer) use ($mia) {
                    $drawer->assertSeeIn('@galat-nama_kelas', 'Nama kelas wajib diisi.')
                        ->assertSeeIn('@galat-kelompok', 'Kelompok wajib dipilih.')
                        ->assertSeeIn('@galat-tahun_ajaran', 'Tahun ajaran wajib diisi.')
                        ->assertSeeIn('@galat-guru_id', 'Pilih satu guru pengampu.')
                        // Guru nonaktif tidak muncul sebagai pilihan.
                        ->assertDontSeeIn('@pilih-guru', 'Guru Cuti')
                        ->type('nama_kelas', 'Kelas Ceria')
                        ->select('kelompok', 'TK A')
                        ->type('tahun_ajaran', '2026/2027')
                        ->select('guru_id', (string) $mia->id)
                        ->click('@simpan-kelas');
                })
                ->waitForText('Kelas Kelas Ceria berhasil disimpan.')
                ->assertSeeIn('@tabel-kelas', 'Kelas Ceria')
                ->assertSeeIn('@tabel-kelas', 'TK A')
                ->assertSeeIn('@tabel-kelas', 'Mia Stacia');
        });

        $this->assertDatabaseHas('kelas', [
            'nama_kelas' => 'Kelas Ceria',
            'kelompok' => 'TK A',
            'tahun_ajaran' => '2026/2027',
            'guru_id' => $mia->id,
        ]);
    }

    public function test_admin_mengubah_kelas_dan_mengganti_guru_pengampu(): void
    {
        $admin = User::factory()->admin()->create(['nama' => 'Admin SIPANDAI']);
        $mia = User::factory()->create(['nama' => 'Mia Stacia']);
        $ayu = User::factory()->create(['nama' => 'Ayu Rizki']);
        $kelas = Kelas::factory()->create([
            'guru_id' => $mia->id,
            'nama_kelas' => 'Melati',
            'kelompok' => 'TK A',
            'tahun_ajaran' => '2026/2027',
        ]);

        $this->browse(function (Browser $browser) use ($admin, $ayu, $kelas) {
            $browser->loginAs($admin)
                ->visit('/classes?tahun_ajaran=2026/2027')
                ->assertSeeIn('@baris-kelas-'.$kelas->id, 'Mia Stacia')
                ->click('@ubah-kelas-'.$kelas->id)
                ->waitFor('#drawer-kelas[open]')
                ->within('@drawer-kelas', function (Browser $drawer) {
                    $drawer->assertSee('Ubah Kelas')
                        ->assertInputValue('nama_kelas', 'Melati')
                        ->assertMissing('@pilih-guru')
                        ->clear('nama_kelas')
                        ->type('nama_kelas', 'Melati Ceria')
                        ->select('kelompok', 'TK B')
                        ->click('@simpan-kelas');
                })
                ->waitForText('Kelas Melati Ceria berhasil diperbarui.')
                ->assertSeeIn('@baris-kelas-'.$kelas->id, 'Melati Ceria')
                ->assertSeeIn('@baris-kelas-'.$kelas->id, 'TK B')
                ->click('@ganti-guru-'.$kelas->id)
                ->waitFor('#modal-guru[open]')
                ->within('@modal-guru', function (Browser $modal) use ($ayu) {
                    $modal->assertSee('Melati Ceria')
                        ->select('guru_id', (string) $ayu->id)
                        ->click('@simpan-guru');
                })
                ->waitForText('Guru pengampu Melati Ceria berhasil diperbarui.')
                ->assertSeeIn('@baris-kelas-'.$kelas->id, 'Ayu Rizki')
                ->assertDontSeeIn('@baris-kelas-'.$kelas->id, 'Mia Stacia');
        });

        $this->assertDatabaseHas('kelas', [
            'id' => $kelas->id,
            'nama_kelas' => 'Melati Ceria',
            'kelompok' => 'TK B',
            'guru_id' => $ayu->id,
        ]);
    }

    public function test_admin_memfilter_daftar_kelas_per_tahun_ajaran(): void
    {
        $admin = User::factory()->admin()->create();
        $guru = User::factory()->create();
        Kelas::factory()->create(['guru_id' => $guru->id, 'nama_kelas' => 'Melati', 'tahun_ajaran' => '2026/2027']);
        Kelas::factory()->create(['guru_id' => $guru->id, 'nama_kelas' => 'Anggrek', 'tahun_ajaran' => '2025/2026']);

        $this->browse(function (Browser $browser) use ($admin) {
            $browser->loginAs($admin)
                ->visit('/classes?tahun_ajaran=2026/2027')
                ->assertSeeIn('@tabel-kelas', 'Melati')
                ->assertDontSeeIn('@tabel-kelas', 'Anggrek')
                ->select('@filter-tahun', '2025/2026')
                ->waitForTextIn('@tabel-kelas', 'Anggrek')
                ->assertQueryStringHas('tahun_ajaran', '2025/2026')
                ->assertDontSeeIn('@tabel-kelas', 'Melati');
        });
    }

    public function test_tc_03_02_guru_tidak_dapat_membuka_halaman_kelola_kelas(): void
    {
        $guru = User::factory()->create();

        $this->browse(function (Browser $browser) use ($guru) {
            $browser->loginAs($guru)
                ->visit('/classes')
                ->assertSee('403');
        });
    }
}
