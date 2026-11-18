<?php

namespace Tests\Browser;

use App\Enums\KategoriRespons;
use App\Models\Aktivitas;
use App\Models\Anak;
use App\Models\AspekPerkembangan;
use App\Models\Kelas;
use App\Models\Observasi;
use App\Models\StrategiPembelajaran;
use App\Models\User;
use Illuminate\Foundation\Testing\DatabaseTruncation;
use Laravel\Dusk\Browser;
use Tests\DuskTestCase;

// Fitur: PBI-11 Riwayat Observasi Anak dengan Filter
//
// Browser: tampilan dan interaksi filter di layar
// (TC-11-01, 02, 03, 05, 06, 07, 08, 09, 10, 11, 12).
// Logika, batas periode, hak akses, dan validasi ada di tests/Feature/Guru/RiwayatObservasiTest.php.
class RiwayatObservasiTest extends DuskTestCase
{
    // Truncation: migrasi sekali di awal, lalu tabel dikosongkan tiap test (lebih cepat dari migrate:fresh).
    use DatabaseTruncation;

    /** Jeda (ms) antar langkah saat ditonton dengan `php artisan dusk --browse`. */
    private const JEDA = 1000;

    /** Kata sandi dummy akun guru uji. */
    private const KATA_SANDI = 'GuruMia#2026';

    private User $guruA;

    private Anak $anak1;

    private Anak $anak2;

    /** @var array<string, AspekPerkembangan> */
    private array $aspek = [];

    /** @var array<string, Aktivitas> */
    private array $aktivitas = [];

    /** @var array<string, StrategiPembelajaran> */
    private array $strategi = [];

    protected function setUp(): void
    {
        parent::setUp();

        $this->guruA = User::factory()->create([
            'nama' => 'Mia Stacia',
            'nama_pengguna' => 'miastacia',
            'kata_sandi' => self::KATA_SANDI,
        ]);

        $kelasA = Kelas::factory()->create(['guru_id' => $this->guruA->id, 'nama_kelas' => 'Melati', 'kelompok' => 'TK A']);

        foreach (['Kognitif', 'Bahasa', 'Fisik Motorik', 'Sosial Emosional', 'Seni'] as $nama) {
            $this->aspek[$nama] = AspekPerkembangan::factory()->create(['nama' => $nama]);
        }

        foreach (['Menyusun balok', 'Bernyanyi bersama', 'Menempel kertas', 'Mewarnai gambar', 'Bermain peran'] as $nama) {
            $this->aktivitas[$nama] = Aktivitas::factory()->create(['nama' => $nama]);
        }

        foreach (['Media visual', 'Kelompok kecil', 'Permainan gerak'] as $nama) {
            $this->strategi[$nama] = StrategiPembelajaran::factory()->create(['nama' => $nama]);
        }

        $this->anak1 = Anak::factory()->create(['kelas_id' => $kelasA->id, 'nama' => 'Dedek Tirta', 'nama_panggilan' => 'Tirta']);
        $this->anak2 = Anak::factory()->create(['kelas_id' => $kelasA->id, 'nama_panggilan' => 'Waluyo']);

        // 14 observasi Tirta: catatan ke-01 paling lama, ke-14 paling baru.
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

    public function test_tc_11_01_riwayat_tampil_kronologis_dan_penghitung_14(): void
    {
        $this->browse(function (Browser $browser) {
            $this->bukaRiwayat($browser)
                ->assertSeeIn('#penghitung-atas', '14 catatan')
                ->assertMissing('#muat-lebih-banyak');

            $this->assertSame([
                'Catatan ke-14', 'Catatan ke-13', 'Catatan ke-12', 'Catatan ke-11',
                'Catatan ke-10', 'Catatan ke-09', 'Catatan ke-08', 'Catatan ke-07',
                'Catatan ke-06', 'Catatan ke-05', 'Catatan ke-04', 'Catatan ke-03',
                'Catatan ke-02', 'Catatan ke-01',
            ], $this->catatanTampil($browser));

            $this->jeda($browser);
        });
    }

    public function test_tc_11_02_kartu_observasi_memuat_data_lengkap(): void
    {
        $this->browse(function (Browser $browser) {
            $this->bukaRiwayat($browser)
                // Judul tanggal ditampilkan kapital oleh CSS.
                ->assertSeeIn('#daftar-observasi', 'KAMIS, 15 OKT 2026')
                ->within('.obs-card', function (Browser $kartu) {
                    $kartu->assertSee('Menyusun balok')
                        ->assertSee('Aspek: Kognitif')
                        ->assertSee('Strategi: Kelompok kecil')
                        ->assertSee('Kurang tertarik')
                        ->assertSee('Catatan ke-14');
                });

            $this->jeda($browser);
        });
    }

    public function test_tc_11_03_filter_periode_bulan(): void
    {
        $this->browse(function (Browser $browser) {
            $this->bukaRiwayat($browser);
            $this->pilihFilter($browser, 'periode', '2026-10')
                ->assertSeeIn('#penghitung-atas', '12 dari 14 catatan')
                ->assertSee('Catatan ke-03')
                ->assertDontSee('Catatan ke-02');

            $this->assertCount(12, $this->catatanTampil($browser));
        });
    }

    public function test_tc_11_05_filter_aspek(): void
    {
        $this->browse(function (Browser $browser) {
            $this->bukaRiwayat($browser);
            $this->pilihFilter($browser, 'aspek', (string) $this->aspek['Sosial Emosional']->id)
                ->assertSeeIn('#penghitung-atas', '2 dari 14 catatan');

            $this->assertSame(['Catatan ke-11', 'Catatan ke-07'], $this->catatanTampil($browser));
        });
    }

    public function test_tc_11_06_filter_aktivitas(): void
    {
        $this->browse(function (Browser $browser) {
            $this->bukaRiwayat($browser);
            $this->pilihFilter($browser, 'aktivitas', (string) $this->aktivitas['Menyusun balok']->id)
                ->assertSeeIn('#penghitung-atas', '5 dari 14 catatan');
        });
    }

    public function test_tc_11_07_filter_strategi(): void
    {
        $this->browse(function (Browser $browser) {
            $this->bukaRiwayat($browser);
            $this->pilihFilter($browser, 'strategi', (string) $this->strategi['Media visual']->id)
                ->assertSeeIn('#penghitung-atas', '6 dari 14 catatan');
        });
    }

    public function test_tc_11_08_filter_kategori_respons(): void
    {
        $this->browse(function (Browser $browser) {
            $this->bukaRiwayat($browser);
            $this->pilihFilter($browser, 'respons', KategoriRespons::KurangTertarik->value)
                ->assertSeeIn('#penghitung-atas', '6 dari 14 catatan');
        });
    }

    public function test_tc_11_09_kombinasi_filter_aspek_dan_respons(): void
    {
        $this->browse(function (Browser $browser) {
            $this->bukaRiwayat($browser);
            $this->pilihFilter($browser, 'aspek', (string) $this->aspek['Kognitif']->id);
            $this->pilihFilter($browser, 'respons', KategoriRespons::KurangTertarik->value)
                ->assertSelected('aspek', (string) $this->aspek['Kognitif']->id)
                ->assertSeeIn('#penghitung-atas', '5 dari 14 catatan')
                ->assertSee('Catatan ke-14')
                ->assertDontSee('Catatan ke-12');
        });
    }

    public function test_tc_11_10_hapus_filter_mengembalikan_seluruh_catatan(): void
    {
        $this->browse(function (Browser $browser) {
            $this->bukaRiwayat($browser);
            $this->pilihFilter($browser, 'aspek', (string) $this->aspek['Kognitif']->id)
                ->assertSeeIn('#penghitung-atas', '5 dari 14 catatan');

            $browser->waitForReload(fn (Browser $b) => $b->clickLink('Hapus filter'))
                ->assertSeeIn('#penghitung-atas', '14 catatan')
                ->assertDontSeeIn('#penghitung-atas', 'dari')
                ->assertSelected('aspek', '');

            $this->jeda($browser);
        });
    }

    public function test_tc_11_11_filter_tanpa_hasil_tampil_pesan_kosong(): void
    {
        $this->browse(function (Browser $browser) {
            $this->bukaRiwayat($browser);
            $this->pilihFilter($browser, 'aspek', (string) $this->aspek['Seni']->id)
                ->assertSeeIn('#penghitung-atas', '0 dari 14 catatan')
                ->assertSee('Belum ada observasi yang sesuai')
                ->assertMissing('#daftar-observasi');

            $this->jeda($browser);
        });
    }

    public function test_tc_11_12_anak_belum_punya_observasi(): void
    {
        $this->browse(function (Browser $browser) {
            // Waluyo belum punya observasi, jadi tidak muncul di tabel Observasi.
            // Halaman riwayatnya dibuka lewat alamat langsung setelah login.
            $this->masuk($browser)
                ->visit(route('guru.riwayat-observasi', $this->anak2->id))
                ->waitForText('Belum ada observasi')
                ->assertSee('Waluyo')
                ->assertSeeIn('#penghitung-atas', '0 catatan');

            $this->jeda($browser);
        });
    }

    public function test_muat_lebih_banyak_muncul_jika_lebih_dari_20_catatan_lalu_hilang_setelah_semua_tampil(): void
    {
        // 14 catatan bawaan + 11 catatan November = 25 catatan.
        foreach (range(1, 11) as $hari) {
            $this->buatObservasi($this->anak1, sprintf('2026-11-%02d', $hari), 'Bahasa', 'Bernyanyi bersama', 'Kelompok kecil', KategoriRespons::TerlibatAktif, 'Catatan November '.$hari);
        }

        $this->browse(function (Browser $browser) {
            $this->bukaRiwayat($browser)
                ->assertSeeIn('#penghitung-atas', '25 catatan')
                ->assertVisible('#muat-lebih-banyak')
                ->assertSeeIn('#penghitung-bawah', 'Menampilkan 20 dari 25 catatan');

            $this->assertCount(20, $this->catatanTampil($browser));

            $browser->click('#muat-lebih-banyak')
                ->waitUntilMissing('#muat-lebih-banyak')
                ->assertSeeIn('#penghitung-bawah', 'Menampilkan 25 dari 25 catatan');

            $this->assertCount(25, $this->catatanTampil($browser));

            $this->jeda($browser);
        });
    }

    /**
     * Login lalu buka Riwayat Observasi Tirta lewat URL halaman PBI-11.
     * Sengaja tidak lewat halaman PBI lain (Kelola Observasi/Data Anak) agar test ini
     * tidak bergantung pada tampilan fitur milik PBI lain.
     */
    private function bukaRiwayat(Browser $browser): Browser
    {
        $this->masuk($browser)
            ->visit(route('guru.riwayat-observasi', $this->anak1->id))
            ->waitForLocation(route('guru.riwayat-observasi', $this->anak1->id, false))
            ->waitFor('#penghitung-atas')
            ->assertSee('Riwayat Observasi')
            ->assertSee('Dedek Tirta');

        return $this->jeda($browser);
    }

    /**
     * Login lewat halaman login: ketik nama pengguna dan kata sandi, lalu klik Masuk.
     */
    private function masuk(Browser $browser): Browser
    {
        // Pastikan mulai dari keadaan belum login (browser dipakai ulang antar test).
        $browser->logout()
            ->visit('/login')
            ->waitForText('Masuk ke SIPANDAI');

        $this->jeda($browser);

        $browser->type('login', 'miastacia')
            ->type('kata_sandi', self::KATA_SANDI);

        $this->jeda($browser);

        $browser->press('Masuk')
            ->waitForLocation('/guru/dashboard')
            ->assertSee('Selamat datang, Mia Stacia');

        return $this->jeda($browser);
    }

    /**
     * Pilih nilai dropdown filter; halaman dimuat ulang otomatis lewat onchange.
     */
    private function pilihFilter(Browser $browser, string $nama, string $nilai): Browser
    {
        $browser->waitForReload(fn (Browser $b) => $b->select($nama, $nilai))
            ->waitFor('#penghitung-atas');

        return $this->jeda($browser);
    }

    /**
     * Jeda hanya saat browser ditonton (--browse); mode headless tetap cepat.
     */
    private function jeda(Browser $browser): Browser
    {
        return $this->hasHeadlessDisabled() ? $browser->pause(self::JEDA) : $browser;
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
                .map((kartu) => (kartu.innerText.match(/Catatan ke-\d+/) || [''])[0]);
        JS)[0];
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
