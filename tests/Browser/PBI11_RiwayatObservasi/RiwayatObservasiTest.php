<?php

namespace Tests\Browser\PBI11_RiwayatObservasi;

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

/**
 * PBI-11 Riwayat Observasi Anak dengan Filter (FR-09, NFR-02, NFR-05, NFR-06).
 *
 * Browser: TC-11-01, 02, 03, 05, 06, 07, 08, 09, 10, 11, 12, 18, 19, 20.
 * Logika server dan hak akses (TC-11-04, 13, 14, 15, 16, 17) ada di
 * tests/Feature/Guru/RiwayatObservasiTest.php.
 *
 * Alur mengikuti Jira: guru login, buka Data Anak, pilih anak, lalu klik "Riwayat Observasi".
 *
 * Tanpa DatabaseMigrations/DatabaseTruncation dan tanpa seeder: prasyarat dibuat lewat
 * factory dengan kode unik, jadi setiap run menambah data baru di database sipandai_dusk
 * dan data run sebelumnya tetap tersimpan. Jeda antar langkah diatur DUSK_SLOW_MS.
 *
 * Data uji (Jira): Guru A mengampu TK A - Melati. Anak 1 (Dedek) punya 14 observasi:
 * 4 terlibat aktif, 3 terlibat dengan pendampingan, 6 kurang tertarik, 1 belum terlibat;
 * 12 di Oktober 2026 dan 2 di September 2026. Anak baru (Raka) belum punya observasi.
 */
class RiwayatObservasiTest extends DuskTestCase
{
    use DataUnik;

    /** Kata sandi dummy akun guru uji. */
    private const KATA_SANDI = 'GuruMia#2026';

    private User $guruA;

    private Anak $anak1;

    private Anak $anakBaru;

    /** @var array<string, AspekPerkembangan> nama dasar => model */
    private array $aspek = [];

    /** @var array<string, Aktivitas> nama dasar => model */
    private array $aktivitas = [];

    /** @var array<string, StrategiPembelajaran> nama dasar => model */
    private array $strategi = [];

    protected function setUp(): void
    {
        parent::setUp();

        $kode = $this->kodeUnik();

        $this->guruA = User::factory()->create([
            'nama' => "Mia Stacia {$kode}",
            'nama_pengguna' => "mia{$kode}",
            'email' => "mia.{$kode}@tkceria.sch.id",
            'kata_sandi' => self::KATA_SANDI,
        ]);

        $kelasA = Kelas::factory()->create([
            'guru_id' => $this->guruA->id,
            'kelompok' => 'TK A',
            'nama_kelas' => "Melati {$kode}",
        ]);

        // Data master diberi kode unik agar mudah dibedakan di dropdown filter,
        // karena database Dusk menyimpan data dari run sebelumnya.
        foreach (['Kognitif', 'Bahasa', 'Fisik Motorik', 'Sosial Emosional', 'Seni'] as $nama) {
            $this->aspek[$nama] = AspekPerkembangan::factory()->create(['nama' => "{$nama} {$kode}"]);
        }

        foreach (['Menyusun balok', 'Bernyanyi bersama', 'Menempel kertas', 'Mewarnai gambar', 'Bermain peran'] as $nama) {
            $this->aktivitas[$nama] = Aktivitas::factory()->create(['nama' => "{$nama} {$kode}"]);
        }

        foreach (['Media visual', 'Kelompok kecil', 'Permainan gerak'] as $nama) {
            $this->strategi[$nama] = StrategiPembelajaran::factory()->create(['nama' => "{$nama} {$kode}"]);
        }

        $this->anak1 = Anak::factory()->create([
            'kelas_id' => $kelasA->id,
            'nama' => "Dedek Tirta {$kode}",
            'nama_panggilan' => 'Dedek',
        ]);
        $this->anakBaru = Anak::factory()->create([
            'kelas_id' => $kelasA->id,
            'nama' => "Raka Aditya {$kode}",
            'nama_panggilan' => 'Raka',
        ]);

        // 14 observasi Dedek: catatan ke-01 paling lama, ke-14 paling baru.
        $baris = [
            ['2026-09-22', 'Fisik Motorik', 'Bernyanyi bersama', 'Kelompok kecil', KategoriRespons::TerlibatAktif],
            ['2026-09-28', 'Kognitif', 'Menyusun balok', 'Kelompok kecil', KategoriRespons::KurangTertarik],
            ['2026-10-01', 'Fisik Motorik', 'Menempel kertas', 'Media visual', KategoriRespons::BelumTerlibat],
            ['2026-10-01', 'Kognitif', 'Menyusun balok', 'Kelompok kecil', KategoriRespons::KurangTertarik],
            ['2026-10-01', 'Bahasa', 'Menempel kertas', 'Media visual', KategoriRespons::TerlibatDenganPendampingan],
            ['2026-10-03', 'Kognitif', 'Menyusun balok', 'Media visual', KategoriRespons::KurangTertarik],
            ['2026-10-03', 'Sosial Emosional', 'Bermain peran', 'Kelompok kecil', KategoriRespons::TerlibatAktif],
            ['2026-10-05', 'Fisik Motorik', 'Mewarnai gambar', 'Media visual', KategoriRespons::TerlibatDenganPendampingan],
            ['2026-10-07', 'Bahasa', 'Bernyanyi bersama', 'Kelompok kecil', KategoriRespons::TerlibatAktif],
            ['2026-10-09', 'Kognitif', 'Menyusun balok', 'Media visual', KategoriRespons::KurangTertarik],
            ['2026-10-09', 'Sosial Emosional', 'Bermain peran', 'Kelompok kecil', KategoriRespons::KurangTertarik],
            ['2026-10-13', 'Fisik Motorik', 'Bernyanyi bersama', 'Permainan gerak', KategoriRespons::TerlibatAktif],
            ['2026-10-13', 'Bahasa', 'Menempel kertas', 'Media visual', KategoriRespons::TerlibatDenganPendampingan],
            ['2026-10-15', 'Kognitif', 'Menyusun balok', 'Kelompok kecil', KategoriRespons::KurangTertarik],
        ];

        foreach ($baris as $i => [$tanggal, $aspek, $aktivitas, $strategi, $respons]) {
            $this->buatObservasi($this->anak1, $tanggal, $aspek, $aktivitas, $strategi, $respons, 'Catatan ke-'.str_pad((string) ($i + 1), 2, '0', STR_PAD_LEFT));
        }
    }

    /**
     * TC-11-01: Guru A login, buka Data Anak, pilih Anak 1, lalu klik "Riwayat Observasi".
     */
    public function test_tc_11_01_riwayat_tampil_kronologis_dan_penghitung_14(): void
    {
        $this->browse(function (Browser $browser) {
            $this->masuk($browser);
            $this->bukaRiwayatLewatDataAnak($browser, $this->anak1)
                ->assertSeeIn('#penghitung-atas', '14 catatan')
                ->assertMissing('#muat-lebih-banyak');

            $this->assertSame([
                'Catatan ke-14', 'Catatan ke-13', 'Catatan ke-12', 'Catatan ke-11',
                'Catatan ke-10', 'Catatan ke-09', 'Catatan ke-08', 'Catatan ke-07',
                'Catatan ke-06', 'Catatan ke-05', 'Catatan ke-04', 'Catatan ke-03',
                'Catatan ke-02', 'Catatan ke-01',
            ], $this->catatanTampil($browser));
        });
    }

    /**
     * TC-11-02: setiap catatan memuat tanggal, aktivitas, aspek, strategi, respons, dan catatan guru.
     */
    public function test_tc_11_02_kartu_observasi_memuat_data_lengkap(): void
    {
        $this->browse(function (Browser $browser) {
            $this->bukaRiwayatAnak1($browser)
                // Judul tanggal ditampilkan kapital oleh CSS.
                ->assertSeeIn('#daftar-observasi', 'KAMIS, 15 OKT 2026')
                ->within('.obs-card', function (Browser $kartu) {
                    $kartu->assertSee($this->aktivitas['Menyusun balok']->nama)
                        ->assertSee('Aspek: '.$this->aspek['Kognitif']->nama)
                        ->assertSee('Strategi: '.$this->strategi['Kelompok kecil']->nama)
                        ->assertSee('Kurang tertarik')
                        ->assertSee('Catatan ke-14');
                });
        });
    }

    /**
     * TC-11-03: periode Oktober 2026 (1-31 Okt) hanya menampilkan 12 catatan Oktober.
     */
    public function test_tc_11_03_filter_periode_bulan(): void
    {
        $this->browse(function (Browser $browser) {
            $this->bukaRiwayatAnak1($browser);
            $this->pilihFilter($browser, 'periode', '2026-10')
                ->assertSeeIn('#penghitung-atas', '12 dari 14 catatan')
                ->assertSee('Catatan ke-03')
                ->assertDontSee('Catatan ke-02')
                ->assertDontSee('Catatan ke-01');

            $this->assertCount(12, $this->catatanTampil($browser));
        });
    }

    public function test_tc_11_05_filter_aspek(): void
    {
        $this->browse(function (Browser $browser) {
            $this->bukaRiwayatAnak1($browser);
            $this->pilihFilter($browser, 'aspek', (string) $this->aspek['Sosial Emosional']->id)
                ->assertSeeIn('#penghitung-atas', '2 dari 14 catatan');

            $this->assertSame(['Catatan ke-11', 'Catatan ke-07'], $this->catatanTampil($browser));
            $this->assertSemuaKartuMemuat($browser, 'Aspek: '.$this->aspek['Sosial Emosional']->nama, 2);
        });
    }

    public function test_tc_11_06_filter_aktivitas(): void
    {
        $this->browse(function (Browser $browser) {
            $this->bukaRiwayatAnak1($browser);
            $this->pilihFilter($browser, 'aktivitas', (string) $this->aktivitas['Menyusun balok']->id)
                ->assertSeeIn('#penghitung-atas', '5 dari 14 catatan');

            $this->assertSemuaKartuMemuat($browser, $this->aktivitas['Menyusun balok']->nama, 5);
        });
    }

    public function test_tc_11_07_filter_strategi(): void
    {
        $this->browse(function (Browser $browser) {
            $this->bukaRiwayatAnak1($browser);
            $this->pilihFilter($browser, 'strategi', (string) $this->strategi['Media visual']->id)
                ->assertSeeIn('#penghitung-atas', '6 dari 14 catatan');

            $this->assertSemuaKartuMemuat($browser, 'Strategi: '.$this->strategi['Media visual']->nama, 6);
        });
    }

    public function test_tc_11_08_filter_kategori_respons(): void
    {
        $this->browse(function (Browser $browser) {
            $this->bukaRiwayatAnak1($browser);
            $this->pilihFilter($browser, 'respons', KategoriRespons::KurangTertarik->value)
                ->assertSeeIn('#penghitung-atas', '6 dari 14 catatan');

            $this->assertSemuaKartuMemuat($browser, KategoriRespons::KurangTertarik->label(), 6);
        });
    }

    /**
     * TC-11-09: aspek Kognitif DAN respons Kurang tertarik.
     */
    public function test_tc_11_09_kombinasi_filter_aspek_dan_respons(): void
    {
        $this->browse(function (Browser $browser) {
            $this->bukaRiwayatAnak1($browser);
            $this->pilihFilter($browser, 'aspek', (string) $this->aspek['Kognitif']->id);
            $this->pilihFilter($browser, 'respons', KategoriRespons::KurangTertarik->value)
                ->assertSelected('aspek', (string) $this->aspek['Kognitif']->id)
                ->assertSelected('respons', KategoriRespons::KurangTertarik->value)
                ->assertSeeIn('#penghitung-atas', '5 dari 14 catatan');

            $this->assertSemuaKartuMemuat($browser, 'Aspek: '.$this->aspek['Kognitif']->nama, 5);
            $this->assertSemuaKartuMemuat($browser, KategoriRespons::KurangTertarik->label(), 5);
        });
    }

    /**
     * TC-11-10: setelah kombinasi filter TC-11-09, klik "Hapus filter".
     */
    public function test_tc_11_10_hapus_filter_mengembalikan_seluruh_catatan(): void
    {
        $this->browse(function (Browser $browser) {
            $this->bukaRiwayatAnak1($browser);
            $this->pilihFilter($browser, 'aspek', (string) $this->aspek['Kognitif']->id);
            $this->pilihFilter($browser, 'respons', KategoriRespons::KurangTertarik->value)
                ->assertSeeIn('#penghitung-atas', '5 dari 14 catatan');

            $this->hapusFilter($browser)
                ->assertSeeIn('#penghitung-atas', '14 catatan')
                ->assertDontSeeIn('#penghitung-atas', 'dari')
                ->assertSelected('periode', 'semua')
                ->assertSelected('aspek', '')
                ->assertSelected('aktivitas', '')
                ->assertSelected('strategi', '')
                ->assertSelected('respons', '');

            $this->assertCount(14, $this->catatanTampil($browser));
        });
    }

    /**
     * TC-11-11: kombinasi filter yang tidak cocok dengan catatan mana pun.
     */
    public function test_tc_11_11_filter_tanpa_hasil_tampil_pesan_kosong(): void
    {
        $this->browse(function (Browser $browser) {
            $this->bukaRiwayatAnak1($browser);
            // Sosial Emosional hanya punya respons "terlibat aktif" dan "kurang tertarik".
            $this->pilihFilter($browser, 'aspek', (string) $this->aspek['Sosial Emosional']->id);
            $this->pilihFilter($browser, 'respons', KategoriRespons::BelumTerlibat->value)
                ->assertSeeIn('#penghitung-atas', '0 dari 14 catatan')
                ->assertSee('Belum ada observasi yang sesuai')
                ->assertSeeLink('Hapus filter')
                ->assertMissing('#daftar-observasi');
        });
    }

    /**
     * TC-11-12: anak baru di TK A belum punya observasi, dibuka lewat Data Anak.
     */
    public function test_tc_11_12_anak_belum_punya_observasi(): void
    {
        $this->browse(function (Browser $browser) {
            $this->masuk($browser);
            $this->bukaRiwayatLewatDataAnak($browser, $this->anakBaru)
                ->assertSee('Belum ada observasi')
                ->assertSee($this->anakBaru->nama)
                ->assertSeeIn('#penghitung-atas', '0 catatan')
                ->assertMissing('#daftar-observasi');
        });
    }

    /**
     * TC-11-18: lebar 360 px, 768 px, dan 1920 px. Filter tidak terpotong dan tidak ada
     * scroll horizontal (NFR-05). Lebar layar diemulasikan lewat Chrome DevTools karena
     * jendela Chrome tidak bisa dikecilkan sampai 360 px.
     */
    public function test_tc_11_18_tampilan_responsif(): void
    {
        $this->browse(function (Browser $browser) {
            $this->bukaRiwayatAnak1($browser);

            try {
                foreach ([360 => 800, 768 => 1024, 1920 => 1080] as $lebar => $tinggi) {
                    $this->cdp($browser, 'Emulation.setDeviceMetricsOverride', [
                        'width' => $lebar,
                        'height' => $tinggi,
                        'deviceScaleFactor' => 1,
                        'mobile' => $lebar < 768,
                    ]);
                    $browser->refresh()->waitFor('#penghitung-atas');

                    // lebarLayar tanpa scrollbar vertikal (clientWidth); lebarJendela = lebar yang diemulasikan.
                    $ukuran = $browser->script(<<<'JS'
                        const lebarLayar = document.documentElement.clientWidth;
                        const lebarJendela = window.innerWidth;
                        const filter = ['periode', 'aspek', 'aktivitas', 'strategi', 'respons']
                            .map((nama) => document.getElementById('filter-' + nama).getBoundingClientRect());
                        return {
                            lebarLayar,
                            lebarJendela,
                            lebarHalaman: document.documentElement.scrollWidth,
                            filterTerpotong: filter.filter((r) => r.width === 0 || r.left < 0 || r.right > lebarLayar).length,
                            filterSatuBaris: filter.every((r) => Math.abs(r.top - filter[0].top) < 2),
                        };
                    JS)[0];

                    $this->assertSame($lebar, $ukuran['lebarJendela'], "Lebar layar {$lebar} px tidak terpasang.");
                    $this->assertLessThanOrEqual($ukuran['lebarLayar'], $ukuran['lebarHalaman'], "Ada scroll horizontal pada {$lebar} px.");
                    $this->assertSame(0, $ukuran['filterTerpotong'], "Ada filter yang terpotong pada {$lebar} px.");
                    // Tata letak menyesuaikan: di HP filter bertumpuk, di desktop satu baris.
                    if ($lebar === 360) {
                        $this->assertFalse($ukuran['filterSatuBaris'], 'Filter pada 360 px seharusnya bertumpuk.');
                    }
                    if ($lebar === 1920) {
                        $this->assertTrue($ukuran['filterSatuBaris'], 'Filter pada 1920 px seharusnya satu baris.');
                    }

                    $browser->assertVisible('#penghitung-atas')
                        ->assertSeeLink('Hapus filter');
                }
            } finally {
                $this->cdp($browser, 'Emulation.clearDeviceMetricsOverride');
            }
        });
    }

    /**
     * TC-11-19: Anak 1 punya 100 observasi, halaman dibuka pada koneksi 4G (diemulasikan
     * lewat Chrome DevTools: 4 Mbps unduh, 3 Mbps unggah, latensi 20 ms) dan termuat
     * paling lama 3 detik (NFR-02). Waktu proses server tidak ikut dihitung (lihat catatan di bawah).
     */
    public function test_tc_11_19_waktu_muat_100_observasi_maksimal_3_detik(): void
    {
        // 14 catatan bawaan + 86 catatan November = 100 catatan.
        foreach (range(1, 86) as $i) {
            $this->buatObservasi($this->anak1, sprintf('2026-11-%02d', ($i % 17) + 1), 'Bahasa', 'Bernyanyi bersama', 'Kelompok kecil', KategoriRespons::TerlibatAktif, 'Catatan tambahan '.$i);
        }

        $this->browse(function (Browser $browser) {
            $this->masuk($browser);
            $this->bukaProfilLewatDataAnak($browser, $this->anak1);

            try {
                $this->cdp($browser, 'Network.enable');
                $this->cdp($browser, 'Network.emulateNetworkConditions', [
                    'offline' => false,
                    'latency' => 20,
                    'downloadThroughput' => (int) (4 * 1024 * 1024 / 8),
                    'uploadThroughput' => (int) (3 * 1024 * 1024 / 8),
                ]);

                $browser->clickLink('Riwayat Observasi')
                    ->waitForLocation(route('guru.riwayat-observasi', $this->anak1->id, false))
                    ->waitUntil('document.readyState === "complete"')
                    ->assertSeeIn('#penghitung-atas', '100 catatan');

                $ukur = $browser->script(<<<'JS'
                    const n = performance.getEntriesByType('navigation')[0];
                    return {
                        total: Math.round(n.loadEventEnd - n.startTime),
                        server: Math.round(n.responseStart - n.requestStart),
                        html: Math.round(n.responseEnd - n.responseStart),
                        htmlKb: Math.round(n.transferSize / 1024),
                        sumber: performance.getEntriesByType('resource').map((r) =>
                            Math.round(r.duration) + ' ms, ' + Math.round(r.transferSize / 1024) + ' KB, ' + r.name),
                    };
                JS)[0];

                // Yang diukur: waktu muat di sisi pengguna pada koneksi 4G (unduh HTML, CSS, JS,
                // lalu tampil), tanpa waktu proses server. Server Docker di laptop Windows lambat
                // untuk semua halaman (rute /up yang kosong pun sekitar 3 detik), jadi waktu server
                // tidak mencerminkan server produksi. Waktu server tetap ditampilkan di rincian.
                $waktuMuat = $ukur['total'] - $ukur['server'];
                $rincian = "total {$ukur['total']} ms, server {$ukur['server']} ms, unduh HTML {$ukur['html']} ms ({$ukur['htmlKb']} KB); "
                    .'sumber lain: '.implode(' | ', $ukur['sumber']);

                $this->assertGreaterThan(0, $waktuMuat);
                $this->assertLessThanOrEqual(3000, $waktuMuat, "Halaman termuat dalam {$waktuMuat} ms di koneksi 4G, lebih dari 3 detik. Rincian: {$rincian}");
            } finally {
                $this->cdp($browser, 'Network.emulateNetworkConditions', [
                    'offline' => false,
                    'latency' => 0,
                    'downloadThroughput' => -1,
                    'uploadThroughput' => -1,
                ]);
            }
        });
    }

    /**
     * TC-11-20: login Guru A, buka profil Anak 1, klik "Riwayat Observasi", pilih periode
     * dan respons, lalu "Hapus filter" mengembalikan daftar penuh.
     */
    public function test_tc_11_20_alur_utama_di_browser(): void
    {
        $this->browse(function (Browser $browser) {
            $this->masuk($browser);
            $this->bukaRiwayatLewatDataAnak($browser, $this->anak1)
                ->assertSeeIn('#penghitung-atas', '14 catatan');

            $this->pilihFilter($browser, 'periode', '2026-10')
                ->assertSeeIn('#penghitung-atas', '12 dari 14 catatan');

            // Oktober + kurang tertarik: catatan ke-04, 06, 10, 11, 14.
            $this->pilihFilter($browser, 'respons', KategoriRespons::KurangTertarik->value)
                ->assertSelected('periode', '2026-10')
                ->assertSeeIn('#penghitung-atas', '5 dari 14 catatan');
            $this->assertSame(
                ['Catatan ke-14', 'Catatan ke-11', 'Catatan ke-10', 'Catatan ke-06', 'Catatan ke-04'],
                $this->catatanTampil($browser)
            );

            $this->hapusFilter($browser)
                ->assertSeeIn('#penghitung-atas', '14 catatan')
                ->assertDontSeeIn('#penghitung-atas', 'dari');
            $this->assertCount(14, $this->catatanTampil($browser));
        });
    }

    /**
     * Tambahan: tombol "Muat lebih banyak" muncul jika lebih dari 20 catatan.
     */
    public function test_muat_lebih_banyak_muncul_jika_lebih_dari_20_catatan_lalu_hilang_setelah_semua_tampil(): void
    {
        // 14 catatan bawaan + 11 catatan November = 25 catatan.
        foreach (range(1, 11) as $hari) {
            $this->buatObservasi($this->anak1, sprintf('2026-11-%02d', $hari), 'Bahasa', 'Bernyanyi bersama', 'Kelompok kecil', KategoriRespons::TerlibatAktif, 'Catatan November '.$hari);
        }

        $this->browse(function (Browser $browser) {
            $this->bukaRiwayatAnak1($browser)
                ->assertSeeIn('#penghitung-atas', '25 catatan')
                ->assertVisible('#muat-lebih-banyak')
                ->assertSeeIn('#penghitung-bawah', 'Menampilkan 20 dari 25 catatan');

            $this->assertCount(20, $this->catatanTampil($browser));

            $browser->click('#muat-lebih-banyak')
                ->waitUntilMissing('#muat-lebih-banyak')
                ->assertSeeIn('#penghitung-bawah', 'Menampilkan 25 dari 25 catatan');

            $this->assertCount(25, $this->catatanTampil($browser));
        });
    }

    /**
     * Login lalu buka Riwayat Observasi Anak 1 lewat Data Anak (alur Jira).
     */
    private function bukaRiwayatAnak1(Browser $browser): Browser
    {
        $this->masuk($browser);

        return $this->bukaRiwayatLewatDataAnak($browser, $this->anak1);
    }

    /**
     * Login lewat halaman login: ketik nama pengguna dan kata sandi, lalu klik Masuk.
     * Sistem menampilkan dashboard guru.
     */
    private function masuk(Browser $browser): Browser
    {
        // Pastikan mulai dari keadaan belum login (browser dipakai ulang antar test).
        return $browser->logout()
            ->visit('/login')
            ->waitForText('Masuk ke SIPANDAI')
            ->type('login', $this->guruA->nama_pengguna)
            ->type('kata_sandi', self::KATA_SANDI)
            ->press('Masuk')
            ->waitForLocation('/guru/dashboard')
            ->assertSee('Selamat datang, '.$this->guruA->nama);
    }

    /**
     * Menu Data Anak di sidebar, lalu klik anak untuk membuka profilnya.
     */
    private function bukaProfilLewatDataAnak(Browser $browser, Anak $anak): Browser
    {
        return $browser->click('@sidebar a[href$="/guru/anak"]')
            ->waitForLocation('/guru/anak')
            ->waitFor('@daftar-anak')
            ->assertSeeIn('@daftar-anak', $anak->nama_panggilan)
            ->click('@daftar-anak a[href$="/guru/anak/'.$anak->id.'"]')
            ->waitForLocation('/guru/anak/'.$anak->id)
            ->waitForText($anak->nama)
            ->assertSeeLink('Riwayat Observasi');
    }

    /**
     * Data Anak, pilih anak, lalu klik tombol "Riwayat Observasi" di profil anak.
     */
    private function bukaRiwayatLewatDataAnak(Browser $browser, Anak $anak): Browser
    {
        return $this->bukaProfilLewatDataAnak($browser, $anak)
            ->clickLink('Riwayat Observasi')
            ->waitForLocation(route('guru.riwayat-observasi', $anak->id, false))
            ->waitFor('#penghitung-atas')
            ->assertSee('Riwayat Observasi')
            ->assertSee($anak->nama);
    }

    /**
     * Pilih nilai dropdown filter; halaman dimuat ulang otomatis lewat onchange.
     */
    private function pilihFilter(Browser $browser, string $nama, string $nilai): Browser
    {
        return $browser->waitForReload(fn (Browser $b) => $b->select($nama, $nilai))
            ->waitFor('#penghitung-atas');
    }

    private function hapusFilter(Browser $browser): Browser
    {
        return $browser->waitForReload(fn (Browser $b) => $b->clickLink('Hapus filter'))
            ->waitFor('#penghitung-atas');
    }

    /**
     * Teks "Catatan ke-xx" dari kartu yang terlihat, urut dari atas.
     *
     * @return list<string>
     */
    private function catatanTampil(Browser $browser): array
    {
        return $browser->script(<<<'JS'
            return Array.from(document.querySelectorAll('.obs-card'))
                .filter((kartu) => kartu.offsetParent !== null)
                .map((kartu) => (kartu.textContent.match(/Catatan ke-\d+/) || [''])[0]);
        JS)[0];
    }

    /**
     * Memastikan hanya catatan yang sesuai filter yang tampil: jumlah kartu terlihat
     * sama dengan $jumlah dan setiap kartu memuat $teks.
     */
    private function assertSemuaKartuMemuat(Browser $browser, string $teks, int $jumlah): void
    {
        $isiKartu = $browser->script(<<<'JS'
            return Array.from(document.querySelectorAll('.obs-card'))
                .filter((kartu) => kartu.offsetParent !== null)
                .map((kartu) => kartu.textContent.replace(/\s+/g, ' '));
        JS)[0];

        $this->assertCount($jumlah, $isiKartu);

        foreach ($isiKartu as $isi) {
            $this->assertStringContainsStringIgnoringCase($teks, $isi);
        }
    }

    /**
     * Menjalankan perintah Chrome DevTools Protocol lewat Selenium.
     *
     * @param  array<string, mixed>  $parameter
     */
    private function cdp(Browser $browser, string $perintah, array $parameter = []): void
    {
        $browser->driver->executeCustomCommand('/session/:sessionId/goog/cdp/execute', 'POST', [
            'cmd' => $perintah,
            'params' => (object) $parameter,
        ]);
    }

    private function buatObservasi(Anak $anak, string $tanggal, string $aspek, string $aktivitas, string $strategi, KategoriRespons $respons, string $catatan): Observasi
    {
        return Observasi::factory()->create([
            'anak_id' => $anak->id,
            'aspek_id' => $this->aspek[$aspek]->id,
            'aktivitas_id' => $this->aktivitas[$aktivitas]->id,
            'strategi_id' => $this->strategi[$strategi]->id,
            'tanggal' => $tanggal,
            'kategori_respons' => $respons,
            'catatan_pendukung' => $catatan,
        ]);
    }
}
