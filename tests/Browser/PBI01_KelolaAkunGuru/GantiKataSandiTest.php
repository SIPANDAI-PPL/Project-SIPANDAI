<?php

namespace Tests\Browser\PBI01_KelolaAkunGuru;

use App\Models\User;
use Illuminate\Support\Facades\Hash;
use Laravel\Dusk\Browser;
use Tests\Browser\Concerns\DataUnik;
use Tests\DuskTestCase;

// PBI-01 Kelola Akun Guru dan Reset Kata Sandi
// TS-01-04 Reset dan ganti kata sandi
class GantiKataSandiTest extends DuskTestCase
{
    // Tanpa DatabaseMigrations: data hasil test tetap tersimpan di database sipandai_dusk.
    use DataUnik;

    // TC-01-09 | Positif | Guru login dengan kata sandi sementara lalu menggantinya
    public function test_tc_01_09_guru_login_dengan_kata_sandi_sementara_lalu_menggantinya(): void
    {
        $kode = $this->kodeUnik();

        // Pre Condition: guru dengan kata sandi sementara dibuat lewat factory.
        $guru = User::factory()->wajibGantiSandi()->create([
            'nama' => "Ayu Rizki {$kode}",
            'nama_pengguna' => "ayu{$kode}",
            'email' => "ayu.{$kode}@tkceria.sch.id",
            'kata_sandi' => 'Sementara#2026',
        ]);

        $this->browse(function (Browser $browser) use ($guru) {
            $browser->logout()
                ->visit('/login')
                ->type('login', $guru->nama_pengguna)
                ->type('kata_sandi', 'Sementara#2026')
                ->press('Masuk')
                ->waitForLocation('/ganti-kata-sandi')
                ->assertPathIs('/ganti-kata-sandi')
                ->type('kata_sandi_baru', 'GuruAyu#2026')
                ->type('kata_sandi_baru_confirmation', 'GuruAyu#2026')
                ->press('Simpan kata sandi')
                // Expected Result - Tampilan
                ->waitForLocation('/guru/dashboard')
                ->assertPathIs('/guru/dashboard')
                ->assertSee('Kata sandi berhasil diganti.')
                ->assertSee("Selamat datang, {$guru->nama}")
                ->press('Keluar')
                ->waitForLocation('/login')
                ->assertPathIs('/login')
                ->type('login', $guru->nama_pengguna)
                ->type('kata_sandi', 'GuruAyu#2026')
                ->press('Masuk')
                ->waitForLocation('/guru/dashboard')
                ->assertPathIs('/guru/dashboard');
        });

        // Expected Result - Database: kata sandi baru tersimpan dan kewajiban ganti sandi selesai.
        $guru->refresh();
        $this->assertFalse($guru->wajib_ganti_sandi);
        $this->assertTrue(Hash::check('GuruAyu#2026', $guru->getAuthPassword()));
    }

    /**
     * Guru dengan kata sandi sementara masuk lewat form login sampai halaman ganti kata sandi.
     */
    private function masukDenganSandiSementara(Browser $browser, User $guru): Browser
    {
        // Test sebelumnya bisa meninggalkan sesi guru yang wajib ganti sandi. Sesi itu tidak bisa
        // keluar lewat logout() bawaan Dusk, jadi cookie sesi dihapus langsung.
        $browser->driver->manage()->deleteAllCookies();

        return $browser->visit('/login')
            ->type('login', $guru->nama_pengguna)
            ->type('kata_sandi', 'Sementara#2026')
            ->press('Masuk')
            ->waitForLocation('/ganti-kata-sandi');
    }

    private function buatGuruSandiSementara(): User
    {
        $kode = $this->kodeUnik();

        return User::factory()->wajibGantiSandi()->create([
            'nama' => "Rina Kartika {$kode}",
            'nama_pengguna' => "rina{$kode}",
            'email' => "rina.{$kode}@tkceria.sch.id",
            'kata_sandi' => 'Sementara#2026',
        ]);
    }

    // TC-01-09 | Negatif | Konfirmasi kata sandi baru tidak cocok
    public function test_tc_01_09_konfirmasi_kata_sandi_tidak_cocok_menampilkan_pesan_error(): void
    {
        $guru = $this->buatGuruSandiSementara();

        $this->browse(function (Browser $browser) use ($guru) {
            $this->masukDenganSandiSementara($browser, $guru)
                ->type('kata_sandi_baru', 'GuruRina#2026')
                ->type('kata_sandi_baru_confirmation', 'GuruRina#2027')
                ->press('Simpan kata sandi')
                ->waitForText('Konfirmasi kata sandi baru tidak cocok.')
                ->assertPathIs('/ganti-kata-sandi')
                ->assertSee('Konfirmasi kata sandi baru tidak cocok.');
        });

        // Expected Result - Database: kata sandi sementara masih berlaku.
        $guru->refresh();
        $this->assertTrue($guru->wajib_ganti_sandi);
        $this->assertTrue(Hash::check('Sementara#2026', $guru->getAuthPassword()));
    }

    // TC-01-09 | Negatif | Kata sandi baru sama dengan kata sandi sementara
    public function test_tc_01_09_kata_sandi_baru_sama_dengan_sementara_menampilkan_pesan_error(): void
    {
        $guru = $this->buatGuruSandiSementara();

        $this->browse(function (Browser $browser) use ($guru) {
            $this->masukDenganSandiSementara($browser, $guru)
                ->type('kata_sandi_baru', 'Sementara#2026')
                ->type('kata_sandi_baru_confirmation', 'Sementara#2026')
                ->press('Simpan kata sandi')
                ->waitForText('Kata sandi baru harus berbeda dari kata sandi sementara.')
                ->assertPathIs('/ganti-kata-sandi')
                ->assertSee('Kata sandi baru harus berbeda dari kata sandi sementara.');
        });

        $this->assertTrue($guru->fresh()->wajib_ganti_sandi);
    }
}
