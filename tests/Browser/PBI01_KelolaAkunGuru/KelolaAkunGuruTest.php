<?php

namespace Tests\Browser\PBI01_KelolaAkunGuru;

use App\Models\Kelas;
use App\Models\User;
use Illuminate\Support\Facades\Hash;
use Laravel\Dusk\Browser;
use Tests\Browser\Concerns\DataUnik;
use Tests\DuskTestCase;

// PBI-01 Kelola Akun Guru dan Reset Kata Sandi
// TS-01-02 Menambah akun guru, TS-01-03 Menonaktifkan akun guru, TS-01-04 Reset kata sandi
class KelolaAkunGuruTest extends DuskTestCase
{
    // Tanpa DatabaseMigrations: data hasil test tetap tersimpan di database sipandai_dusk.
    use DataUnik;

    private User $admin;

    private User $ayu;

    protected function setUp(): void
    {
        parent::setUp();

        $kode = $this->kodeUnik();

        // Pre Condition: admin dan guru pengampu kelas dibuat lewat factory (langsung tersimpan di database).
        $this->admin = User::factory()->admin()->create([
            'nama' => "Admin SIPANDAI {$kode}",
            'nama_pengguna' => "admin{$kode}",
            'email' => "admin.{$kode}@tkceria.sch.id",
            'kata_sandi' => 'Admin#2026',
        ]);

        $this->ayu = User::factory()->create([
            'nama' => "Ayu Rizki {$kode}",
            'nama_pengguna' => "ayu{$kode}",
            'email' => "ayu.{$kode}@tkceria.sch.id",
            'kata_sandi' => 'SandiLama#2026',
        ]);
        Kelas::factory()->create([
            'guru_id' => $this->ayu->id,
            'kelompok' => 'TK B',
            'nama_kelas' => "Cendrawasih {$kode}",
        ]);
    }

    private function masukSebagaiAdmin(Browser $browser): Browser
    {
        // Sesi browser dipakai ulang antar-test, jadi keluar dulu sebelum login.
        return $browser->logout()
            ->visit('/login')
            ->type('login', $this->admin->nama_pengguna)
            ->type('kata_sandi', 'Admin#2026')
            ->press('Masuk')
            ->waitForLocation('/admin/dashboard')
            ->assertPathIs('/admin/dashboard')
            ->clickLink('Kelola Akun Guru')
            ->waitForLocation('/admin/guru')
            ->assertPathIs('/admin/guru');
    }

    /**
     * Daftar guru terus bertambah setiap run, jadi tabel disaring dengan kode unik test ini.
     */
    private function bukaDaftarGuruTestIni(Browser $browser): Browser
    {
        return $browser->visit('/admin/guru?q='.$this->kodeUnik())
            ->waitFor("@baris-guru-{$this->ayu->id}");
    }

    // TC-01-04 | Positif | Admin menambah guru dengan data valid
    public function test_tc_01_04_menambah_akun_guru_valid(): void
    {
        $kode = $this->kodeUnik();
        $jumlahGuruAwal = User::query()->where('peran', 'guru')->count();

        $this->browse(function (Browser $browser) use ($kode) {
            $this->masukSebagaiAdmin($browser)
                ->click('@tombol-tambah-guru')
                ->waitFor('@modal-tambah')
                ->within('@modal-tambah', function (Browser $modal) use ($kode) {
                    $modal->type('nama', "Rina Kartika {$kode}")
                        ->type('nama_pengguna', "rina{$kode}")
                        ->type('email', "rina.{$kode}@tkceria.sch.id")
                        ->click('@simpan-tambah');
                })
                // Expected Result - Tampilan
                ->waitFor('@modal-sandi-sementara')
                ->assertSeeIn('@modal-sandi-sementara', 'Akun guru berhasil dibuat')
                ->assertSeeIn('@modal-sandi-sementara', "Rina Kartika {$kode}")
                ->assertSeeIn('@modal-sandi-sementara', 'Tanpa Kelas');

            $sandi = trim($browser->text('@teks-sandi-sementara'));
            $this->assertSame(12, strlen($sandi));

            // Expected Result - Database
            $rina = User::query()->where('nama_pengguna', "rina{$kode}")->firstOrFail();
            $this->assertSame('guru', $rina->peran);
            $this->assertTrue($rina->aktif);
            $this->assertTrue($rina->wajib_ganti_sandi);
            $this->assertTrue(Hash::check($sandi, $rina->getAuthPassword()));

            $browser->click('@selesai-sandi')
                ->waitUntilMissing('@modal-sandi-sementara')
                ->visit('/admin/guru?q='.$kode)
                ->assertSee("Rina Kartika {$kode}");
        });

        $this->assertSame($jumlahGuruAwal + 1, User::query()->where('peran', 'guru')->count());
        $this->assertDatabaseHas('users', [
            'email' => "rina.{$kode}@tkceria.sch.id",
            'peran' => 'guru',
            'aktif' => 1,
            'wajib_ganti_sandi' => 1,
        ]);
    }

    // TC-01-08 | Positif | Admin mereset kata sandi guru
    public function test_tc_01_08_reset_kata_sandi_guru(): void
    {
        $kode = $this->kodeUnik();

        $this->browse(function (Browser $browser) use ($kode) {
            $this->masukSebagaiAdmin($browser);
            $this->bukaDaftarGuruTestIni($browser)
                ->click("@menu-aksi-{$this->ayu->id}")
                ->waitFor("@aksi-reset-{$this->ayu->id}")
                ->click("@aksi-reset-{$this->ayu->id}")
                ->waitFor('@modal-reset')
                ->assertSeeIn('@modal-reset', "Ayu Rizki {$kode}")
                ->assertSeeIn('@modal-reset', "TK B - Cendrawasih {$kode}")
                ->click('@konfirmasi-reset')
                // Expected Result - Tampilan
                ->waitFor('@modal-sandi-sementara')
                ->assertSeeIn('@modal-sandi-sementara', 'Kata sandi berhasil direset');

            $sandi = trim($browser->text('@teks-sandi-sementara'));
            $this->assertSame(12, strlen($sandi));

            // Expected Result - Database: kata sandi baru berlaku, kata sandi lama tidak.
            $this->ayu->refresh();
            $this->assertTrue($this->ayu->wajib_ganti_sandi);
            $this->assertTrue(Hash::check($sandi, $this->ayu->getAuthPassword()));
            $this->assertFalse(Hash::check('SandiLama#2026', $this->ayu->getAuthPassword()));
        });
    }

    // TC-01-14 | Negatif | Admin menonaktifkan guru yang masih mengampu kelas
    public function test_tc_01_14_tombol_nonaktifkan_disabled_untuk_guru_yang_mengampu_kelas(): void
    {
        $kode = $this->kodeUnik();

        $this->browse(function (Browser $browser) use ($kode) {
            $this->masukSebagaiAdmin($browser);
            $this->bukaDaftarGuruTestIni($browser)
                ->click("@menu-aksi-{$this->ayu->id}")
                ->waitFor("@aksi-nonaktifkan-{$this->ayu->id}")
                ->click("@aksi-nonaktifkan-{$this->ayu->id}")
                ->waitFor('@modal-nonaktifkan')
                // Expected Result - Tampilan
                ->assertSeeIn('@modal-nonaktifkan', "Nonaktifkan akun Ayu Rizki {$kode}?")
                ->assertVisible('@peringatan-kelas')
                ->assertSeeIn('@peringatan-kelas', '1 kelas')
                ->assertSeeIn('@peringatan-kelas', "TK B - Cendrawasih {$kode}")
                ->assertDisabled('@konfirmasi-nonaktifkan');
        });

        // Expected Result - Database: status guru tidak berubah.
        $this->assertDatabaseHas('users', ['id' => $this->ayu->id, 'aktif' => 1]);
    }

    // TC-01-20 | Error | Kata sandi sementara tidak tampil lagi setelah halaman dimuat ulang
    public function test_tc_01_20_kata_sandi_sementara_tidak_tampil_lagi_setelah_reload(): void
    {
        $kode = $this->kodeUnik();

        $this->browse(function (Browser $browser) use ($kode) {
            $this->masukSebagaiAdmin($browser);
            $this->bukaDaftarGuruTestIni($browser)
                ->click("@menu-aksi-{$this->ayu->id}")
                ->waitFor("@aksi-reset-{$this->ayu->id}")
                ->click("@aksi-reset-{$this->ayu->id}")
                ->waitFor('@modal-reset')
                ->click('@konfirmasi-reset')
                ->waitFor('@modal-sandi-sementara');

            $sandi = trim($browser->text('@teks-sandi-sementara'));
            $this->assertNotSame('', $sandi);

            // Expected Result - Tampilan dan sumber halaman tidak lagi memuat kata sandi.
            $browser->visit('/admin/guru?q='.$kode)
                ->assertPathIs('/admin/guru')
                ->assertSee("Ayu Rizki {$kode}")
                ->waitUntilMissing('@modal-sandi-sementara')
                ->assertDontSee($sandi)
                ->assertSourceMissing($sandi);
        });

        // Expected Result - Database: guru tetap wajib mengganti kata sandi.
        $this->assertDatabaseHas('users', ['id' => $this->ayu->id, 'wajib_ganti_sandi' => 1]);
    }
}
