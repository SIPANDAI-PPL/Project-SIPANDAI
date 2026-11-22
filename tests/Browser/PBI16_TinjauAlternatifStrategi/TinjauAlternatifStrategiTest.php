<?php

namespace Tests\Browser\PBI16_TinjauAlternatifStrategi;

use App\Enums\KategoriRespons;
use App\Models\AlternatifStrategi;
use App\Models\Aktivitas;
use App\Models\Anak;
use App\Models\AspekPerkembangan;
use App\Models\AturanStrategi;
use App\Models\Kelas;
use App\Models\StrategiPembelajaran;
use App\Models\User;
use Laravel\Dusk\Browser;
use Tests\Browser\Concerns\DataUnik;
use Tests\DuskTestCase;

/**
 * PBI-16 Tinjau, pilih, atau abaikan alternatif strategi (FR-13, NFR-06).
 *
 * Alur diuji dari awal lewat antarmuka: guru masuk dari beranda, mencatat observasi lewat formulir,
 * membuka Mode Fokus dari menu Alternatif Strategi, lalu memilih atau mengabaikan alternatif.
 *
 * Tanpa DatabaseMigrations/DatabaseTruncation dan tanpa seeder: data awal (guru, kelas, anak,
 * data master, aturan) dibuat lewat factory dengan kode unik, jadi setiap run menambah data baru
 * di database sipandai_dusk dan data run sebelumnya tetap tersimpan.
 */
class TinjauAlternatifStrategiTest extends DuskTestCase
{
    use DataUnik;

    private const SANDI_MIA = 'GuruMia#2026';

    private const SANDI_AYU = 'GuruAyu#2026';

    private User $mia;

    private User $ayu;

    private Kelas $kelas;

    private Anak $anak;

    private Aktivitas $aktivitas;

    private AspekPerkembangan $aspek;

    /** @var array<string, StrategiPembelajaran> nama dasar strategi => model */
    private array $s = [];

    protected function setUp(): void
    {
        parent::setUp();

        $kode = $this->kodeUnik();

        // Pre Condition: dua guru, masing-masing punya kelas sendiri.
        $this->mia = User::factory()->create([
            'nama' => "Mia Stacia {$kode}",
            'nama_pengguna' => "mia{$kode}",
            'email' => "mia.{$kode}@tkceria.sch.id",
            'kata_sandi' => self::SANDI_MIA,
        ]);
        $this->ayu = User::factory()->create([
            'nama' => "Ayu Rizki {$kode}",
            'nama_pengguna' => "ayu{$kode}",
            'email' => "ayu.{$kode}@tkceria.sch.id",
            'kata_sandi' => self::SANDI_AYU,
        ]);

        $this->kelas = Kelas::factory()->create([
            'guru_id' => $this->mia->id,
            'kelompok' => 'TK A',
            'nama_kelas' => "Melati {$kode}",
        ]);
        Kelas::factory()->create([
            'guru_id' => $this->ayu->id,
            'kelompok' => 'TK B',
            'nama_kelas' => "Cendrawasih {$kode}",
        ]);

        // Data dummy, bukan data anak asli.
        $this->anak = Anak::factory()->create([
            'kelas_id' => $this->kelas->id,
            'nama' => "Raka Pratama {$kode}",
            'nama_panggilan' => 'Raka',
        ]);

        $this->aktivitas = Aktivitas::factory()->create(['nama' => "Bermain peran {$kode}"]);
        $this->aspek = AspekPerkembangan::factory()->create(['nama' => "Bahasa {$kode}"]);

        foreach (['Bercerita Lisan', 'Media Visual', 'Kerja Kelompok', 'Permainan Gerak'] as $nama) {
            $this->s[$nama] = StrategiPembelajaran::factory()->create(['nama' => "{$nama} {$kode}", 'aktif' => true]);
        }

        // Aturan: Kurang tertarik 3x pada Bercerita Lisan, sarankan Media Visual.
        AturanStrategi::factory()->create([
            'strategi_asal_id' => $this->s['Bercerita Lisan']->id,
            'respons_pemicu' => KategoriRespons::KurangTertarik,
            'minimal_kejadian' => 3,
            'strategi_alternatif_id' => $this->s['Media Visual']->id,
            'aspek_id' => null,
            'aktif' => true,
        ]);
    }

    // ---------------------------------------------------------------- Positif

    // TC-16-01 | Positif | Guru memilih saran dari aturan lalu menyimpan keputusan
    public function test_tc_16_01_guru_memilih_saran_dari_aturan_lalu_menyimpan_keputusan(): void
    {
        $this->browse(function (Browser $browser) {
            $this->login($browser, $this->mia, self::SANDI_MIA);
            $this->catatObservasi($browser, KategoriRespons::KurangTertarik, 3);
            $this->bukaModeFokus($browser);

            $id = $this->idSaran('Media Visual');
            $browser->assertSeeIn('@badge-keputusan', 'Menunggu keputusan guru')
                ->assertAttribute('@simpan-keputusan', 'disabled', 'true');

            // Tandai kartu: belum tersimpan sampai Simpan Keputusan ditekan.
            $this->klikKartu($browser, 'tandai-'.$id)
                ->assertVisible('@kartu-saran-'.$id.' [data-penanda-ditandai]')
                ->assertSeeIn('@bilah-keputusan', 'Alternatif '.$this->s['Media Visual']->nama.' dipilih. Belum tersimpan, tekan Simpan Keputusan.');
            $this->assertNull($browser->attribute('@simpan-keputusan', 'disabled'));
            $this->assertSame('disarankan', AlternatifStrategi::find($id)->status);

            $browser->click('@simpan-keputusan')->waitFor('@pesan-status')
                ->assertSeeIn('@pesan-status', 'Strategi '.$this->s['Media Visual']->nama.' dipilih.')
                ->assertSeeIn('@kartu-saran-'.$id.' [dusk="badge-status"]', 'Status: Dipilih')
                ->assertSeeIn('@badge-keputusan', 'Alternatif dipilih')
                ->assertSeeIn('@bilah-keputusan', 'Sudah dipilih dan tersimpan: '.$this->s['Media Visual']->nama.'.')
                ->assertSeeIn('@kartu-saran-'.$id, 'Buat Tindak Lanjut');
        });

        $this->assertSame('dipilih', AlternatifStrategi::where('anak_id', $this->anak->id)
            ->where('strategi_id', $this->s['Media Visual']->id)->value('status'));
    }

    // TC-16-02 | Positif | Guru mengabaikan saran dari aturan
    public function test_tc_16_02_guru_mengabaikan_saran_dari_aturan(): void
    {
        $this->browse(function (Browser $browser) {
            $this->login($browser, $this->mia, self::SANDI_MIA);
            $this->catatObservasi($browser, KategoriRespons::KurangTertarik, 3);
            $this->bukaModeFokus($browser);

            $id = $this->idSaran('Media Visual');
            $this->klikKartu($browser, 'abaikan-'.$id)->waitFor('@dialog-abaikan-alternatif')
                ->assertSeeIn('@dialog-abaikan-alternatif', 'Abaikan alternatif?')
                ->assertSeeIn('@dialog-abaikan-alternatif', $this->s['Media Visual']->nama);

            $browser->click('@konfirmasi-abaikan')->waitFor('@pesan-status')
                ->assertSeeIn('@pesan-status', 'Strategi '.$this->s['Media Visual']->nama.' diabaikan.')
                ->assertSeeIn('@kartu-saran-'.$id.' [dusk="badge-status"]', 'Status: Diabaikan')
                ->assertMissing('@tandai-'.$id)
                ->assertMissing('@abaikan-'.$id);
        });

        $this->assertSame('diabaikan', AlternatifStrategi::where('anak_id', $this->anak->id)
            ->where('strategi_id', $this->s['Media Visual']->id)->value('status'));
    }

    // TC-16-03 | Positif | Guru memilih alternatif yang ditambahkan sendiri
    public function test_tc_16_03_guru_memilih_alternatif_yang_ditambahkan_sendiri(): void
    {
        $this->browse(function (Browser $browser) {
            $this->login($browser, $this->mia, self::SANDI_MIA);
            $this->catatObservasi($browser, KategoriRespons::KurangTertarik, 1);
            $this->bukaModeFokus($browser);
            $this->tambahAlternatif($browser, 'Kerja Kelompok', 'Anak lebih semangat bersama teman.');

            $id = $this->idSaran('Kerja Kelompok');
            $this->klikKartu($browser, 'tandai-'.$id);
            $browser->click('@simpan-keputusan')->waitFor('@pesan-status')
                ->assertSeeIn('@pesan-status', 'Strategi '.$this->s['Kerja Kelompok']->nama.' dipilih.')
                ->assertSeeIn('@kartu-saran-'.$id, 'Ditambahkan guru')
                ->assertSeeIn('@kartu-saran-'.$id.' [dusk="badge-status"]', 'Status: Dipilih')
                ->assertSeeIn('@badge-keputusan', 'Alternatif dipilih');
        });

        $this->assertDatabaseHas('alternatif_strategi', [
            'anak_id' => $this->anak->id,
            'strategi_id' => $this->s['Kerja Kelompok']->id,
            'aturan_id' => null,
            'status' => 'dipilih',
        ]);
    }

    // TC-16-04 | Positif | Guru memilih lebih dari satu alternatif
    public function test_tc_16_04_guru_memilih_lebih_dari_satu_alternatif(): void
    {
        $this->browse(function (Browser $browser) {
            $this->login($browser, $this->mia, self::SANDI_MIA);
            $this->catatObservasi($browser, KategoriRespons::KurangTertarik, 3);
            $this->bukaModeFokus($browser);
            $this->tambahAlternatif($browser, 'Kerja Kelompok', 'Dicoba bersamaan dengan media visual.');

            $idAturan = $this->idSaran('Media Visual');
            $idManual = $this->idSaran('Kerja Kelompok');

            $this->klikKartu($browser, 'tandai-'.$idAturan);
            $browser->click('@simpan-keputusan')->waitFor('@pesan-status')
                ->assertSeeIn('@pesan-status', 'Strategi '.$this->s['Media Visual']->nama.' dipilih.');

            // Saran lain tetap bisa dipilih: proposal tidak membatasi satu pilihan per anak.
            $this->klikKartu($browser, 'tandai-'.$idManual);
            $browser->click('@simpan-keputusan')->waitFor('@pesan-status')
                ->assertSeeIn('@pesan-status', 'Strategi '.$this->s['Kerja Kelompok']->nama.' dipilih.')
                ->assertSeeIn('@bilah-keputusan', $this->s['Media Visual']->nama)
                ->assertSeeIn('@bilah-keputusan', $this->s['Kerja Kelompok']->nama)
                ->assertSeeIn('@badge-keputusan', 'Alternatif dipilih');
        });

        $this->assertSame(2, AlternatifStrategi::where('anak_id', $this->anak->id)->where('status', 'dipilih')->count());
    }

    // ---------------------------------------------------------------- Negatif

    // TC-16-05 | Negatif | Simpan keputusan tidak bisa sebelum ada kartu yang ditandai
    public function test_tc_16_05_simpan_keputusan_tidak_bisa_sebelum_ada_kartu_yang_ditandai(): void
    {
        $this->browse(function (Browser $browser) {
            $this->login($browser, $this->mia, self::SANDI_MIA);
            $this->catatObservasi($browser, KategoriRespons::KurangTertarik, 3);
            $this->bukaModeFokus($browser);

            $id = $this->idSaran('Media Visual');
            $browser
                ->assertSeeIn('@bilah-keputusan', 'Belum ada alternatif yang ditandai.')
                ->assertAttribute('@simpan-keputusan', 'disabled', 'true');

            // Tandai lalu tekan lagi untuk membatalkan tanda: tombol Simpan kembali nonaktif.
            $this->klikKartu($browser, 'tandai-'.$id)
                ->waitFor('@kartu-saran-'.$id.' [data-penanda-ditandai]')
                ->assertAttribute('@tandai-'.$id, 'aria-pressed', 'true')
                ->pause(500);
            $this->klikKartu($browser, 'tandai-'.$id)
                ->waitUntilMissing('@kartu-saran-'.$id.' [data-penanda-ditandai]')
                ->assertSeeIn('@bilah-keputusan', 'Belum ada alternatif yang ditandai.')
                ->assertAttribute('@simpan-keputusan', 'disabled', 'true');
        });

        $this->assertSame('disarankan', AlternatifStrategi::where('anak_id', $this->anak->id)
            ->where('strategi_id', $this->s['Media Visual']->id)->value('status'));
    }

    // TC-16-06 | Negatif | Batal mengabaikan tidak mengubah status
    public function test_tc_16_06_batal_mengabaikan_tidak_mengubah_status(): void
    {
        $this->browse(function (Browser $browser) {
            $this->login($browser, $this->mia, self::SANDI_MIA);
            $this->catatObservasi($browser, KategoriRespons::KurangTertarik, 3);
            $this->bukaModeFokus($browser);

            $id = $this->idSaran('Media Visual');
            $this->klikKartu($browser, 'abaikan-'.$id)->waitFor('@dialog-abaikan-alternatif');
            $browser->within('@dialog-abaikan-alternatif', fn (Browser $dialog) => $dialog->press('Batal'));
            $browser->waitUntilMissing('@dialog-abaikan-alternatif')
                ->assertSeeIn('@kartu-saran-'.$id.' [dusk="badge-status"]', 'Status: Disarankan')
                ->assertVisible('@tandai-'.$id)
                ->assertVisible('@abaikan-'.$id)
                ->assertMissing('@pesan-status');
        });

        $this->assertSame('disarankan', AlternatifStrategi::where('anak_id', $this->anak->id)
            ->where('strategi_id', $this->s['Media Visual']->id)->value('status'));
    }

    // TC-16-07 | Negatif | Saran yang sudah diabaikan tidak bisa dipilih lagi
    public function test_tc_16_07_saran_yang_sudah_diabaikan_tidak_bisa_dipilih_lagi(): void
    {
        $this->browse(function (Browser $browser) {
            $this->login($browser, $this->mia, self::SANDI_MIA);
            $this->catatObservasi($browser, KategoriRespons::KurangTertarik, 3);
            $this->bukaModeFokus($browser);

            $id = $this->idSaran('Media Visual');
            $this->klikKartu($browser, 'abaikan-'.$id)->waitFor('@dialog-abaikan-alternatif');
            $browser->click('@konfirmasi-abaikan')->waitFor('@pesan-status');

            // Buka ulang Mode Fokus: kartu yang diabaikan tetap tanpa tombol Pilih dan Abaikan.
            $this->bukaModeFokus($browser)
                ->assertSeeIn('@kartu-saran-'.$id.' [dusk="badge-status"]', 'Status: Diabaikan')
                ->assertMissing('@tandai-'.$id)
                ->assertMissing('@abaikan-'.$id)
                ->assertMissing('@badge-keputusan');
        });

        $this->assertSame('diabaikan', AlternatifStrategi::where('anak_id', $this->anak->id)
            ->where('strategi_id', $this->s['Media Visual']->id)->value('status'));
    }

    // TC-16-08 | Negatif | Saran yang sudah dipilih tidak bisa diabaikan
    public function test_tc_16_08_saran_yang_sudah_dipilih_tidak_bisa_diabaikan(): void
    {
        $this->browse(function (Browser $browser) {
            $this->login($browser, $this->mia, self::SANDI_MIA);
            $this->catatObservasi($browser, KategoriRespons::KurangTertarik, 3);
            $this->bukaModeFokus($browser);

            $id = $this->idSaran('Media Visual');
            $this->klikKartu($browser, 'tandai-'.$id);
            $browser->click('@simpan-keputusan')->waitFor('@pesan-status');

            // Kartu yang dipilih tidak punya tombol Pilih atau Abaikan lagi.
            $this->bukaModeFokus($browser)
                ->assertSeeIn('@kartu-saran-'.$id.' [dusk="badge-status"]', 'Status: Dipilih')
                ->assertMissing('@tandai-'.$id)
                ->assertMissing('@abaikan-'.$id);
        });

        $this->assertSame('dipilih', AlternatifStrategi::where('anak_id', $this->anak->id)
            ->where('strategi_id', $this->s['Media Visual']->id)->value('status'));
    }

    // ---------------------------------------------------------------- Error

    // TC-16-09 | Error | Guru kelas lain tidak bisa membuka Mode Fokus anak kelas lain
    public function test_tc_16_09_guru_kelas_lain_tidak_bisa_membuka_mode_fokus_anak_kelas_lain(): void
    {
        $this->browse(function (Browser $browser) {
            // Mia mencatat 3 observasi, jadi anak di kelas Mia punya saran dari aturan.
            $this->login($browser, $this->mia, self::SANDI_MIA);
            $this->catatObservasi($browser, KategoriRespons::KurangTertarik, 3);
            $this->bukaModeFokus($browser);

            // Ayu (kelas lain) membuka alamat Mode Fokus anak itu secara langsung.
            $this->login($browser, $this->ayu, self::SANDI_AYU);
            $browser->visit('/guru/alternatif-strategi/anak/'.$this->anak->id)
                ->assertSee('403')
                ->assertMissing('@tombol-tambah-alternatif')
                ->assertDontSee($this->anak->nama);
        });

        // Saran tetap berstatus disarankan karena Ayu tidak bisa mengambil keputusan.
        $this->assertSame('disarankan', AlternatifStrategi::where('anak_id', $this->anak->id)
            ->where('strategi_id', $this->s['Media Visual']->id)->value('status'));
    }

    // TC-16-10 | Error | Tamu diarahkan ke halaman login
    public function test_tc_16_10_tamu_diarahkan_ke_halaman_login(): void
    {
        $this->browse(function (Browser $browser) {
            $browser->logout()
                ->visit('/guru/alternatif-strategi/anak/'.$this->anak->id)
                ->waitForLocation('/login')
                ->assertPathIs('/login');
        });

        $this->assertDatabaseMissing('alternatif_strategi', ['anak_id' => $this->anak->id]);
    }

    // ---------------------------------------------------------------- Bantuan

    /**
     * Masuk seperti guru sungguhan: buka beranda, klik tombol "Masuk", lalu isi formulir login.
     * Sesi browser dipakai ulang antar-test, jadi keluar dulu sebelum login.
     */
    private function login(Browser $browser, User $guru, string $kataSandi): Browser
    {
        return $browser->logout()
            ->visit('/')
            ->waitFor('@tombol-atas')
            ->click('@tombol-atas')
            ->waitForLocation('/login')
            ->type('login', $guru->nama_pengguna)
            ->type('kata_sandi', $kataSandi)
            ->press('Masuk')
            ->waitUntilMissing('input[name="kata_sandi"]');
    }

    /**
     * Catat observasi anak pertama kelas Melati lewat formulir observasi (PBI-07), sebanyak $jumlah kali,
     * dengan aktivitas Bermain peran, aspek Bahasa, dan strategi Bercerita Lisan.
     */
    private function catatObservasi(Browser $browser, KategoriRespons $respons, int $jumlah): Browser
    {
        for ($i = 1; $i <= $jumlah; $i++) {
            $browser->visit('/guru/observasi/tambah')
                ->waitForText('Formulir Observasi Perkembangan')
                ->select('kelas_id', (string) $this->kelas->id)
                ->waitFor('#anak_id option[value="'.$this->anak->id.'"]')
                ->select('anak_id', (string) $this->anak->id)
                ->select('aktivitas_id', (string) $this->aktivitas->id)
                ->select('aspek_id', (string) $this->aspek->id)
                ->select('strategi_id', (string) $this->s['Bercerita Lisan']->id)
                ->click('label:has(input[name="kategori_respons"][value="'.$respons->value.'"])')
                ->type('catatan_pendukung', "Observasi ke-{$i}.");

            $browser
                ->press('Simpan Observasi')
                ->waitForText('Observasi berhasil disimpan');
        }

        return $browser;
    }

    /**
     * Buka Mode Fokus seperti guru: menu Alternatif Strategi, lalu klik nama anak di daftar.
     */
    private function bukaModeFokus(Browser $browser): Browser
    {
        $browser->clickLink('Alternatif Strategi')
            ->waitForLocation('/guru/alternatif-strategi')
            ->assertPresent('@baris-anak-'.$this->anak->id);

        return $browser->click('@tautan-anak-'.$this->anak->id)
            ->waitForLocation('/guru/alternatif-strategi/anak/'.$this->anak->id)
            ->waitFor('@tombol-tambah-alternatif');
    }

    /**
     * Tambah alternatif lewat dialog "+ Tambah alternatif" di Mode Fokus (fitur PBI-15).
     */
    private function tambahAlternatif(Browser $browser, string $strategi, string $catatan): Browser
    {
        $browser->click('@tombol-tambah-alternatif')->waitFor('@dialog-tambah-alternatif');
        $browser->within('@dialog-tambah-alternatif', function (Browser $dialog) use ($strategi, $catatan) {
            $dialog->select('strategi_id', (string) $this->s[$strategi]->id)
                ->type('catatan', $catatan);
            $dialog->click('@simpan-alternatif');
        });

        return $browser->waitFor('@pesan-status');
    }

    /**
     * Klik tombol di kartu setelah kartu digulir ke tengah layar. Bilah "Simpan Keputusan" menempel
     * di bawah layar, jadi tombol di kartu paling bawah bisa tertutup bilah itu kalau tidak digulir.
     */
    private function klikKartu(Browser $browser, string $dusk): Browser
    {
        $browser->script("document.querySelector('[dusk=\"{$dusk}\"]').scrollIntoView({block: 'center'});");

        return $browser->click('@'.$dusk);
    }

    /**
     * Id saran untuk anak ini dengan strategi tertentu. Saran dari aturan dibuat saat halaman dibuka.
     */
    private function idSaran(string $strategi): int
    {
        return (int) AlternatifStrategi::query()
            ->where('anak_id', $this->anak->id)
            ->where('strategi_id', $this->s[$strategi]->id)
            ->latest('id')
            ->value('id');
    }
}
