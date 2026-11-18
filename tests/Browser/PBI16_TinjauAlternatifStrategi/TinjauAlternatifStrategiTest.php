<?php

namespace Tests\Browser\PBI16_TinjauAlternatifStrategi;

use App\Enums\KategoriRespons;
use App\Models\AlternatifStrategi;
use App\Models\Aktivitas;
use App\Models\Anak;
use App\Models\AspekPerkembangan;
use App\Models\Kelas;
use App\Models\StrategiPembelajaran;
use Illuminate\Foundation\Testing\DatabaseTruncation;
use Laravel\Dusk\Browser;
use Tests\DuskTestCase;

/**
 * PBI-16 Tinjau, pilih, atau abaikan alternatif strategi (FR-13, NFR-06).
 *
 * Alur diuji dari awal lewat antarmuka: guru masuk dari beranda, mencatat observasi lewat formulir,
 * membuka Mode Fokus dari menu Alternatif Strategi, lalu memilih atau mengabaikan alternatif.
 *
 * Data awal memakai DatabaseSeeder project (akun miastacia, kelas TK A Melati, anak, data master,
 * aturan "Kurang tertarik 3x pada Bercerita Lisan, sarankan Media Visual"). Test berjalan di database
 * Dusk (DB_DATABASE di .env.dusk.local, contoh sipandai_dusk). DatabaseTruncation mengosongkan tabel di
 * AWAL setiap test dan tidak menghapus apa pun di akhir, jadi data test terakhir yang dijalankan tetap
 * tersimpan dan bisa dicek.
 *
 * Jeda antarlangkah untuk demo GUI:
 * docker compose exec -e DUSK_JEDA_MS=1000 laravel.test php artisan dusk --browse --filter=KeputusanAlternatifStrategiTest
 */
class TinjauAlternatifStrategiTest extends DuskTestCase
{
    use DatabaseTruncation;

    private const KATA_SANDI = 'password';

    private Kelas $kelas;

    private Anak $anak;

    private Aktivitas $aktivitas;

    private AspekPerkembangan $aspek;

    /** @var array<string, StrategiPembelajaran> nama strategi => model */
    private array $s = [];

    protected function setUp(): void
    {
        parent::setUp();

        $this->seed();

        $this->kelas = Kelas::query()->where('nama_kelas', 'Melati')->firstOrFail();
        // Nama anak dibuat Faker oleh seeder, jadi diambil dari database, bukan ditulis tetap.
        $this->anak = $this->kelas->anak()->where('aktif', true)->orderBy('nama')->firstOrFail();
        $this->aktivitas = Aktivitas::query()->where('nama', 'Bermain peran')->firstOrFail();
        $this->aspek = AspekPerkembangan::query()->where('nama', 'Bahasa')->firstOrFail();
        $this->s = StrategiPembelajaran::query()->get()->keyBy('nama')->all();
    }

    // ---------------------------------------------------------------- Positif

    // TC-16-01
    public function test_tc_16_01_guru_memilih_saran_dari_aturan_lalu_menyimpan_keputusan(): void
    {
        $this->browse(function (Browser $browser) {
            $this->login($browser, 'miastacia');
            $this->catatObservasi($browser, KategoriRespons::KurangTertarik, 3);
            $this->bukaModeFokus($browser);

            $id = $this->idSaran('Media Visual');
            $browser->assertSeeIn('@badge-keputusan', 'Menunggu keputusan guru')
                ->assertAttribute('@simpan-keputusan', 'disabled', 'true');

            // Tandai kartu: belum tersimpan sampai Simpan Keputusan ditekan.
            $this->jeda($browser->click('@tandai-'.$id))
                ->assertVisible('@kartu-saran-'.$id.' [data-penanda-ditandai]')
                ->assertSeeIn('@bilah-keputusan', 'Alternatif Media Visual dipilih. Belum tersimpan, tekan Simpan Keputusan.');
            $this->assertNull($browser->attribute('@simpan-keputusan', 'disabled'));
            $this->assertSame('disarankan', AlternatifStrategi::find($id)->status);

            $this->jeda($browser->click('@simpan-keputusan')->waitFor('@pesan-status'))
                ->assertSeeIn('@pesan-status', 'Strategi Media Visual dipilih.')
                ->assertSeeIn('@kartu-saran-'.$id.' [dusk="badge-status"]', 'Status: Dipilih')
                ->assertSeeIn('@badge-keputusan', 'Alternatif dipilih')
                ->assertSeeIn('@bilah-keputusan', 'Sudah dipilih dan tersimpan: Media Visual.')
                ->assertSeeIn('@kartu-saran-'.$id, 'Buat Tindak Lanjut');
        });

        $this->assertSame('dipilih', AlternatifStrategi::where('anak_id', $this->anak->id)
            ->where('strategi_id', $this->s['Media Visual']->id)->value('status'));
    }

    // TC-16-02
    public function test_tc_16_02_guru_mengabaikan_saran_dari_aturan(): void
    {
        $this->browse(function (Browser $browser) {
            $this->login($browser, 'miastacia');
            $this->catatObservasi($browser, KategoriRespons::KurangTertarik, 3);
            $this->bukaModeFokus($browser);

            $id = $this->idSaran('Media Visual');
            $this->jeda($browser->click('@abaikan-'.$id)->waitFor('@dialog-abaikan-alternatif'))
                ->assertSeeIn('@dialog-abaikan-alternatif', 'Abaikan alternatif?')
                ->assertSeeIn('@dialog-abaikan-alternatif', 'Media Visual');

            $this->jeda($browser->click('@konfirmasi-abaikan')->waitFor('@pesan-status'))
                ->assertSeeIn('@pesan-status', 'Strategi Media Visual diabaikan.')
                ->assertSeeIn('@kartu-saran-'.$id.' [dusk="badge-status"]', 'Status: Diabaikan')
                ->assertMissing('@tandai-'.$id)
                ->assertMissing('@abaikan-'.$id);
        });

        $this->assertSame('diabaikan', AlternatifStrategi::where('anak_id', $this->anak->id)
            ->where('strategi_id', $this->s['Media Visual']->id)->value('status'));
    }

    // TC-16-03
    public function test_tc_16_03_guru_memilih_alternatif_yang_ditambahkan_sendiri(): void
    {
        $this->browse(function (Browser $browser) {
            $this->login($browser, 'miastacia');
            $this->catatObservasi($browser, KategoriRespons::KurangTertarik, 1);
            $this->bukaModeFokus($browser);
            $this->tambahAlternatif($browser, 'Kerja Kelompok', 'Anak lebih semangat bersama teman.');

            $id = $this->idSaran('Kerja Kelompok');
            $this->jeda($browser->click('@tandai-'.$id));
            $this->jeda($browser->click('@simpan-keputusan')->waitFor('@pesan-status'))
                ->assertSeeIn('@pesan-status', 'Strategi Kerja Kelompok dipilih.')
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

    // TC-16-04
    public function test_tc_16_04_guru_memilih_lebih_dari_satu_alternatif(): void
    {
        $this->browse(function (Browser $browser) {
            $this->login($browser, 'miastacia');
            $this->catatObservasi($browser, KategoriRespons::KurangTertarik, 3);
            $this->bukaModeFokus($browser);
            $this->tambahAlternatif($browser, 'Kerja Kelompok', 'Dicoba bersamaan dengan media visual.');

            $idAturan = $this->idSaran('Media Visual');
            $idManual = $this->idSaran('Kerja Kelompok');

            $this->jeda($browser->click('@tandai-'.$idAturan));
            $this->jeda($browser->click('@simpan-keputusan')->waitFor('@pesan-status'))
                ->assertSeeIn('@pesan-status', 'Strategi Media Visual dipilih.');

            // Saran lain tetap bisa dipilih: proposal tidak membatasi satu pilihan per anak.
            $this->jeda($browser->click('@tandai-'.$idManual));
            $this->jeda($browser->click('@simpan-keputusan')->waitFor('@pesan-status'))
                ->assertSeeIn('@pesan-status', 'Strategi Kerja Kelompok dipilih.')
                ->assertSeeIn('@bilah-keputusan', 'Media Visual')
                ->assertSeeIn('@bilah-keputusan', 'Kerja Kelompok')
                ->assertSeeIn('@badge-keputusan', 'Alternatif dipilih');
        });

        $this->assertSame(2, AlternatifStrategi::where('anak_id', $this->anak->id)->where('status', 'dipilih')->count());
    }

    // ---------------------------------------------------------------- Negatif

    // TC-16-05
    public function test_tc_16_05_simpan_keputusan_tidak_bisa_sebelum_ada_kartu_yang_ditandai(): void
    {
        $this->browse(function (Browser $browser) {
            $this->login($browser, 'miastacia');
            $this->catatObservasi($browser, KategoriRespons::KurangTertarik, 3);
            $this->bukaModeFokus($browser);

            $id = $this->idSaran('Media Visual');
            $this->jeda($browser)
                ->assertSeeIn('@bilah-keputusan', 'Belum ada alternatif yang ditandai.')
                ->assertAttribute('@simpan-keputusan', 'disabled', 'true');

            // Tandai lalu tekan lagi untuk membatalkan tanda: tombol Simpan kembali nonaktif.
            $this->jeda($browser->click('@tandai-'.$id));
            $this->jeda($browser->click('@tandai-'.$id))
                ->assertMissing('@kartu-saran-'.$id.' [data-penanda-ditandai]')
                ->assertSeeIn('@bilah-keputusan', 'Belum ada alternatif yang ditandai.')
                ->assertAttribute('@simpan-keputusan', 'disabled', 'true');
        });

        $this->assertSame('disarankan', AlternatifStrategi::where('anak_id', $this->anak->id)
            ->where('strategi_id', $this->s['Media Visual']->id)->value('status'));
    }

    // TC-16-06
    public function test_tc_16_06_batal_mengabaikan_tidak_mengubah_status(): void
    {
        $this->browse(function (Browser $browser) {
            $this->login($browser, 'miastacia');
            $this->catatObservasi($browser, KategoriRespons::KurangTertarik, 3);
            $this->bukaModeFokus($browser);

            $id = $this->idSaran('Media Visual');
            $this->jeda($browser->click('@abaikan-'.$id)->waitFor('@dialog-abaikan-alternatif'));
            $browser->within('@dialog-abaikan-alternatif', fn (Browser $dialog) => $dialog->press('Batal'));
            $this->jeda($browser->waitUntilMissing('@dialog-abaikan-alternatif'))
                ->assertSeeIn('@kartu-saran-'.$id.' [dusk="badge-status"]', 'Status: Disarankan')
                ->assertVisible('@tandai-'.$id)
                ->assertVisible('@abaikan-'.$id)
                ->assertMissing('@pesan-status');
        });

        $this->assertSame('disarankan', AlternatifStrategi::where('anak_id', $this->anak->id)
            ->where('strategi_id', $this->s['Media Visual']->id)->value('status'));
    }

    // TC-16-07
    public function test_tc_16_07_saran_yang_sudah_diabaikan_tidak_bisa_dipilih_lagi(): void
    {
        $this->browse(function (Browser $browser) {
            $this->login($browser, 'miastacia');
            $this->catatObservasi($browser, KategoriRespons::KurangTertarik, 3);
            $this->bukaModeFokus($browser);

            $id = $this->idSaran('Media Visual');
            $browser->click('@abaikan-'.$id)->waitFor('@dialog-abaikan-alternatif');
            $this->jeda($browser->click('@konfirmasi-abaikan')->waitFor('@pesan-status'));

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

    // TC-16-08
    public function test_tc_16_08_saran_yang_sudah_dipilih_tidak_bisa_diabaikan(): void
    {
        $this->browse(function (Browser $browser) {
            $this->login($browser, 'miastacia');
            $this->catatObservasi($browser, KategoriRespons::KurangTertarik, 3);
            $this->bukaModeFokus($browser);

            $id = $this->idSaran('Media Visual');
            $browser->click('@tandai-'.$id);
            $this->jeda($browser->click('@simpan-keputusan')->waitFor('@pesan-status'));

            // Kartu yang dipilih tidak punya tombol Pilih atau Abaikan lagi.
            $this->bukaModeFokus($browser)
                ->assertSeeIn('@kartu-saran-'.$id.' [dusk="badge-status"]', 'Status: Dipilih')
                ->assertMissing('@tandai-'.$id)
                ->assertMissing('@abaikan-'.$id);
        });

        $this->assertSame('dipilih', AlternatifStrategi::where('anak_id', $this->anak->id)
            ->where('strategi_id', $this->s['Media Visual']->id)->value('status'));
    }


    // ---------------------------------------------------------------- Bantuan

    /**
     * Masuk seperti guru sungguhan: buka beranda, klik tombol "Masuk", lalu isi formulir login.
     */
    private function login(Browser $browser, string $namaPengguna): Browser
    {
        $this->jeda($browser->logout()
            ->visit('/')
            ->waitFor('@tombol-atas'))
            ->click('@tombol-atas')
            ->waitForLocation('/login');

        $this->jeda($browser)
            ->type('login', $namaPengguna)
            ->type('kata_sandi', self::KATA_SANDI);

        return $this->jeda($this->jeda($browser)->press('Masuk')->waitUntilMissing('input[name="kata_sandi"]'));
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

            $this->jeda($browser)
                ->press('Simpan Observasi')
                ->waitForText('Observasi berhasil disimpan');
            $this->jeda($browser);
        }

        return $browser;
    }

    /**
     * Buka Mode Fokus seperti guru: menu Alternatif Strategi, lalu klik nama anak di daftar.
     */
    private function bukaModeFokus(Browser $browser): Browser
    {
        $this->jeda($browser->clickLink('Alternatif Strategi')
            ->waitForLocation('/guru/alternatif-strategi'))
            ->assertPresent('@baris-anak-'.$this->anak->id);

        return $this->jeda($browser->click('@tautan-anak-'.$this->anak->id)
            ->waitForLocation('/guru/alternatif-strategi/anak/'.$this->anak->id)
            ->waitFor('@tombol-tambah-alternatif'));
    }

    /**
     * Tambah alternatif lewat dialog "+ Tambah alternatif" di Mode Fokus (fitur PBI-15).
     */
    private function tambahAlternatif(Browser $browser, string $strategi, string $catatan): Browser
    {
        $browser->click('@tombol-tambah-alternatif')->waitFor('@dialog-tambah-alternatif');
        $this->jeda($browser)->within('@dialog-tambah-alternatif', function (Browser $dialog) use ($strategi, $catatan) {
            $dialog->select('strategi_id', (string) $this->s[$strategi]->id)
                ->type('catatan', $catatan);
            $this->jeda($dialog)->click('@simpan-alternatif');
        });

        return $this->jeda($browser->waitFor('@pesan-status'));
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

    /**
     * Jeda opsional supaya langkah terlihat saat uji dengan GUI (DUSK_JEDA_MS, default 0).
     */
    private function jeda(Browser $browser): Browser
    {
        $ms = (int) env('DUSK_JEDA_MS', 0);

        return $ms > 0 ? $browser->pause($ms) : $browser;
    }
}