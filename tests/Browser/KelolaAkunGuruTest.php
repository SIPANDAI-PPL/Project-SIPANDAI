<?php

namespace Tests\Browser;

use App\Models\Kelas;
use App\Models\User;
use Illuminate\Foundation\Testing\DatabaseMigrations;
use Illuminate\Support\Facades\Hash;
use Laravel\Dusk\Browser;
use Tests\DuskTestCase;

class KelolaAkunGuruTest extends DuskTestCase
{
    use DatabaseMigrations;

    private User $admin;

    private User $ayu;

    protected function setUp(): void
    {
        parent::setUp();

        $this->admin = User::factory()->admin()->create([
            'nama' => 'Admin SIPANDAI',
            'nama_pengguna' => 'admin',
            'kata_sandi' => 'Admin#2026',
        ]);

        $mia = User::factory()->create([
            'nama' => 'Mia Stacia',
            'nama_pengguna' => 'miastacia',
            'email' => 'mia.stacia@tkceria.sch.id',
        ]);
        Kelas::factory()->create(['guru_id' => $mia->id, 'kelompok' => 'TK A', 'nama_kelas' => 'Melati']);

        $this->ayu = User::factory()->create([
            'nama' => 'Ayu Rizki',
            'nama_pengguna' => 'ayurizki',
            'email' => 'ayu.rizki@tkceria.sch.id',
            'kata_sandi' => 'SandiLama#2026',
        ]);
        Kelas::factory()->create(['guru_id' => $this->ayu->id, 'kelompok' => 'TK B', 'nama_kelas' => 'Cendrawasih']);

        User::factory()->nonaktif()->create(['nama' => 'Dina Nugraha', 'nama_pengguna' => 'dinanugraha']);
        User::factory()->create(['nama' => 'Andi Pratama', 'nama_pengguna' => 'andipratama']);
    }

    private function masukSebagaiAdmin(Browser $browser): Browser
    {
        return $browser->visit('/login')
            ->type('login', 'admin')
            ->type('kata_sandi', 'Admin#2026')
            ->press('Masuk')
            ->waitForLocation('/admin/dashboard')
            ->assertPathIs('/admin/dashboard')
            ->clickLink('Kelola Akun Guru')
            ->waitForLocation('/admin/guru')
            ->assertPathIs('/admin/guru');
    }

    public function test_tc_01_04_menambah_akun_guru_valid(): void
    {
        $this->browse(function (Browser $browser) {
            $this->masukSebagaiAdmin($browser)
                ->click('@tombol-tambah-guru')
                ->waitFor('@modal-tambah')
                ->within('@modal-tambah', function (Browser $modal) {
                    $modal->type('nama', 'Rina Kartika')
                        ->type('nama_pengguna', 'rinakartika')
                        ->type('email', 'rina.kartika@tkceria.sch.id')
                        ->click('@simpan-tambah');
                })
                ->waitFor('@modal-sandi-sementara')
                ->assertSeeIn('@modal-sandi-sementara', 'Akun guru berhasil dibuat')
                ->assertSeeIn('@modal-sandi-sementara', 'Rina Kartika')
                ->assertSeeIn('@modal-sandi-sementara', 'Tanpa Kelas');

            $sandi = trim($browser->text('@teks-sandi-sementara'));
            $this->assertSame(12, strlen($sandi));

            $rina = User::where('nama_pengguna', 'rinakartika')->firstOrFail();
            $this->assertSame('guru', $rina->peran);
            $this->assertTrue($rina->aktif);
            $this->assertTrue($rina->wajib_ganti_sandi);
            $this->assertTrue(Hash::check($sandi, $rina->getAuthPassword()));

            $browser->click('@selesai-sandi')
                ->assertMissing('@modal-sandi-sementara')
                ->assertSee('Rina Kartika')
                ->assertSee('Semua (5)');
        });
    }

    public function test_tc_01_08_reset_kata_sandi_guru(): void
    {
        $this->browse(function (Browser $browser) {
            $this->masukSebagaiAdmin($browser)
                ->click("@menu-aksi-{$this->ayu->id}")
                ->waitFor("@aksi-reset-{$this->ayu->id}")
                ->click("@aksi-reset-{$this->ayu->id}")
                ->waitFor('@modal-reset')
                ->assertSeeIn('@modal-reset', 'Ayu Rizki')
                ->assertSeeIn('@modal-reset', 'TK B - Cendrawasih')
                ->click('@konfirmasi-reset')
                ->waitFor('@modal-sandi-sementara')
                ->assertSeeIn('@modal-sandi-sementara', 'Kata sandi berhasil direset');

            $sandi = trim($browser->text('@teks-sandi-sementara'));
            $this->assertSame(12, strlen($sandi));

            $this->ayu->refresh();
            $this->assertTrue($this->ayu->wajib_ganti_sandi);
            $this->assertTrue(Hash::check($sandi, $this->ayu->getAuthPassword()));
            $this->assertFalse(Hash::check('SandiLama#2026', $this->ayu->getAuthPassword()));
        });
    }

    public function test_tc_01_14_tombol_nonaktifkan_disabled_untuk_guru_yang_mengampu_kelas(): void
    {
        $this->browse(function (Browser $browser) {
            $this->masukSebagaiAdmin($browser)
                ->click("@menu-aksi-{$this->ayu->id}")
                ->waitFor("@aksi-nonaktifkan-{$this->ayu->id}")
                ->click("@aksi-nonaktifkan-{$this->ayu->id}")
                ->waitFor('@modal-nonaktifkan')
                ->assertSeeIn('@modal-nonaktifkan', 'Nonaktifkan akun Ayu Rizki?')
                ->assertVisible('@peringatan-kelas')
                ->assertSeeIn('@peringatan-kelas', '1 kelas')
                ->assertSeeIn('@peringatan-kelas', 'TK B - Cendrawasih')
                ->assertDisabled('@konfirmasi-nonaktifkan');
        });

        $this->assertTrue($this->ayu->fresh()->aktif);
    }

    public function test_tc_01_20_kata_sandi_sementara_tidak_tampil_lagi_setelah_reload(): void
    {
        $this->browse(function (Browser $browser) {
            $this->masukSebagaiAdmin($browser)
                ->click("@menu-aksi-{$this->ayu->id}")
                ->waitFor("@aksi-reset-{$this->ayu->id}")
                ->click("@aksi-reset-{$this->ayu->id}")
                ->waitFor('@modal-reset')
                ->click('@konfirmasi-reset')
                ->waitFor('@modal-sandi-sementara');

            $sandi = trim($browser->text('@teks-sandi-sementara'));
            $this->assertNotSame('', $sandi);

            $browser->refresh()
                ->assertPathIs('/admin/guru')
                ->assertSee('Ayu Rizki')
                ->assertMissing('@modal-sandi-sementara')
                ->assertDontSee($sandi)
                ->assertSourceMissing($sandi);
        });
    }
}
