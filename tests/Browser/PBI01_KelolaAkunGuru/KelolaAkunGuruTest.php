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

    // TC-01-21 | Negatif | Admin menambah guru dengan NIP lebih dari 18 karakter
    public function test_tc_01_21_nip_lebih_dari_18_karakter_menampilkan_pesan_error(): void
    {
        $kode = $this->kodeUnik();

        $this->browse(function (Browser $browser) use ($kode) {
            $this->masukSebagaiAdmin($browser)
                ->click('@tombol-tambah-guru')
                ->waitFor('@modal-tambah')
                ->within('@modal-tambah', function (Browser $modal) use ($kode) {
                    $modal->type('nama', "Sinta Dewi {$kode}")
                        ->type('nama_pengguna', "sinta{$kode}")
                        ->type('email', "sinta.{$kode}@tkceria.sch.id")
                        ->type('nip', '1992031520210220011')
                        ->click('@simpan-tambah');
                })
                // Expected Result - Tampilan: modal terbuka lagi dengan pesan error di bawah NIP.
                ->waitFor('@modal-tambah')
                ->waitForTextIn('@modal-tambah', 'NIP maksimal 18 karakter.')
                ->assertSeeIn('@modal-tambah', 'NIP maksimal 18 karakter.')
                ->assertInputValue('#tambah_nama', "Sinta Dewi {$kode}")
                ->assertMissing('@modal-sandi-sementara');
        });

        // Expected Result - Database: akun tidak tersimpan.
        $this->assertDatabaseMissing('users', ['nama_pengguna' => "sinta{$kode}"]);
    }

    /**
     * Membaca status validasi bawaan browser (HTML5) pada sebuah input.
     */
    private function validitas(Browser $browser, string $selector, string $jenis): bool
    {
        return (bool) $browser->script("return document.querySelector('{$selector}').validity.{$jenis};")[0];
    }

    // TC-01-10 | Negatif | Admin menyimpan akun guru dengan field wajib kosong
    public function test_tc_01_10_field_wajib_kosong_menampilkan_pesan_error(): void
    {
        $jumlahAwal = User::count();

        $this->browse(function (Browser $browser) {
            $this->masukSebagaiAdmin($browser)
                ->click('@tombol-tambah-guru')
                ->waitFor('@modal-tambah')
                ->click('@simpan-tambah')
                ->pause(500)
                // Expected Result - Tampilan: browser menahan form dan menandai field wajib yang kosong.
                ->assertVisible('@modal-tambah')
                ->assertMissing('@modal-sandi-sementara');

            foreach (['#tambah_nama', '#tambah_nama_pengguna', '#tambah_email'] as $field) {
                $this->assertTrue($this->validitas($browser, $field, 'valueMissing'), "{$field} harus ditandai wajib diisi.");
            }
            $this->assertNotSame('', $browser->script("return document.querySelector('#tambah_nama').validationMessage;")[0]);
        });

        // Expected Result - Database: tidak ada akun baru.
        $this->assertSame($jumlahAwal, User::count());
    }

    // TC-01-11 | Negatif | Admin mengisi email dengan format tidak valid
    public function test_tc_01_11_format_email_tidak_valid_menampilkan_pesan_error(): void
    {
        $kode = $this->kodeUnik();

        $this->browse(function (Browser $browser) use ($kode) {
            $this->masukSebagaiAdmin($browser)
                ->click('@tombol-tambah-guru')
                ->waitFor('@modal-tambah')
                ->within('@modal-tambah', function (Browser $modal) use ($kode) {
                    $modal->type('nama', "Rina Kartika {$kode}")
                        ->type('nama_pengguna', "rina{$kode}")
                        ->type('email', 'rina.kartika')
                        ->click('@simpan-tambah');
                })
                ->pause(500)
                // Expected Result - Tampilan: browser menolak format email dan modal tetap terbuka.
                ->assertVisible('@modal-tambah')
                ->assertMissing('@modal-sandi-sementara');

            $this->assertTrue($this->validitas($browser, '#tambah_email', 'typeMismatch'));
        });

        // Expected Result - Database: akun tidak tersimpan.
        $this->assertDatabaseMissing('users', ['nama_pengguna' => "rina{$kode}"]);
    }

    // TC-01-12 | Negatif | Admin menambah guru dengan email yang sudah terdaftar
    public function test_tc_01_12_email_duplikat_menampilkan_pesan_error(): void
    {
        $kode = $this->kodeUnik();

        $this->browse(function (Browser $browser) use ($kode) {
            $this->masukSebagaiAdmin($browser)
                ->click('@tombol-tambah-guru')
                ->waitFor('@modal-tambah')
                ->within('@modal-tambah', function (Browser $modal) use ($kode) {
                    $modal->type('nama', "Rina Kartika {$kode}")
                        ->type('nama_pengguna', "rina{$kode}")
                        ->type('email', $this->ayu->email)
                        ->click('@simpan-tambah');
                })
                // Expected Result - Tampilan: modal terbuka lagi dengan pesan error dari server.
                ->waitForTextIn('@modal-tambah', 'Email sudah terdaftar.')
                ->assertSeeIn('@modal-tambah', 'Email sudah terdaftar.')
                ->assertMissing('@modal-sandi-sementara');
        });

        // Expected Result - Database: akun baru tidak tersimpan.
        $this->assertDatabaseMissing('users', ['nama_pengguna' => "rina{$kode}"]);
    }

    // TC-01-13 | Negatif | Admin menambah guru dengan nama pengguna yang sudah terdaftar
    public function test_tc_01_13_nama_pengguna_duplikat_menampilkan_pesan_error(): void
    {
        $kode = $this->kodeUnik();

        $this->browse(function (Browser $browser) use ($kode) {
            $this->masukSebagaiAdmin($browser)
                ->click('@tombol-tambah-guru')
                ->waitFor('@modal-tambah')
                ->within('@modal-tambah', function (Browser $modal) use ($kode) {
                    $modal->type('nama', "Rina Kartika {$kode}")
                        ->type('nama_pengguna', $this->ayu->nama_pengguna)
                        ->type('email', "rina.{$kode}@tkceria.sch.id")
                        ->click('@simpan-tambah');
                })
                ->waitForTextIn('@modal-tambah', 'Nama pengguna sudah terdaftar.')
                ->assertSeeIn('@modal-tambah', 'Nama pengguna sudah terdaftar.')
                ->assertMissing('@modal-sandi-sementara');
        });

        $this->assertDatabaseMissing('users', ['email' => "rina.{$kode}@tkceria.sch.id"]);
    }

    // TC-01-16 | Error | Guru membuka halaman Kelola Akun Guru
    public function test_tc_01_16_guru_membuka_kelola_akun_guru_mendapat_403(): void
    {
        $jumlahAwal = User::count();

        $this->browse(function (Browser $browser) {
            $browser->logout()
                ->loginAs($this->ayu)
                ->visit('/admin/guru')
                ->assertSee('403')
                ->assertMissing('@tombol-tambah-guru');
        });

        $this->assertSame($jumlahAwal, User::count());
    }

    // TC-01-17 | Error | Tamu membuka halaman Kelola Akun Guru
    public function test_tc_01_17_tamu_diarahkan_ke_login(): void
    {
        $this->browse(function (Browser $browser) {
            $browser->logout()
                ->visit('/admin/guru')
                ->waitForLocation('/login')
                ->assertPathIs('/login')
                ->assertSee('Masuk ke SIPANDAI');
        });
    }

    // TC-01-18 | Negatif | Guru login dengan kata sandi lama setelah direset admin
    public function test_tc_01_18_login_dengan_kata_sandi_lama_setelah_reset_ditolak(): void
    {
        $this->browse(function (Browser $browser) {
            $this->masukSebagaiAdmin($browser);
            $this->bukaDaftarGuruTestIni($browser)
                ->click("@menu-aksi-{$this->ayu->id}")
                ->waitFor("@aksi-reset-{$this->ayu->id}")
                ->click("@aksi-reset-{$this->ayu->id}")
                ->waitFor('@modal-reset')
                ->click('@konfirmasi-reset')
                ->waitFor('@modal-sandi-sementara');

            $browser->logout()
                ->visit('/login')
                ->type('login', $this->ayu->nama_pengguna)
                ->type('kata_sandi', 'SandiLama#2026')
                ->press('Masuk')
                // Expected Result - Tampilan: login ditolak dengan pesan error.
                ->waitForText('Email/nama pengguna atau kata sandi salah.')
                ->assertPathIs('/login')
                ->assertSee('Email/nama pengguna atau kata sandi salah.');
        });

        // Expected Result - Database: kata sandi lama sudah tidak berlaku.
        $this->assertFalse(Hash::check('SandiLama#2026', $this->ayu->fresh()->getAuthPassword()));
    }

    // TC-01-19 | Error | Guru yang wajib ganti sandi membuka dashboard
    public function test_tc_01_19_guru_wajib_ganti_sandi_diarahkan_ke_ganti_kata_sandi(): void
    {
        $this->ayu->update(['wajib_ganti_sandi' => true]);

        $this->browse(function (Browser $browser) {
            $browser->logout()
                ->loginAs($this->ayu)
                ->visit('/guru/dashboard')
                ->waitForLocation('/ganti-kata-sandi')
                ->assertPathIs('/ganti-kata-sandi')
                ->assertSee('Anda masih memakai kata sandi sementara.')
                // Keluar lewat tombol supaya sesi tidak terbawa ke test berikutnya.
                ->press('Keluar')
                ->waitForLocation('/login');
        });

        $this->assertDatabaseHas('users', ['id' => $this->ayu->id, 'wajib_ganti_sandi' => 1]);
    }
}
