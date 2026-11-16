<?php

namespace Tests\Browser;

use App\Models\Anak;
use App\Models\Kelas;
use App\Models\User;
use Illuminate\Foundation\Testing\DatabaseMigrations;
use Laravel\Dusk\Browser;
use Tests\DuskTestCase;

// Fitur: PBI-5 Kelola Data Dasar Anak
class KelolaDataAnakTest extends DuskTestCase
{
    use DatabaseMigrations;

    private function guruDenganKelas(): array
    {
        $guru = User::factory()->create(['nama' => 'Mia Stacia', 'nama_pengguna' => 'miastacia']);
        $kelas = Kelas::factory()->create([
            'guru_id' => $guru->id,
            'nama_kelas' => 'Melati',
            'kelompok' => 'TK B',
            'tahun_ajaran' => '2026/2027',
        ]);

        return [$guru, $kelas];
    }

    public function test_tc_05_05_guru_menambah_data_anak_lewat_modal(): void
    {
        [$guru, $kelas] = $this->guruDenganKelas();
        $lahir = now()->subMonths(42)->format('Y-m-d');

        $this->browse(function (Browser $browser) use ($guru, $lahir) {
            $browser->loginAs($guru)
                ->visit('/guru/anak')
                ->assertSee('Kelola Data Anak')
                ->click('@tombol-tambah-anak')
                ->waitFor('#modal-tambah:not(.hidden)')
                ->type('#tambah-nama', 'Rani Kartika')
                ->type('#tambah-panggilan', 'Rani')
                ->script("document.getElementById('tambah-lahir').value = '{$lahir}';");

            $browser->click('#modal-tambah button[type=submit]')
                ->waitFor('@pesan-status')
                ->assertSeeIn('@pesan-status', 'Data anak berhasil ditambahkan.')
                ->assertSeeIn('@daftar-anak', 'Rani')
                ->assertSeeIn('@daftar-anak', 'Belum diobservasi')
                ->assertSeeIn('@tab-aktif', 'Aktif (1)');
        });

        $this->assertDatabaseHas('anak', [
            'nama' => 'Rani Kartika',
            'nama_panggilan' => 'Rani',
            'kelas_id' => $kelas->id,
            'aktif' => 1,
        ]);
    }

    public function test_tc_05_08_guru_menonaktifkan_anak_lewat_modal_ubah(): void
    {
        [$guru, $kelas] = $this->guruDenganKelas();
        $anak = Anak::factory()->create([
            'kelas_id' => $kelas->id,
            'nama' => 'Bima Pratama',
            'nama_panggilan' => 'Bima',
            'tanggal_lahir' => now()->subMonths(45)->format('Y-m-d'),
        ]);

        $this->browse(function (Browser $browser) use ($guru, $anak) {
            $browser->loginAs($guru)
                ->visit('/guru/anak')
                ->assertSeeIn('@daftar-anak', 'Bima')
                ->clickLink('Bima')
                ->assertPathIs('/guru/anak/'.$anak->id)
                ->click('[data-modal-buka="modal-ubah"]')
                ->waitFor('#modal-ubah:not(.hidden)')
                ->click('#modal-ubah [data-modal-buka="modal-nonaktifkan"]')
                ->waitFor('#modal-nonaktifkan:not(.hidden)')
                ->assertSee('Nonaktifkan anak?')
                ->click('#modal-nonaktifkan button[type=submit]')
                ->waitFor('@pesan-status')
                ->assertSeeIn('@pesan-status', 'Anak berhasil dinonaktifkan.');
        });

        // Data tetap ada di basis data, hanya berstatus nonaktif.
        $this->assertDatabaseHas('anak', ['id' => $anak->id, 'aktif' => 0]);
    }

    public function test_tc_05_19_guru_tanpa_kelas_melihat_pesan_kosong_dan_tombol_tambah_nonaktif(): void
    {
        $guru = User::factory()->create(['nama' => 'Andi Pratama', 'nama_pengguna' => 'andipratama']);

        $this->browse(function (Browser $browser) use ($guru) {
            $browser->loginAs($guru)
                ->visit('/guru/anak')
                ->assertSeeIn('@pesan-tanpa-kelas', 'Anda belum mengampu kelas, hubungi admin.')
                ->assertDisabled('@tombol-tambah-anak');
        });
    }
}
