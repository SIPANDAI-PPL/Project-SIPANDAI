<?php

namespace Tests\Browser;

use App\Enums\KategoriRespons;
use App\Models\AlternatifStrategi;
use App\Models\Aktivitas;
use App\Models\Anak;
use App\Models\AspekPerkembangan;
use App\Models\Kelas;
use App\Models\StrategiPembelajaran;
use Illuminate\Foundation\Testing\DatabaseMigrations;
use Laravel\Dusk\Browser;
use Tests\DuskTestCase;

/**
 * PBI-15 Penyusunan alternatif strategi berdasarkan aturan (FR-13, NFR-04, NFR-06).
 *
 * Alur diuji dari awal lewat antarmuka: guru login, guru mencatat observasi lewat formulir
 * observasi, lalu sistem menyusun saran dan guru meninjau atau menambah alternatif strategi.
 *
 * Data awal memakai DatabaseSeeder project (akun miastacia/ayurizki, kelas, anak, data master, aturan),
 * sama dengan data di web lokal. Test berjalan di database testing yang dikosongkan setiap test,
 * jadi database development tidak tersentuh. Observasi dibuat guru lewat formulir.
 *
 * Jeda antarlangkah untuk demo GUI:
 * docker compose exec -e DUSK_JEDA_MS=1000 laravel.test php artisan dusk --browse --filter=AlternatifStrategiTest
 */
class AlternatifStrategiTest extends DuskTestCase
{
    use DatabaseMigrations;

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

        // Data yang sama dengan web lokal: miastacia (kelas TK A Melati), ayurizki (TK B Cendrawasih),
        // anak per kelas, aspek, aktivitas, strategi, dan aturan "Kurang tertarik 3x pada Bercerita Lisan,
        // sarankan Media Visual". Seeder ini tidak membuat observasi.
        $this->seed();

        $this->kelas = Kelas::query()->where('nama_kelas', 'Melati')->firstOrFail();
        // Nama anak dibuat Faker oleh seeder, jadi diambil dari database, bukan ditulis tetap.
        $this->anak = $this->kelas->anak()->where('aktif', true)->orderBy('nama')->firstOrFail();
        $this->aktivitas = Aktivitas::query()->where('nama', 'Bermain peran')->firstOrFail();
        $this->aspek = AspekPerkembangan::query()->where('nama', 'Bahasa')->firstOrFail();
        $this->s = StrategiPembelajaran::query()->get()->keyBy('nama')->all();
    }

    // ---------------------------------------------------------------- Positif

    // TC-15-01
    public function test_tc_15_01_guru_login_mencatat_tiga_observasi_lalu_sistem_menyusun_saran(): void
    {
        $this->browse(function (Browser $browser) {
            $this->login($browser, 'miastacia');
            $this->catatObservasi($browser, KategoriRespons::KurangTertarik, 3);

            $this->jeda($browser->clickLink('Alternatif Strategi')->waitForLocation('/guru/alternatif-strategi'))
                ->assertSeeIn('@jumlah-saran', '1')
                ->within('@baris-anak-'.$this->anak->id, function (Browser $baris) {
                    $baris->assertSee($this->anak->nama)
                        ->assertSee('Kurang tertarik')
                        ->assertSee('Bercerita Lisan')
                        ->assertSee('Media Visual');
                });

            $this->jeda($browser->click('@tautan-anak-'.$this->anak->id)
                ->waitForLocation('/guru/alternatif-strategi/anak/'.$this->anak->id))
                ->assertPresent('[dusk^="kartu-saran-"]')
                ->assertSee('Dari aturan')
                ->assertSee('Respons Kurang tertarik tercatat 3 kali pada strategi Bercerita Lisan');
        });

        $this->assertDatabaseHas('alternatif_strategi', [
            'anak_id' => $this->anak->id,
            'strategi_id' => $this->s['Media Visual']->id,
            'status' => 'disarankan',
        ]);
    }

    // TC-15-02
    public function test_tc_15_02_guru_menambah_alternatif_sendiri_setelah_meninjau_saran(): void
    {
        $this->browse(function (Browser $browser) {
            $this->login($browser, 'miastacia');
            $this->catatObservasi($browser, KategoriRespons::KurangTertarik, 3);
            $this->bukaModeFokus($browser);

            $this->bukaDialogTambah($browser)
                ->within('@dialog-tambah-alternatif', function (Browser $dialog) {
                    $dialog->assertSeeIn('[data-ringkasan="anak"]', $this->anak->nama)
                        ->select('strategi_id', (string) $this->s['Kerja Kelompok']->id)
                        ->type('catatan', 'Lebih antusias dengan teman dekat.');
                    $this->jeda($dialog)->click('@simpan-alternatif');
                })
                ->waitFor('@pesan-status')
                ->assertSeeIn('@pesan-status', "Alternatif strategi untuk {$this->anak->nama} berhasil ditambahkan.")
                ->assertVisible('@penanda-manual')
                ->assertSee('Lebih antusias dengan teman dekat.');
            $this->jeda($browser);
        });

        $this->assertDatabaseHas('alternatif_strategi', [
            'anak_id' => $this->anak->id,
            'strategi_id' => $this->s['Kerja Kelompok']->id,
            'aturan_id' => null,
            'dasar_saran' => 'Lebih antusias dengan teman dekat.',
            'status' => 'disarankan',
        ]);
    }

    // TC-15-03
    public function test_tc_15_03_guru_menulis_catatan_teks_biasa_yang_tersimpan_dan_tampil(): void
    {
        $catatan = 'Anak lebih fokus saat cerita dibantu gambar berwarna.';

        $this->browse(function (Browser $browser) use ($catatan) {
            $this->login($browser, 'miastacia');
            $this->catatObservasi($browser, KategoriRespons::KurangTertarik, 1);
            $this->bukaModeFokus($browser);

            $this->bukaDialogTambah($browser)
                ->within('@dialog-tambah-alternatif', function (Browser $dialog) use ($catatan) {
                    $dialog->select('strategi_id', (string) $this->s['Permainan Gerak']->id)
                        ->type('catatan', $catatan);
                    $this->jeda($dialog);

                    // Teks di bawah batas: penghitung sesuai panjang teks, tanpa peringatan.
                    $dialog->assertSeeIn('@hitung-catatan', mb_strlen($catatan).'/1000')
                        ->assertMissing('@batas-catatan');
                    $this->jeda($dialog)->click('@simpan-alternatif');
                })
                ->waitFor('@pesan-status')
                ->assertSeeIn('@pesan-status', "Alternatif strategi untuk {$this->anak->nama} berhasil ditambahkan.")
                ->assertSee('Permainan Gerak')
                ->assertSee($catatan);
            $this->jeda($browser);
        });

        $this->assertDatabaseHas('alternatif_strategi', [
            'anak_id' => $this->anak->id,
            'strategi_id' => $this->s['Permainan Gerak']->id,
            'aturan_id' => null,
            'dasar_saran' => $catatan,
            'status' => 'disarankan',
        ]);
    }

    // TC-15-04
    public function test_tc_15_04_guru_menambah_alternatif_tanpa_menunggu_saran_sistem(): void
    {
        $this->browse(function (Browser $browser) {
            // Baru 1 observasi, jadi aturan (minimal 3 kejadian) belum terpenuhi dan sistem belum memberi saran.
            $this->login($browser, 'miastacia');
            $this->catatObservasi($browser, KategoriRespons::KurangTertarik, 1);

            $this->jeda($browser->clickLink('Alternatif Strategi')->waitForLocation('/guru/alternatif-strategi'))
                ->within('@baris-anak-'.$this->anak->id, function (Browser $baris) {
                    $baris->assertSee('1 dari minimal 3 kejadian.')
                        ->assertPresent('@belum-ada-alternatif');
                });

            $this->jeda($browser->click('@tautan-anak-'.$this->anak->id)
                ->waitForLocation('/guru/alternatif-strategi/anak/'.$this->anak->id))
                ->assertPresent('@belum-ada-alternatif')
                ->assertDontSee('Dari aturan');

            // Guru tetap bisa menentukan alternatif sendiri (FR-13).
            $this->bukaDialogTambah($browser)
                ->within('@dialog-tambah-alternatif', function (Browser $dialog) {
                    $dialog->select('strategi_id', (string) $this->s['Media Visual']->id)
                        ->type('catatan', 'Belum ada saran sistem, tetapi anak tampak bosan saat bercerita lisan.');
                    $this->jeda($dialog)->click('@simpan-alternatif');
                })
                ->waitFor('@pesan-status')
                ->assertSeeIn('@pesan-status', "Alternatif strategi untuk {$this->anak->nama} berhasil ditambahkan.")
                ->assertVisible('@penanda-manual')
                ->assertSee('Media Visual')
                ->assertDontSee('Dari aturan');
            $this->jeda($browser);
        });

        $this->assertDatabaseHas('alternatif_strategi', [
            'anak_id' => $this->anak->id,
            'strategi_id' => $this->s['Media Visual']->id,
            'aturan_id' => null,
            'status' => 'disarankan',
        ]);
        $this->assertSame(0, AlternatifStrategi::query()->where('anak_id', $this->anak->id)->whereNotNull('aturan_id')->count());
    }

    // ---------------------------------------------------------------- Negatif

    // TC-15-05
    public function test_tc_15_05_satu_observasi_belum_cukup_untuk_menyusun_saran(): void
    {
        $this->browse(function (Browser $browser) {
            $this->login($browser, 'miastacia');
            $this->catatObservasi($browser, KategoriRespons::KurangTertarik, 1);

            $this->jeda($browser->clickLink('Alternatif Strategi')->waitForLocation('/guru/alternatif-strategi'))
                ->within('@baris-anak-'.$this->anak->id, function (Browser $baris) {
                    $baris->assertSee('1 dari minimal 3 kejadian.')
                        ->assertPresent('@belum-ada-alternatif');
                });
        });

        $this->assertDatabaseMissing('alternatif_strategi', ['anak_id' => $this->anak->id]);
    }

    // TC-15-06
    public function test_tc_15_06_strategi_yang_masih_disarankan_tidak_bisa_ditambah_lagi(): void
    {
        $this->browse(function (Browser $browser) {
            $this->login($browser, 'miastacia');
            $this->catatObservasi($browser, KategoriRespons::KurangTertarik, 3);
            $this->bukaModeFokus($browser);

            $this->bukaDialogTambah($browser)
                ->within('@dialog-tambah-alternatif', function (Browser $dialog) {
                    // Media Visual sudah menjadi saran "disarankan" dari aturan R1.
                    $this->jeda($dialog->select('strategi_id', (string) $this->s['Media Visual']->id))->click('@simpan-alternatif');
                })
                ->waitFor('@salah-strategi')
                ->assertSeeIn('@salah-strategi', 'Strategi ini sudah ada di daftar saran anak.')
                ->assertVisible('@pesan-salah');
            $this->jeda($browser);
        });

        $this->assertSame(1, AlternatifStrategi::query()->where('anak_id', $this->anak->id)->where('strategi_id', $this->s['Media Visual']->id)->count());
    }

    // TC-15-07
    public function test_tc_15_07_simpan_alternatif_tanpa_memilih_strategi_ditolak(): void
    {
        $this->browse(function (Browser $browser) {
            $this->login($browser, 'miastacia');
            $this->catatObservasi($browser, KategoriRespons::KurangTertarik, 1);
            $this->bukaModeFokus($browser);

            $this->bukaDialogTambah($browser)
                ->within('@dialog-tambah-alternatif', function (Browser $dialog) {
                    $this->jeda($dialog->type('catatan', 'Tanpa strategi.'))->click('@simpan-alternatif');
                });

            // Pilihan strategi wajib diisi, jadi browser menahan pengiriman dan dialog tetap terbuka.
            $this->jeda($browser)
                ->assertVisible('@dialog-tambah-alternatif')
                ->assertMissing('@pesan-status');
        });

        $this->assertDatabaseMissing('alternatif_strategi', ['anak_id' => $this->anak->id]);
    }

    // TC-15-08
    public function test_tc_15_08_catatan_alternatif_dibatasi_1000_karakter(): void
    {
        $this->browse(function (Browser $browser) {
            $this->login($browser, 'miastacia');
            $this->catatObservasi($browser, KategoriRespons::KurangTertarik, 1);
            $this->bukaModeFokus($browser);

            // Ketikan berhenti di 1000 karakter, penghitung dan peringatan muncul di form.
            $this->bukaDialogTambah($browser)
                ->within('@dialog-tambah-alternatif', function (Browser $dialog) {
                    $dialog->type('catatan', str_repeat('a', 1001));
                    $this->jeda($dialog);

                    $this->assertSame(1000, mb_strlen((string) $dialog->value('#catatan')));
                    $dialog->assertSeeIn('@hitung-catatan', '1000/1000')
                        ->assertVisible('@batas-catatan')
                        ->assertSeeIn('@batas-catatan', 'Catatan maksimal 1000 karakter.');
                    $this->jeda($dialog);
                });
        });
    }

    // TC-15-09
    public function test_tc_15_09_catatan_berisi_script_ditampilkan_sebagai_teks(): void
    {
        $this->browse(function (Browser $browser) {
            $this->login($browser, 'miastacia');
            $this->catatObservasi($browser, KategoriRespons::KurangTertarik, 1);
            $this->bukaModeFokus($browser);

            $this->bukaDialogTambah($browser)
                ->within('@dialog-tambah-alternatif', function (Browser $dialog) {
                    $dialog->select('strategi_id', (string) $this->s['Permainan Gerak']->id)
                        ->type('catatan', '<script>alert(1)</script>');
                    $this->jeda($dialog)->click('@simpan-alternatif');
                })
                ->waitFor('@pesan-status')
                ->assertSee('<script>alert(1)</script>');
            $this->jeda($browser);
        });
    }

    // TC-15-10
    public function test_tc_15_10_guru_kelas_lain_tidak_melihat_pola_anak_kelas_lain(): void
    {
        $this->browse(function (Browser $browser) {
            // Mia Stacia mencatat 3 observasi untuk anak di kelasnya (TK A Melati), jadi anak itu punya pola dan saran.
            $this->login($browser, 'miastacia');
            $this->catatObservasi($browser, KategoriRespons::KurangTertarik, 3);

            // Ayu Rizki (TK B Cendrawasih) membuka menu Alternatif Strategi lewat sidebar.
            $this->login($browser, 'ayurizki');

            $this->jeda($browser->clickLink('Alternatif Strategi')->waitForLocation('/guru/alternatif-strategi'))
                ->assertSee('Belum ada pola respons yang perlu ditinjau di kelas ini.')
                ->assertMissing('@baris-anak-'.$this->anak->id)
                ->assertDontSee($this->anak->nama);
        });
    }

    // TC-15-11
    public function test_tc_15_11_tamu_diarahkan_ke_halaman_login(): void
    {
        $this->browse(function (Browser $browser) {
            $browser->logout()
                ->visit('/guru/alternatif-strategi/anak/'.$this->anak->id)
                ->assertPathIs('/login');
            $this->jeda($browser);
        });
    }

    // ---------------------------------------------------------------- Bantuan

    /**
     * Login seperti guru sungguhan: buka beranda, klik tombol "Masuk", lalu isi formulir login.
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
     * Anak tampil di daftar karena sudah punya minimal satu observasi yang cocok dengan aturan.
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
     * Buka dialog "+ Tambah alternatif" di Mode Fokus.
     */
    private function bukaDialogTambah(Browser $browser): Browser
    {
        $browser->click('@tombol-tambah-alternatif')->waitFor('@dialog-tambah-alternatif');

        return $this->jeda($browser);
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