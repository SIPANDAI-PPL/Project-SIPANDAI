<?php

namespace Tests\Browser\PBI08_UbahHapusObservasi;

use App\Enums\KategoriRespons;
use App\Models\Aktivitas;
use App\Models\Anak;
use App\Models\AspekPerkembangan;
use App\Models\Kelas;
use App\Models\Observasi;
use App\Models\StrategiPembelajaran;
use App\Models\User;
use Illuminate\Foundation\Testing\DatabaseMigrations;
use Laravel\Dusk\Browser;
use Tests\DuskTestCase;

/**
 * PBI-8 Ubah dan Hapus Observasi beserta Riwayat Perubahan (browser).
 *
 * Test case mengikuti issue PBI-8. Yang diotomasi dengan Dusk:
 * - TC-08-01 Mengubah data observasi dengan input valid.
 * - TC-08-02 Menghapus data observasi.
 * Skenario lainnya diotomasi dengan PHPUnit Feature.
 *
 * Strategi pengujian:
 * - Setiap test dimulai dari halaman login sebagai guru pengampu (Mia Stacia).
 * - TC-08-01 (partisi input valid): data diubah lewat formulir, lalu dicek di halaman
 *   Detail dan di Riwayat dengan format "Field: sebelum -> sesudah". Field yang tidak
 *   diubah tidak ikut tercatat.
 * - TC-08-02: hapus selalu melewati modal konfirmasi (batal lalu konfirmasi), dan
 *   data beserta riwayat perubahannya hilang dari daftar.
 */
class KelolaObservasiTest extends DuskTestCase
{
    use DatabaseMigrations;

    /** Jeda (milidetik) antarlangkah agar proses mudah ditonton lewat noVNC. */
    private const JEDA = 1000;

    private Observasi $observasi;

    private Aktivitas $bernyanyi;

    protected function setUp(): void
    {
        parent::setUp();

        $mia = User::factory()->create([
            'nama' => 'Mia Stacia',
            'nama_pengguna' => 'miastacia',
            'kata_sandi' => 'password',
        ]);

        $melati = Kelas::factory()->create(['guru_id' => $mia->id, 'kelompok' => 'TK A', 'nama_kelas' => 'Melati']);

        $anak = Anak::factory()->create([
            'kelas_id' => $melati->id,
            'nama' => 'Dedek Tirta',
            'nama_panggilan' => 'Tirta',
        ]);

        $this->bernyanyi = Aktivitas::factory()->create(['nama' => 'Bernyanyi bersama']);

        $this->observasi = Observasi::factory()->create([
            'anak_id' => $anak->id,
            'guru_id' => $mia->id,
            'aktivitas_id' => Aktivitas::factory()->create(['nama' => 'Menyusun balok'])->id,
            'strategi_id' => StrategiPembelajaran::factory()->create(['nama' => 'Media Visual'])->id,
            'aspek_id' => AspekPerkembangan::factory()->create(['nama' => 'Kognitif'])->id,
            'tanggal' => now()->subDays(5)->toDateString(),
            'kategori_respons' => KategoriRespons::TerlibatAktif,
            'catatan_pendukung' => 'Catatan awal pengamatan.',
        ]);
    }

    /**
     * Login sebagai Mia lewat form lalu membuka Detail observasi dari Data Observasi.
     */
    private function masukLaluBukaDetail(Browser $browser): Browser
    {
        return $browser->visit('/login')
            ->type('login', 'miastacia')
            ->type('kata_sandi', 'password')
            ->pause(self::JEDA)
            ->press('Masuk')
            ->waitForLocation('/guru/dashboard')
            ->assertPathIs('/guru/dashboard')
            ->visit('/guru/observasi')
            ->assertSee('Data Observasi Perkembangan')
            ->assertSee('Dedek Tirta')
            ->pause(self::JEDA)
            ->clickLink('Detail')
            ->waitForText('Detail Observasi Perkembangan')
            ->assertPathIs($this->urlObservasi())
            ->pause(self::JEDA);
    }

    private function urlObservasi(string $akhiran = ''): string
    {
        return '/guru/observasi/'.$this->observasi->id.$akhiran;
    }

    /**
     * TC-08-01: Mengubah data observasi dengan input valid.
     * Alur: login -> Detail (data awal) -> Ubah (ganti data, simpan)
     * -> Detail (data baru) -> Riwayat (tercatat "Field: sebelum -> sesudah").
     */
    public function test_tc_08_01_mengubah_data_observasi_dengan_input_valid(): void
    {
        $this->browse(function (Browser $browser) {
            // 1. Detail menampilkan data awal dan belum ada riwayat.
            $this->masukLaluBukaDetail($browser)
                ->assertSee('Menyusun balok')
                ->assertSee('Terlibat aktif')
                ->assertSee('Catatan awal pengamatan.')
                ->assertSee('Lihat Riwayat (0)')
                ->assertSee('Belum ada riwayat perubahan untuk observasi ini.')

                // 2. Formulir Ubah terisi data awal.
                ->clickLink('Ubah')
                ->waitForText('Ubah Observasi')
                ->assertPathIs($this->urlObservasi('/ubah'))
                ->assertSelected('aktivitas_id', (string) $this->observasi->aktivitas_id)
                ->assertRadioSelected('kategori_respons', KategoriRespons::TerlibatAktif->value)
                ->assertValue('#catatan_pendukung', 'Catatan awal pengamatan.')
                ->pause(self::JEDA)

                // 3. Ganti aktivitas, respons anak, dan catatan, lalu simpan.
                ->select('aktivitas_id', (string) $this->bernyanyi->id)
                ->radio('kategori_respons', KategoriRespons::KurangTertarik->value)
                ->clear('catatan_pendukung')
                ->type('catatan_pendukung', 'Anak mudah beralih ke mainan lain.')
                ->pause(self::JEDA)
                ->press('Simpan Perubahan')

                // 4. Kembali ke Detail dengan data baru dan riwayat bertambah.
                ->waitForText('Observasi berhasil diperbarui')
                ->assertPathIs($this->urlObservasi())
                ->assertSee('Perubahan tersimpan dan tercatat pada riwayat perubahan observasi.')
                ->assertSee('Bernyanyi bersama')
                ->assertSee('Kurang tertarik')
                ->assertSee('Anak mudah beralih ke mainan lain.')
                ->assertSee('Lihat Riwayat (1)')
                ->pause(self::JEDA)

                // 5. Riwayat mencatat data sebelum dan sesudah untuk field yang berubah saja.
                ->clickLink('Lihat Riwayat (1)')
                ->waitForText('Riwayat Perubahan')
                ->assertPathIs($this->urlObservasi('/riwayat'))
                ->assertSee('Mia Stacia')
                // Label aksi ditampilkan kapital lewat CSS (DIUBAH), jadi dicek di sumber HTML.
                ->assertSourceHas('Diubah')
                ->assertSee('Aktivitas: Menyusun balok -> Bernyanyi bersama')
                ->assertSee('Respons anak: Terlibat aktif -> Kurang tertarik')
                ->assertSee('Catatan pendukung: Catatan awal pengamatan. -> Anak mudah beralih ke mainan lain.')
                ->assertDontSee('Tanggal observasi:')
                ->assertDontSee('Strategi pembelajaran:')
                ->assertDontSee('Aspek perkembangan:')
                ->pause(self::JEDA);
        });

        $this->assertDatabaseHas('observasi', [
            'id' => $this->observasi->id,
            'aktivitas_id' => $this->bernyanyi->id,
            'kategori_respons' => KategoriRespons::KurangTertarik->value,
            'catatan_pendukung' => 'Anak mudah beralih ke mainan lain.',
        ]);
        $this->assertDatabaseCount('riwayat_perubahan', 1);
    }

    /**
     * TC-08-02: Menghapus data observasi lewat modal konfirmasi.
     *
     * Alur: login -> Detail -> Hapus (batal) -> Hapus (konfirmasi) -> daftar
     * observasi tanpa data tersebut.
     */
    public function test_tc_08_02_menghapus_data_observasi(): void
    {
        $this->browse(function (Browser $browser) {
            $this->masukLaluBukaDetail($browser)
                // Batal: modal tertutup, tidak terjadi apa-apa.
                ->click('[data-buka-hapus]')
                ->waitFor('#dialog-hapus-observasi')
                ->assertSee('Hapus Observasi?')
                ->assertSee('Dedek Tirta')
                ->pause(self::JEDA)
                ->press('Batal')
                ->waitUntilMissing('#dialog-hapus-observasi')
                ->assertPathIs($this->urlObservasi())
                ->pause(self::JEDA)

                // Konfirmasi: centang persetujuan lalu tekan Hapus Observasi.
                ->click('[data-buka-hapus]')
                ->whenAvailable('#dialog-hapus-observasi', function (Browser $dialog) {
                    $dialog->check('#konfirmasi-hapus-observasi')
                        ->pause(self::JEDA)
                        ->press('Hapus Observasi');
                })
                ->waitForLocation('/guru/observasi')
                ->assertPathIs('/guru/observasi')
                ->waitForText('Observasi berhasil dihapus')
                ->assertSee('Observasi berhasil dihapus')
                ->assertDontSee('Dedek Tirta')
                ->pause(self::JEDA);
        });

        $this->assertDatabaseMissing('observasi', ['id' => $this->observasi->id]);
        $this->assertDatabaseMissing('riwayat_perubahan', [
            'observasi_id' => $this->observasi->id,
        ]);
    }
}
