<?php

namespace Tests\Browser\PBI15_AlternatifStrategi;

use App\Enums\KategoriRespons;
use App\Models\AlternatifStrategi;
use App\Models\Aktivitas;
use App\Models\Anak;
use App\Models\AspekPerkembangan;
use App\Models\AturanStrategi;
use App\Models\Kelas;
use App\Models\Observasi;
use App\Models\StrategiPembelajaran;
use App\Models\User;
use Laravel\Dusk\Browser;
use Tests\Browser\Concerns\DataUnik;
use Tests\DuskTestCase;

/**
 * PBI-15 Penyusunan alternatif strategi berdasarkan aturan (FR-13, NFR-04, NFR-06).
 *
 * Alur diuji dari awal lewat antarmuka: guru login, guru mencatat observasi lewat formulir
 * observasi, lalu sistem menyusun saran dan guru meninjau atau menambah alternatif strategi.
 *
 * Tanpa DatabaseMigrations/DatabaseTruncation dan tanpa seeder: data awal (guru, kelas, anak,
 * data master, aturan) dibuat lewat factory dengan kode unik, jadi setiap run menambah data baru
 * di database sipandai_dusk dan data run sebelumnya tetap tersimpan.
 */
class AlternatifStrategiTest extends DuskTestCase
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

    // TC-15-01 | Positif | Guru mencatat 3 observasi, sistem menyusun saran dari aturan
    public function test_tc_15_01_guru_login_mencatat_tiga_observasi_lalu_sistem_menyusun_saran(): void
    {
        $this->browse(function (Browser $browser) {
            $this->login($browser, $this->mia, self::SANDI_MIA);
            $this->catatObservasi($browser, KategoriRespons::KurangTertarik, 3);

            $browser->clickLink('Alternatif Strategi')
                ->waitForLocation('/guru/alternatif-strategi')
                ->assertSeeIn('@jumlah-saran', '1')
                ->within('@baris-anak-'.$this->anak->id, function (Browser $baris) {
                    $baris->assertSee($this->anak->nama)
                        ->assertSee('Kurang tertarik')
                        ->assertSee($this->s['Bercerita Lisan']->nama)
                        ->assertSee($this->s['Media Visual']->nama);
                })
                ->click('@tautan-anak-'.$this->anak->id)
                ->waitForLocation('/guru/alternatif-strategi/anak/'.$this->anak->id)
                ->assertPresent('[dusk^="kartu-saran-"]')
                ->assertSee('Dari aturan')
                ->assertSee('Respons Kurang tertarik tercatat 3 kali pada strategi '.$this->s['Bercerita Lisan']->nama);
        });

        $this->assertSame(3, Observasi::where('anak_id', $this->anak->id)->count());
        $this->assertDatabaseHas('alternatif_strategi', [
            'anak_id' => $this->anak->id,
            'strategi_id' => $this->s['Media Visual']->id,
            'status' => 'disarankan',
        ]);
    }

    // TC-15-02 | Positif | Guru menambah alternatif sendiri setelah meninjau saran
    public function test_tc_15_02_guru_menambah_alternatif_sendiri_setelah_meninjau_saran(): void
    {
        $this->browse(function (Browser $browser) {
            $this->login($browser, $this->mia, self::SANDI_MIA);
            $this->catatObservasi($browser, KategoriRespons::KurangTertarik, 3);
            $this->bukaModeFokus($browser);

            $this->bukaDialogTambah($browser)
                ->within('@dialog-tambah-alternatif', function (Browser $dialog) {
                    $dialog->assertSeeIn('[data-ringkasan="anak"]', $this->anak->nama)
                        ->select('strategi_id', (string) $this->s['Kerja Kelompok']->id)
                        ->type('catatan', 'Lebih antusias dengan teman dekat.')
                        ->click('@simpan-alternatif');
                })
                ->waitFor('@pesan-status')
                ->assertSeeIn('@pesan-status', "Alternatif strategi untuk {$this->anak->nama} berhasil ditambahkan.")
                ->assertVisible('@penanda-manual')
                ->assertSee('Lebih antusias dengan teman dekat.');
        });

        $this->assertDatabaseHas('alternatif_strategi', [
            'anak_id' => $this->anak->id,
            'strategi_id' => $this->s['Kerja Kelompok']->id,
            'aturan_id' => null,
            'dasar_saran' => 'Lebih antusias dengan teman dekat.',
            'status' => 'disarankan',
        ]);
    }

    // TC-15-03 | Positif | Catatan teks biasa tersimpan dan tampil
    public function test_tc_15_03_guru_menulis_catatan_teks_biasa_yang_tersimpan_dan_tampil(): void
    {
        $catatan = 'Anak lebih fokus saat cerita dibantu gambar berwarna.';

        $this->browse(function (Browser $browser) use ($catatan) {
            $this->login($browser, $this->mia, self::SANDI_MIA);
            $this->catatObservasi($browser, KategoriRespons::KurangTertarik, 1);
            $this->bukaModeFokus($browser);

            $this->bukaDialogTambah($browser)
                ->within('@dialog-tambah-alternatif', function (Browser $dialog) use ($catatan) {
                    $dialog->select('strategi_id', (string) $this->s['Permainan Gerak']->id)
                        ->type('catatan', $catatan)
                        // Teks di bawah batas: penghitung sesuai panjang teks, tanpa peringatan.
                        ->assertSeeIn('@hitung-catatan', mb_strlen($catatan).'/1000')
                        ->assertMissing('@batas-catatan')
                        ->click('@simpan-alternatif');
                })
                ->waitFor('@pesan-status')
                ->assertSeeIn('@pesan-status', "Alternatif strategi untuk {$this->anak->nama} berhasil ditambahkan.")
                ->assertSee($this->s['Permainan Gerak']->nama)
                ->assertSee($catatan);
        });

        $this->assertDatabaseHas('alternatif_strategi', [
            'anak_id' => $this->anak->id,
            'strategi_id' => $this->s['Permainan Gerak']->id,
            'aturan_id' => null,
            'dasar_saran' => $catatan,
            'status' => 'disarankan',
        ]);
    }

    // TC-15-04 | Positif | Guru menambah alternatif tanpa menunggu saran sistem
    public function test_tc_15_04_guru_menambah_alternatif_tanpa_menunggu_saran_sistem(): void
    {
        $this->browse(function (Browser $browser) {
            // Baru 1 observasi, jadi aturan (minimal 3 kejadian) belum terpenuhi dan sistem belum memberi saran.
            $this->login($browser, $this->mia, self::SANDI_MIA);
            $this->catatObservasi($browser, KategoriRespons::KurangTertarik, 1);

            $browser->clickLink('Alternatif Strategi')
                ->waitForLocation('/guru/alternatif-strategi')
                ->within('@baris-anak-'.$this->anak->id, function (Browser $baris) {
                    $baris->assertSee('1 dari minimal 3 kejadian.')
                        ->assertPresent('@belum-ada-alternatif');
                })
                ->click('@tautan-anak-'.$this->anak->id)
                ->waitForLocation('/guru/alternatif-strategi/anak/'.$this->anak->id)
                ->assertPresent('@belum-ada-alternatif')
                ->assertDontSee('Dari aturan');

            // Guru tetap bisa menentukan alternatif sendiri (FR-13).
            $this->bukaDialogTambah($browser)
                ->within('@dialog-tambah-alternatif', function (Browser $dialog) {
                    $dialog->select('strategi_id', (string) $this->s['Media Visual']->id)
                        ->type('catatan', 'Belum ada saran sistem, tetapi anak tampak bosan saat bercerita lisan.')
                        ->click('@simpan-alternatif');
                })
                ->waitFor('@pesan-status')
                ->assertSeeIn('@pesan-status', "Alternatif strategi untuk {$this->anak->nama} berhasil ditambahkan.")
                ->assertVisible('@penanda-manual')
                ->assertSee($this->s['Media Visual']->nama)
                ->assertDontSee('Dari aturan');
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

    // TC-15-05 | Negatif | Satu observasi belum cukup untuk menyusun saran
    public function test_tc_15_05_satu_observasi_belum_cukup_untuk_menyusun_saran(): void
    {
        $this->browse(function (Browser $browser) {
            $this->login($browser, $this->mia, self::SANDI_MIA);
            $this->catatObservasi($browser, KategoriRespons::KurangTertarik, 1);

            $browser->clickLink('Alternatif Strategi')
                ->waitForLocation('/guru/alternatif-strategi')
                ->within('@baris-anak-'.$this->anak->id, function (Browser $baris) {
                    $baris->assertSee('1 dari minimal 3 kejadian.')
                        ->assertPresent('@belum-ada-alternatif');
                });
        });

        $this->assertSame(1, Observasi::where('anak_id', $this->anak->id)->count());
        $this->assertDatabaseMissing('alternatif_strategi', ['anak_id' => $this->anak->id]);
    }

    // TC-15-06 | Negatif | Strategi yang masih disarankan tidak bisa ditambah lagi
    public function test_tc_15_06_strategi_yang_masih_disarankan_tidak_bisa_ditambah_lagi(): void
    {
        $this->browse(function (Browser $browser) {
            $this->login($browser, $this->mia, self::SANDI_MIA);
            $this->catatObservasi($browser, KategoriRespons::KurangTertarik, 3);
            $this->bukaModeFokus($browser);

            $this->bukaDialogTambah($browser)
                ->within('@dialog-tambah-alternatif', function (Browser $dialog) {
                    // Media Visual sudah menjadi saran berstatus disarankan dari aturan.
                    $dialog->select('strategi_id', (string) $this->s['Media Visual']->id)
                        ->click('@simpan-alternatif');
                })
                ->waitFor('@salah-strategi')
                ->assertSeeIn('@salah-strategi', 'Strategi ini sudah ada di daftar saran anak.')
                ->assertVisible('@pesan-salah');
        });

        $this->assertSame(1, AlternatifStrategi::query()
            ->where('anak_id', $this->anak->id)
            ->where('strategi_id', $this->s['Media Visual']->id)
            ->count());
    }

    // TC-15-07 | Negatif | Simpan alternatif tanpa memilih strategi ditolak
    public function test_tc_15_07_simpan_alternatif_tanpa_memilih_strategi_ditolak(): void
    {
        $this->browse(function (Browser $browser) {
            $this->login($browser, $this->mia, self::SANDI_MIA);
            $this->catatObservasi($browser, KategoriRespons::KurangTertarik, 1);
            $this->bukaModeFokus($browser);

            $this->bukaDialogTambah($browser)
                ->within('@dialog-tambah-alternatif', function (Browser $dialog) {
                    $dialog->type('catatan', 'Tanpa strategi.')
                        ->click('@simpan-alternatif');
                })
                // Pilihan strategi wajib diisi, jadi browser menahan pengiriman dan dialog tetap terbuka.
                ->pause(500)
                ->assertVisible('@dialog-tambah-alternatif')
                ->assertMissing('@pesan-status');
        });

        $this->assertDatabaseMissing('alternatif_strategi', ['anak_id' => $this->anak->id]);
    }

    // TC-15-08 | Batas | Catatan alternatif dibatasi 1000 karakter
    public function test_tc_15_08_catatan_alternatif_dibatasi_1000_karakter(): void
    {
        $this->browse(function (Browser $browser) {
            $this->login($browser, $this->mia, self::SANDI_MIA);
            $this->catatObservasi($browser, KategoriRespons::KurangTertarik, 1);
            $this->bukaModeFokus($browser);

            // Ketikan berhenti di 1000 karakter, penghitung dan peringatan muncul di form.
            $this->bukaDialogTambah($browser)
                ->within('@dialog-tambah-alternatif', function (Browser $dialog) {
                    $dialog->type('catatan', str_repeat('a', 1001));

                    $this->assertSame(1000, mb_strlen((string) $dialog->value('#catatan')));
                    $dialog->assertSeeIn('@hitung-catatan', '1000/1000')
                        ->assertVisible('@batas-catatan')
                        ->assertSeeIn('@batas-catatan', 'Catatan maksimal 1000 karakter.');
                });
        });

        // Form belum disimpan, jadi belum ada alternatif untuk anak ini.
        $this->assertDatabaseMissing('alternatif_strategi', ['anak_id' => $this->anak->id]);
    }

    // TC-15-09 | Negatif | Catatan berisi script disimpan dan ditampilkan sebagai teks biasa
    public function test_tc_15_09_catatan_berisi_script_ditampilkan_sebagai_teks(): void
    {
        $this->browse(function (Browser $browser) {
            $this->login($browser, $this->mia, self::SANDI_MIA);
            $this->catatObservasi($browser, KategoriRespons::KurangTertarik, 1);
            $this->bukaModeFokus($browser);

            $this->bukaDialogTambah($browser)
                ->within('@dialog-tambah-alternatif', function (Browser $dialog) {
                    $dialog->select('strategi_id', (string) $this->s['Permainan Gerak']->id)
                        ->type('catatan', '<script>alert(1)</script>')
                        ->click('@simpan-alternatif');
                })
                ->waitFor('@pesan-status')
                ->assertSee('<script>alert(1)</script>');
        });

        $this->assertDatabaseHas('alternatif_strategi', [
            'anak_id' => $this->anak->id,
            'strategi_id' => $this->s['Permainan Gerak']->id,
            'dasar_saran' => '<script>alert(1)</script>',
        ]);
    }

    // ---------------------------------------------------------------- Error

    // TC-15-10 | Error | Guru kelas lain tidak melihat pola anak kelas lain
    public function test_tc_15_10_guru_kelas_lain_tidak_melihat_pola_anak_kelas_lain(): void
    {
        $this->browse(function (Browser $browser) {
            // Mia mencatat 3 observasi untuk anak di kelasnya, jadi anak itu punya pola.
            $this->login($browser, $this->mia, self::SANDI_MIA);
            $this->catatObservasi($browser, KategoriRespons::KurangTertarik, 3);

            // Ayu (kelas lain) membuka menu Alternatif Strategi lewat sidebar.
            $this->login($browser, $this->ayu, self::SANDI_AYU);

            $browser->clickLink('Alternatif Strategi')
                ->waitForLocation('/guru/alternatif-strategi')
                ->assertSee('Belum ada pola respons yang perlu ditinjau di kelas ini.')
                ->assertMissing('@baris-anak-'.$this->anak->id)
                ->assertDontSee($this->anak->nama);
        });

        // Observasi Mia tetap tersimpan dan tidak ada alternatif yang dibuat lewat akun Ayu.
        $this->assertSame(3, Observasi::where('anak_id', $this->anak->id)->count());
        $this->assertSame(0, AlternatifStrategi::where('anak_id', $this->anak->id)->whereNull('aturan_id')->count());
    }

    // TC-15-11 | Error | Tamu diarahkan ke halaman login
    public function test_tc_15_11_tamu_diarahkan_ke_halaman_login(): void
    {
        $this->browse(function (Browser $browser) {
            $browser->logout()
                ->visit('/guru/alternatif-strategi/anak/'.$this->anak->id)
                ->waitForLocation('/login')
                ->assertPathIs('/login');
        });

        $this->assertDatabaseMissing('alternatif_strategi', ['anak_id' => $this->anak->id]);
    }

    // TC-15-12 | Positif | Pesan sukses hilang otomatis setelah 5 detik
    public function test_tc_15_12_pesan_sukses_hilang_otomatis_setelah_lima_detik(): void
    {
        $this->browse(function (Browser $browser) {
            $this->login($browser, $this->mia, self::SANDI_MIA);
            $this->catatObservasi($browser, KategoriRespons::KurangTertarik, 1);
            $this->bukaModeFokus($browser);

            $this->bukaDialogTambah($browser)
                ->within('@dialog-tambah-alternatif', function (Browser $dialog) {
                    $dialog->select('strategi_id', (string) $this->s['Permainan Gerak']->id)
                        ->click('@simpan-alternatif');
                })
                ->waitFor('@pesan-status')
                ->assertVisible('@pesan-status')
                ->assertSeeIn('@pesan-status', "Alternatif strategi untuk {$this->anak->nama} berhasil ditambahkan.")
                // Banner tampil 5 detik, lalu memudar 500 ms dan dihapus dari DOM.
                ->waitUntilMissing('@pesan-status', 8)
                ->assertMissing('@pesan-status');
        });
    }
        // TC-15-13 | Positif | Alternatif tanpa catatan menampilkan "Belum ada catatan."
    public function test_tc_15_13_alternatif_tanpa_catatan_menampilkan_belum_ada_catatan(): void
    {
        $this->browse(function (Browser $browser) {
            $this->login($browser, $this->mia, self::SANDI_MIA);
            $this->catatObservasi($browser, KategoriRespons::KurangTertarik, 1);
            $this->bukaModeFokus($browser);

            // Catatan dibiarkan kosong.
            $this->bukaDialogTambah($browser)
                ->within('@dialog-tambah-alternatif', function (Browser $dialog) {
                    $dialog->select('strategi_id', (string) $this->s['Kerja Kelompok']->id)
                        ->click('@simpan-alternatif');
                })
                ->waitFor('@pesan-status')
                ->assertSeeIn('@pesan-status', "Alternatif strategi untuk {$this->anak->nama} berhasil ditambahkan.");

            $id = (int) AlternatifStrategi::query()
                ->where('anak_id', $this->anak->id)
                ->where('strategi_id', $this->s['Kerja Kelompok']->id)
                ->value('id');

            $browser->assertSeeIn('@kartu-saran-'.$id, 'Belum ada catatan.');

            // Label "Catatan guru" tidak tampil kalau catatan kosong (label ditulis kapital lewat CSS).
            $this->assertStringNotContainsStringIgnoringCase('catatan guru', $browser->text('@kartu-saran-'.$id));
        });

        $this->assertDatabaseHas('alternatif_strategi', [
            'anak_id' => $this->anak->id,
            'strategi_id' => $this->s['Kerja Kelompok']->id,
            'aturan_id' => null,
            'dasar_saran' => null,
        ]);
    }
    
        // TC-15-14 | Positif | Guru menutup pesan notifikasi dengan tombol ×
    public function test_tc_15_14_guru_menutup_pesan_notifikasi_dengan_tombol_tutup(): void
    {
        $this->browse(function (Browser $browser) {
            $this->login($browser, $this->mia, self::SANDI_MIA);
            $this->catatObservasi($browser, KategoriRespons::KurangTertarik, 1);
            $this->bukaModeFokus($browser);

            $this->bukaDialogTambah($browser)
                ->within('@dialog-tambah-alternatif', function (Browser $dialog) {
                    $dialog->select('strategi_id', (string) $this->s['Permainan Gerak']->id)
                        ->click('@simpan-alternatif');
                })
                ->waitFor('@pesan-status')
                ->assertSeeIn('@pesan-status', "Alternatif strategi untuk {$this->anak->nama} berhasil ditambahkan.")
                // Guru tidak menunggu 5 detik, tapi langsung menutup pesan.
                ->click('@pesan-status button[aria-label="Tutup"]')
                ->waitUntilMissing('@pesan-status', 2)
                ->assertMissing('@pesan-status');
        });

        // Menutup pesan hanya mengubah tampilan, data tetap tersimpan.
        $this->assertDatabaseHas('alternatif_strategi', [
            'anak_id' => $this->anak->id,
            'strategi_id' => $this->s['Permainan Gerak']->id,
            'status' => 'disarankan',
        ]);
    }
    
    // ---------------------------------------------------------------- Bantuan

    /**
     * Login seperti guru sungguhan: buka beranda, klik tombol "Masuk", lalu isi formulir login.
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
     * Catat observasi anak lewat formulir observasi (PBI-07), sebanyak $jumlah kali,
     * dengan aktivitas Bermain peran, aspek Bahasa, dan strategi Bercerita Lisan milik test ini.
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
                ->type('catatan_pendukung', "Observasi ke-{$i}.")
                ->press('Simpan Observasi')
                ->waitForText('Observasi berhasil disimpan');
        }

        return $browser;
    }

    /**
     * Buka Mode Fokus seperti guru: menu Alternatif Strategi, lalu klik nama anak di daftar.
     * Anak tampil di daftar karena sudah punya minimal satu observasi yang cocok dengan aturan.
     */
    private function bukaModeFokus(Browser $browser): Browser
    {
        return $browser->clickLink('Alternatif Strategi')
            ->waitForLocation('/guru/alternatif-strategi')
            ->assertPresent('@baris-anak-'.$this->anak->id)
            ->click('@tautan-anak-'.$this->anak->id)
            ->waitForLocation('/guru/alternatif-strategi/anak/'.$this->anak->id)
            ->waitFor('@tombol-tambah-alternatif');
    }

    /**
     * Buka dialog "+ Tambah alternatif" di Mode Fokus.
     */
    private function bukaDialogTambah(Browser $browser): Browser
    {
        return $browser->click('@tombol-tambah-alternatif')
            ->waitFor('@dialog-tambah-alternatif');
    }
}
