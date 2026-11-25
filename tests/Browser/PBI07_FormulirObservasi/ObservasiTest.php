<?php

namespace Tests\Browser\PBI07_FormulirObservasi;

use App\Enums\KategoriRespons;
use App\Models\Aktivitas;
use App\Models\Anak;
use App\Models\AspekPerkembangan;
use App\Models\Kelas;
use App\Models\Observasi;
use App\Models\StrategiPembelajaran;
use App\Models\User;
use Laravel\Dusk\Browser;
use Tests\Browser\Concerns\DataUnik;
use Tests\DuskTestCase;

// Fitur: PBI-07 Formulir Pencatatan Observasi per Anak
// Pengujian GUI (Laravel Dusk + Selenium) sesuai TC_PBI7.xlsx.
class ObservasiTest extends DuskTestCase
{
    // Tanpa DatabaseMigrations: data hasil test tetap tersimpan di database sipandai_dusk.
    // Setiap test memakai kode unik agar data antar-run tidak bentrok.
    use DataUnik;

    private const KATA_SANDI = 'GuruMia#2026';

    private const URL_DASHBOARD = '/guru/dashboard';

    private const URL_INDEX = '/guru/observasi';

    private const URL_TAMBAH = '/guru/observasi/tambah';

    private User $guru;

    private User $guruLain;

    private Kelas $kelasGuru;

    private Kelas $kelasGuruLain;

    private Anak $anak1;

    private Anak $anak2;

    private Anak $anakKelasLain;

    private Anak $anakNonaktif;

    private Aktivitas $aktivitas;

    private AspekPerkembangan $aspek;

    private StrategiPembelajaran $strategi;

    protected function setUp(): void
    {
        parent::setUp();

        $kode = $this->kodeUnik();

        // Pre Condition: data dummy dibuat lewat factory dengan kode unik, misalnya "Melati k7m2q".
        $this->guru = User::factory()->create([
            'nama' => "Mia Stacia {$kode}",
            'nama_pengguna' => "mia{$kode}",
            'email' => "mia.{$kode}@tkceria.sch.id",
            'kata_sandi' => self::KATA_SANDI,
        ]);

        $this->guruLain = User::factory()->create([
            'nama' => "Ayu Rizki {$kode}",
            'nama_pengguna' => "ayu{$kode}",
            'email' => "ayu.{$kode}@tkceria.sch.id",
        ]);

        // Kelas yang menjadi tanggung jawab guru.
        $this->kelasGuru = Kelas::factory()->create([
            'guru_id' => $this->guru->id,
            'nama_kelas' => "Melati {$kode}",
            'kelompok' => 'TK A',
        ]);

        // Kelas milik guru lain.
        $this->kelasGuruLain = Kelas::factory()->create([
            'guru_id' => $this->guruLain->id,
            'nama_kelas' => "Cendrawasih {$kode}",
            'kelompok' => 'TK B',
        ]);

        // Data master observasi.
        $this->aktivitas = Aktivitas::factory()->create(['nama' => "Menyusun balok {$kode}", 'aktif' => true]);
        $this->aspek = AspekPerkembangan::factory()->create(['nama' => "Kognitif {$kode}"]);
        $this->strategi = StrategiPembelajaran::factory()->create(['nama' => "Media visual {$kode}", 'aktif' => true]);

        // Anak pada kelas guru.
        $this->anak1 = Anak::factory()->create([
            'kelas_id' => $this->kelasGuru->id,
            'nama' => "Dedek Tirta {$kode}",
            'nama_panggilan' => 'Tirta',
            'aktif' => true,
        ]);

        $this->anak2 = Anak::factory()->create([
            'kelas_id' => $this->kelasGuru->id,
            'nama' => "Waluyo Putra {$kode}",
            'nama_panggilan' => 'Waluyo',
            'aktif' => true,
        ]);

        // Anak pada kelas guru lain.
        $this->anakKelasLain = Anak::factory()->create([
            'kelas_id' => $this->kelasGuruLain->id,
            'nama' => "Cagak Putra {$kode}",
            'nama_panggilan' => 'Cagak',
            'aktif' => true,
        ]);

        // Anak nonaktif tidak boleh muncul pada formulir baru.
        $this->anakNonaktif = Anak::factory()->create([
            'kelas_id' => $this->kelasGuru->id,
            'nama' => "Sadina Putri {$kode}",
            'nama_panggilan' => 'Sadina',
            'aktif' => false,
        ]);
    }

    private function jumlahObservasiGuru(): int
    {
        return Observasi::where('guru_id', $this->guru->id)->count();
    }

    /**
     * TC.Obs.000.001
     * Guru login dengan username dan password yang benar.
     */
    public function test_tc_obs_000_001_login_guru(): void
    {
        $this->browse(function (Browser $browser) {
            // 1. Buka halaman Login -> halaman Login tampil
            $browser->driver->manage()->deleteAllCookies();
            $browser->visit('/login')
                ->assertSee('Masuk ke SIPANDAI')
                ->assertSee('Email atau nama pengguna')
                ->assertSee('Kata sandi')
                ->assertPresent('input[name="login"]')
                ->assertPresent('input[name="kata_sandi"]');

            // 2. Masukkan username dan password -> field terisi
            $browser->type('login', $this->guru->nama_pengguna)
                ->type('kata_sandi', self::KATA_SANDI)
                ->assertInputValue('login', $this->guru->nama_pengguna)
                ->assertInputValue('kata_sandi', self::KATA_SANDI);

            // 3. Klik Login -> diarahkan ke Dashboard guru
            $browser->press('Masuk')
                ->waitForLocation(self::URL_DASHBOARD)
                ->assertPathIs(self::URL_DASHBOARD)
                ->assertSee("Selamat datang, {$this->guru->nama}");
        });
    }

    /**
     * TC.Obs.001.001
     * Guru melihat daftar kelas dan anak sesuai kelas yang menjadi tanggung jawabnya.
     */
    public function test_tc_obs_001_001_daftar_kelas_dan_anak_sesuai_kelas_guru(): void
    {
        $this->browse(function (Browser $browser) {
            $this->loginGuru($browser);

            // 1. Klik menu Observasi, lalu Tambah Observasi -> formulir tampil
            $this->bukaFormulir($browser);

            // 2. Klik dropdown Pilih Kelas -> hanya Melati, Cendrawasih tidak tampil
            $browser->click('#kelas_id')
                ->assertSelectHasOptions('kelas_id', [$this->kelasGuru->id])
                ->assertSelectMissingOptions('kelas_id', [$this->kelasGuruLain->id])
                ->assertSeeIn('#kelas_id', $this->kelasGuru->nama_kelas)
                ->assertDontSeeIn('#kelas_id', $this->kelasGuruLain->nama_kelas);

            $this->assertSame([$this->kelasGuru->nama_kelas], $this->opsiTanpaPlaceholder($browser, 'kelas_id'));

            // 3. Pilih Melati, klik dropdown Pilih Anak
            $browser->select('kelas_id', $this->kelasGuru->id)
                ->click('#anak_id')
                ->assertSelectHasOptions('anak_id', [$this->anak1->id, $this->anak2->id])
                ->assertSelectMissingOptions('anak_id', [$this->anakKelasLain->id, $this->anakNonaktif->id]);

            $anak = $this->opsiTanpaPlaceholder($browser, 'anak_id');
            sort($anak);
            $this->assertSame([$this->anak1->nama, $this->anak2->nama], $anak);
        });
    }

    /**
     * TC.Obs.001.002
     * Guru memilih anak yang akan diobservasi sesuai kelas yang dipilih.
     */
    public function test_tc_obs_001_002_memilih_anak_sesuai_kelas(): void
    {
        $this->browse(function (Browser $browser) {
            $this->loginGuru($browser);

            // 1. Buka formulir -> Pilih Anak nonaktif, teks "Pilih kelas terlebih dahulu"
            $this->bukaFormulir($browser);
            $browser->assertDisabled('#anak_id')
                ->assertSeeIn('#anak_id', 'Pilih kelas terlebih dahulu');

            // 2. Pilih kelas Melati -> Pilih Anak aktif, hanya anak kelas Melati
            $browser->select('kelas_id', $this->kelasGuru->id)
                ->assertEnabled('#anak_id')
                ->assertSeeIn('#anak_id', 'Pilih anak')
                ->assertSelectMissingOptions('anak_id', [$this->anakKelasLain->id, $this->anakNonaktif->id]);

            $anak = $this->opsiTanpaPlaceholder($browser, 'anak_id');
            sort($anak);
            $this->assertSame([$this->anak1->nama, $this->anak2->nama], $anak);

            // 3. Pilih Dedek Tirta -> Dedek Tirta terpilih
            $browser->select('anak_id', $this->anak1->id)
                ->assertSelected('anak_id', $this->anak1->id);
        });
    }

    /**
     * TC.Obs.001.003
     * Formulir menampilkan semua kolom pencatatan observasi.
     */
    public function test_tc_obs_001_003_formulir_menampilkan_semua_kolom(): void
    {
        $this->browse(function (Browser $browser) {
            $this->loginGuru($browser);

            // 1. Buka formulir -> judul tampil
            $this->bukaFormulir($browser);
            $browser->assertSeeIn('h1', 'Formulir Observasi Perkembangan');

            // 2. Periksa kolom pada formulir
            $browser->assertSee('Tanggal Observasi')
                ->assertSee('Aktivitas Observasi')
                ->assertSee('Aspek Perkembangan')
                ->assertSee('Strategi Pembelajaran')
                ->assertSee('Respons Anak')
                ->assertSee('Deskripsi Pengamatan')
                ->assertPresent('input[type="date"][name="tanggal"]')
                ->assertPresent('select[name="aktivitas_id"]')
                ->assertPresent('select[name="aspek_id"]')
                ->assertPresent('select[name="strategi_id"]')
                ->assertPresent('input[type="radio"][name="kategori_respons"]')
                ->assertPresent('textarea[name="catatan_pendukung"]');
        });
    }

    /**
     * TC.Obs.002.001
     * Guru memilih aktivitas pembelajaran.
     */
    public function test_tc_obs_002_001_memilih_aktivitas_pembelajaran(): void
    {
        $this->browse(function (Browser $browser) {
            $this->loginGuru($browser);

            // 1. Bagian Kegiatan dan Strategi tampil
            $this->bukaFormulir($browser);
            $browser->assertSee('Kegiatan dan Strategi');

            // 2. Klik dropdown Aktivitas Observasi -> "Menyusun balok" tersedia
            $browser->click('#aktivitas_id')
                ->assertSelectHasOptions('aktivitas_id', [$this->aktivitas->id])
                ->assertSeeIn('#aktivitas_id', $this->aktivitas->nama);

            // 3. Pilih "Menyusun balok" -> terpilih
            $browser->select('aktivitas_id', $this->aktivitas->id)
                ->assertSelected('aktivitas_id', $this->aktivitas->id);
        });
    }

    /**
     * TC.Obs.002.002
     * Guru memilih strategi pembelajaran.
     */
    public function test_tc_obs_002_002_memilih_strategi_pembelajaran(): void
    {
        $this->browse(function (Browser $browser) {
            $this->loginGuru($browser);

            // 1. Bagian Kegiatan dan Strategi tampil
            $this->bukaFormulir($browser);
            $browser->assertSee('Kegiatan dan Strategi');

            // 2. Klik dropdown Strategi Pembelajaran -> "Media visual" tersedia
            $browser->click('#strategi_id')
                ->assertSelectHasOptions('strategi_id', [$this->strategi->id])
                ->assertSeeIn('#strategi_id', $this->strategi->nama);

            // 3. Pilih "Media visual" -> terpilih
            $browser->select('strategi_id', $this->strategi->id)
                ->assertSelected('strategi_id', $this->strategi->id);
        });
    }

    /**
     * TC.Obs.002.003
     * Guru memilih kategori respons anak.
     */
    public function test_tc_obs_002_003_memilih_kategori_respons(): void
    {
        $this->browse(function (Browser $browser) {
            $this->loginGuru($browser);

            // 1. Bagian Respons Anak tampil
            $this->bukaFormulir($browser);
            $browser->assertSee('Respons Anak');

            // 2. Tampil 4 pilihan respons
            $browser->assertSeeIn('.obs-resp-grid', 'Terlibat aktif')
                ->assertSeeIn('.obs-resp-grid', 'Terlibat dengan pendampingan')
                ->assertSeeIn('.obs-resp-grid', 'Kurang tertarik')
                ->assertSeeIn('.obs-resp-grid', 'Belum terlibat');

            $this->assertCount(4, $browser->elements('input[type="radio"][name="kategori_respons"]'));
            $this->assertCount(4, KategoriRespons::cases());

            // 3. Klik "Terlibat aktif" -> terpilih; hanya satu respons yang dapat dipilih
            $this->pilihRespons($browser, KategoriRespons::TerlibatAktif->value);
            $browser->assertRadioSelected('kategori_respons', KategoriRespons::TerlibatAktif->value);

            $this->pilihRespons($browser, KategoriRespons::KurangTertarik->value);
            $browser->assertRadioSelected('kategori_respons', KategoriRespons::KurangTertarik->value)
                ->assertRadioNotSelected('kategori_respons', KategoriRespons::TerlibatAktif->value);

            $this->pilihRespons($browser, KategoriRespons::TerlibatAktif->value);
            $browser->assertRadioSelected('kategori_respons', KategoriRespons::TerlibatAktif->value)
                ->assertRadioNotSelected('kategori_respons', KategoriRespons::KurangTertarik->value);

            $this->assertSame(1, $this->jumlahResponsTerpilih($browser));
        });
    }

    /**
     * TC.Obs.003.001
     * Guru menyimpan observasi dengan data lengkap.
     */
    public function test_tc_obs_003_001_menyimpan_observasi_data_lengkap(): void
    {
        $this->browse(function (Browser $browser) {
            $this->loginGuru($browser);

            // 1. Halaman Formulir Observasi tampil
            $this->bukaFormulir($browser);

            // 2. Isi semua field
            $this->isiFormulir($browser);

            $browser->assertSelected('kelas_id', $this->kelasGuru->id)
                ->assertSelected('anak_id', $this->anak1->id)
                ->assertInputValue('tanggal', '2026-11-14')
                ->assertSelected('aktivitas_id', $this->aktivitas->id)
                ->assertSelected('aspek_id', $this->aspek->id)
                ->assertSelected('strategi_id', $this->strategi->id)
                ->assertRadioSelected('kategori_respons', KategoriRespons::TerlibatAktif->value)
                ->assertInputValue('catatan_pendukung', 'Anak mengikuti kegiatan dengan baik.');

            // 3. Klik Simpan -> halaman Data Observasi, notifikasi tampil, data tersimpan
            $browser->press('Simpan Observasi')
                ->waitForLocation(self::URL_INDEX)
                ->assertPathIs(self::URL_INDEX)
                ->waitForTextIn('[data-notifikasi-popup]', 'Observasi berhasil disimpan')
                ->assertSeeIn('table.obs-table', $this->anak1->nama)
                ->assertSeeIn('table.obs-table', $this->aktivitas->nama);
        });

        $this->assertDatabaseHas('observasi', [
            'guru_id' => $this->guru->id,
            'anak_id' => $this->anak1->id,
            'tanggal' => '2026-11-14',
            'aktivitas_id' => $this->aktivitas->id,
            'aspek_id' => $this->aspek->id,
            'strategi_id' => $this->strategi->id,
            'kategori_respons' => KategoriRespons::TerlibatAktif->value,
            'catatan_pendukung' => 'Anak mengikuti kegiatan dengan baik.',
        ]);
    }

    /**
     * TC.Obs.003.002
     * Guru menyimpan observasi tanpa catatan pendukung.
     */
    public function test_tc_obs_003_002_menyimpan_observasi_tanpa_catatan(): void
    {
        $this->browse(function (Browser $browser) {
            $this->loginGuru($browser);

            // 1. Halaman Formulir Observasi tampil
            $this->bukaFormulir($browser);

            // 2. Field wajib terisi, catatan kosong
            $this->isiFormulir($browser, ['catatan_pendukung' => '']);
            $browser->assertInputValue('catatan_pendukung', '')
                ->assertRadioSelected('kategori_respons', KategoriRespons::TerlibatAktif->value);

            // 3. Klik Simpan -> tersimpan dengan catatan kosong, notifikasi tampil
            $browser->press('Simpan Observasi')
                ->waitForLocation(self::URL_INDEX)
                ->waitForTextIn('[data-notifikasi-popup]', 'Observasi berhasil disimpan');
        });

        $this->assertDatabaseHas('observasi', [
            'guru_id' => $this->guru->id,
            'anak_id' => $this->anak1->id,
            'catatan_pendukung' => null,
        ]);
    }

    /**
     * TC.Obs.003.003
     * Guru menyimpan observasi tanpa memilih respons anak (kolom wajib kosong).
     */
    public function test_tc_obs_003_003_menolak_observasi_tanpa_respons_anak(): void
    {
        $this->browse(function (Browser $browser) {
            $this->loginGuru($browser);

            // 1. Halaman Formulir Observasi tampil
            $this->bukaFormulir($browser);

            // 2. Isi semua field kecuali Respons Anak
            $this->isiFormulir($browser, ['kategori_respons' => null]);
            $this->assertSame(0, $this->jumlahResponsTerpilih($browser));

            // 3a. Klik Simpan -> ditolak validasi browser (atribut required)
            $browser->press('Simpan Observasi')
                ->pause(500)
                ->assertPathIs(self::URL_TAMBAH);

            $this->assertTrue($browser->script(
                "return document.querySelector('input[name=\"kategori_respons\"]').validity.valueMissing;"
            )[0]);

            // 3b. Validasi browser dilewati -> ditolak server, pesan tampil pada Respons Anak
            $browser->script("document.querySelector('form.obs-form').noValidate = true;");

            $browser->press('Simpan Observasi')
                ->waitForText('Pilih salah satu respons anak.')
                ->assertPathIs(self::URL_TAMBAH)
                ->assertSee('Periksa kembali isian Anda')
                ->assertSeeIn('.obs-resp-grid + p.obs-error', 'Pilih salah satu respons anak.');
        });

        $this->assertSame(0, $this->jumlahObservasiGuru());
    }

    /**
     * TC.Obs.003.004
     * Data observasi terhubung dengan anak yang dipilih.
     */
    public function test_tc_obs_003_004_observasi_terhubung_dengan_anak_yang_dipilih(): void
    {
        $this->browse(function (Browser $browser) {
            $this->loginGuru($browser);

            // 1. Halaman Formulir Observasi tampil
            $this->bukaFormulir($browser);

            // 2. Isi anak Waluyo Putra, tanggal 20/10/2026, respons Kurang tertarik
            $this->isiFormulir($browser, [
                'anak_id' => $this->anak2->id,
                'tanggal' => '2026-10-20',
                'kategori_respons' => KategoriRespons::KurangTertarik->value,
                'catatan_pendukung' => 'Anak membutuhkan pendampingan.',
            ]);

            $browser->assertSelected('anak_id', $this->anak2->id)
                ->assertInputValue('tanggal', '2026-10-20')
                ->assertRadioSelected('kategori_respons', KategoriRespons::KurangTertarik->value);

            // 3. Klik Simpan
            $browser->press('Simpan Observasi')
                ->waitForLocation(self::URL_INDEX)
                ->waitForTextIn('[data-notifikasi-popup]', 'Observasi berhasil disimpan');

            // 4. Observasi 20 Okt 2026 tampil atas nama Waluyo Putra, bukan Dedek Tirta
            $baris = collect($browser->elements('table.obs-table tbody tr'))
                ->map(fn ($tr) => $tr->getText())
                ->filter(fn (string $teks) => str_contains($teks, '20 Okt 2026'))
                ->values();

            $this->assertCount(1, $baris);
            $this->assertStringContainsString($this->anak2->nama, $baris[0]);
            $this->assertStringNotContainsString($this->anak1->nama, $baris[0]);
            $this->assertStringContainsString('Kurang tertarik', $baris[0]);
        });

        $this->assertDatabaseHas('observasi', [
            'guru_id' => $this->guru->id,
            'anak_id' => $this->anak2->id,
            'tanggal' => '2026-10-20',
            'kategori_respons' => KategoriRespons::KurangTertarik->value,
            'catatan_pendukung' => 'Anak membutuhkan pendampingan.',
        ]);

        $this->assertDatabaseMissing('observasi', [
            'anak_id' => $this->anak1->id,
            'tanggal' => '2026-10-20',
        ]);
    }

    /**
     * TC.Obs.004.001
     * Observasi yang sudah disimpan tampil pada riwayat anak.
     */
    public function test_tc_obs_004_001_observasi_tampil_pada_riwayat_anak(): void
    {
        $this->browse(function (Browser $browser) {
            $this->loginGuru($browser);

            // 1. Halaman Formulir Observasi Perkembangan tampil
            $this->bukaFormulir($browser);

            // 2. Simpan observasi Dedek Tirta
            $this->isiFormulir($browser, [
                'catatan_pendukung' => 'Catatan observasi PBI-07.',
            ]);

            $browser->press('Simpan Observasi')
                ->waitForLocation(self::URL_INDEX)
                ->waitForTextIn('[data-notifikasi-popup]', 'Observasi berhasil disimpan');

            // 3. Buka halaman Riwayat Observasi milik Dedek Tirta
            $urlRiwayat = '/guru/anak/'.$this->anak1->id.'/riwayat-observasi';

            $browser->visit($urlRiwayat)
                ->assertPathIs($urlRiwayat)
                ->assertSeeIn('h1', 'Riwayat Observasi')
                ->assertSee($this->anak1->nama_panggilan);

            // 4. Tampil aktivitas, respons, dan catatan
            $browser->assertSeeIn('#daftar-observasi', $this->aktivitas->nama)
                ->assertSeeIn('#daftar-observasi', 'Terlibat aktif')
                ->assertSeeIn('#daftar-observasi', 'Catatan observasi PBI-07.');
        });
    }

    // TC.Obs.005.001 (penyimpanan gagal karena gangguan database) tidak diotomasi dengan Dusk.
    // Mengganti nama kolom saat test berjalan bisa meninggalkan tabel observasi rusak untuk semua orang.
    // Skenario ini sudah diuji aman di Feature test TC-07-12 dengan mensimulasikan kegagalan query.

    /**
     * TC.Obs.006.001
     * Guru mencatat observasi pada kelas milik guru lain.
     */
    public function test_tc_obs_006_001_menolak_kelas_milik_guru_lain(): void
    {
        $this->browse(function (Browser $browser) {
            $this->loginGuru($browser);

            // 1. Kelas Cendrawasih tidak ada di pilihan kelas
            $this->bukaFormulir($browser);
            $browser->assertSelectMissingOptions('kelas_id', [$this->kelasGuruLain->id])
                ->assertDontSeeIn('#kelas_id', $this->kelasGuruLain->nama_kelas);

            // 2. Ubah nilai kelas -> Cendrawasih, anak -> Cagak Putra (Inspect Element)
            $this->isiFormulir($browser);
            $this->ubahNilaiOpsiTerpilih($browser, 'kelas_id', $this->kelasGuruLain->id);
            $this->ubahNilaiOpsiTerpilih($browser, 'anak_id', $this->anakKelasLain->id);

            $browser->assertValue('#kelas_id', (string) $this->kelasGuruLain->id)
                ->assertValue('#anak_id', (string) $this->anakKelasLain->id);

            // 3. Klik Simpan -> ditolak, kembali ke formulir dengan pesan pada Kelas
            $browser->press('Simpan Observasi')
                ->waitForText('kelas yang dipilih tidak valid.')
                ->assertPathIs(self::URL_TAMBAH)
                ->assertSeeIn('.obs-select:has(#kelas_id) + p.obs-error', 'kelas yang dipilih tidak valid.')
                ->assertAttributeContains('#kelas_id', 'class', 'is-invalid');
        });

        $this->assertSame(0, $this->jumlahObservasiGuru());
    }

    /**
     * TC.Obs.006.002
     * Guru mencatat anak dari kelas guru lain menggunakan kelas miliknya sendiri.
     */
    public function test_tc_obs_006_002_menolak_anak_kelas_lain_dengan_kelas_sendiri(): void
    {
        $this->browse(function (Browser $browser) {
            $this->loginGuru($browser);

            // 1. Halaman Formulir Observasi tampil
            $this->bukaFormulir($browser);

            // 2. Pilih kelas Melati, ubah nilai anak -> Cagak Putra (Inspect Element)
            $this->isiFormulir($browser);
            $this->ubahNilaiOpsiTerpilih($browser, 'anak_id', $this->anakKelasLain->id);

            $browser->assertValue('#kelas_id', (string) $this->kelasGuru->id)
                ->assertValue('#anak_id', (string) $this->anakKelasLain->id);

            // 3. Klik Simpan -> ditolak, kembali ke formulir dengan pesan pada Anak
            $browser->press('Simpan Observasi')
                ->waitForText('anak yang dipilih tidak valid.')
                ->assertPathIs(self::URL_TAMBAH)
                ->assertSeeIn('.obs-select:has(#anak_id) + p.obs-error', 'anak yang dipilih tidak valid.')
                ->assertAttributeContains('#anak_id', 'class', 'is-invalid');
        });

        $this->assertSame(0, $this->jumlahObservasiGuru());
    }

    // ------------------------------------------------------------------
    // Helper
    // ------------------------------------------------------------------

    /**
     * Pre-condition semua TC: guru Mia Stacia sudah login (TC.Obs.000.001).
     */
    private function loginGuru(Browser $browser): Browser
    {
        // Sesi browser dipakai ulang antar-test, jadi cookie sesi lama dihapus dulu.
        $browser->driver->manage()->deleteAllCookies();

        return $browser->visit('/login')
            ->type('login', $this->guru->nama_pengguna)
            ->type('kata_sandi', self::KATA_SANDI)
            ->press('Masuk')
            ->waitForLocation(self::URL_DASHBOARD);
    }

    /**
     * Langkah 1 semua TC: klik menu Observasi, lalu klik Tambah Observasi.
     */
    private function bukaFormulir(Browser $browser): Browser
    {
        return $browser
            ->waitFor('#sidebar a[href$="'.self::URL_INDEX.'"]')
            ->click('#sidebar a[href$="'.self::URL_INDEX.'"]')
            ->waitForLocation(self::URL_INDEX)
            ->assertSee('Data Observasi Perkembangan')
            ->click('a.obs-hero-btn')
            ->waitForLocation(self::URL_TAMBAH)
            ->waitForText('Formulir Observasi Perkembangan');
    }

    /**
     * Mengisi formulir dengan data valid (sama dengan dataObservasi() pada Feature test).
     * Isi 'kategori_respons' => null untuk mengosongkan respons.
     */
    private function isiFormulir(Browser $browser, array $tambahan = []): Browser
    {
        $data = array_merge([
            'kelas_id' => $this->kelasGuru->id,
            'anak_id' => $this->anak1->id,
            'tanggal' => '2026-11-14',
            'aktivitas_id' => $this->aktivitas->id,
            'aspek_id' => $this->aspek->id,
            'strategi_id' => $this->strategi->id,
            'kategori_respons' => KategoriRespons::TerlibatAktif->value,
            'catatan_pendukung' => 'Anak mengikuti kegiatan dengan baik.',
        ], $tambahan);

        $browser->select('kelas_id', $data['kelas_id'])
            ->select('anak_id', $data['anak_id']);

        $this->isiTanggal($browser, $data['tanggal']);

        $browser->select('aktivitas_id', $data['aktivitas_id'])
            ->select('aspek_id', $data['aspek_id'])
            ->select('strategi_id', $data['strategi_id']);

        if ($data['kategori_respons'] !== null) {
            $this->pilihRespons($browser, $data['kategori_respons']);
        }

        $browser->clear('catatan_pendukung');

        if ($data['catatan_pendukung'] !== '') {
            $browser->type('catatan_pendukung', $data['catatan_pendukung']);
        }

        return $browser;
    }

    /**
     * Input type="date" diisi lewat JavaScript agar tidak bergantung format lokal Chrome.
     */
    private function isiTanggal(Browser $browser, string $tanggal): void
    {
        $browser->script(
            "const el = document.getElementById('tanggal');"
            ."el.value = '{$tanggal}';"
            ."el.dispatchEvent(new Event('input', {bubbles: true}));"
            ."el.dispatchEvent(new Event('change', {bubbles: true}));"
        );

        $browser->assertInputValue('tanggal', $tanggal);
    }

    /**
     * Klik kartu respons (radio disembunyikan di dalam label).
     */
    private function pilihRespons(Browser $browser, string $nilai): void
    {
        $browser->click('label.obs-resp:has(input[value="'.$nilai.'"])');
    }

    private function jumlahResponsTerpilih(Browser $browser): int
    {
        return (int) $browser->script(
            "return document.querySelectorAll('input[name=\"kategori_respons\"]:checked').length;"
        )[0];
    }

    /**
     * Teks opsi dropdown tanpa placeholder (value kosong).
     *
     * @return array<int, string>
     */
    private function opsiTanpaPlaceholder(Browser $browser, string $id): array
    {
        return $browser->script(
            "return [...document.getElementById('{$id}').options]"
            .".filter(o => o.value !== '').map(o => o.text.trim());"
        )[0];
    }

    /**
     * Mensimulasikan perubahan value opsi terpilih lewat Inspect Element.
     */
    private function ubahNilaiOpsiTerpilih(Browser $browser, string $id, int $nilai): void
    {
        $browser->script(
            "const s = document.getElementById('{$id}');"
            ."s.options[s.selectedIndex].value = '{$nilai}';"
        );
    }
}
