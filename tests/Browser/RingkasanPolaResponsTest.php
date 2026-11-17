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
use Illuminate\Foundation\Testing\DatabaseMigrations;
use Illuminate\Support\Carbon;
use Laravel\Dusk\Browser;
use Tests\DuskTestCase;

// Fitur: PBI-12 Ringkasan Pola Respons Anak
class RingkasanPolaResponsTest extends DuskTestCase
{
    use DatabaseMigrations;

    private const REKAM_JEJAK = 'section[aria-labelledby="judul-rekam-jejak"]';

    private User $guru;

    private Anak $dedek;

    private Anak $anakBaru;

    private Carbon $awalPekan;

    protected function setUp(): void
    {
        parent::setUp();

        $this->guru = User::factory()->create(['nama' => 'Mia Stacia']);
        $kelas = Kelas::factory()->create(['guru_id' => $this->guru->id, 'nama_kelas' => 'Melati', 'kelompok' => 'TK A']);

        $this->dedek = Anak::factory()->create(['kelas_id' => $kelas->id, 'nama_panggilan' => 'Dedek']);
        $this->anakBaru = Anak::factory()->create(['kelas_id' => $kelas->id, 'nama_panggilan' => 'Rara']);

        $kognitif = AspekPerkembangan::factory()->create(['nama' => 'Kognitif']);
        $bahasa = AspekPerkembangan::factory()->create(['nama' => 'Bahasa']);
        $balok = Aktivitas::factory()->create(['nama' => 'Menyusun balok']);
        $bercerita = Aktivitas::factory()->create(['nama' => 'Bercerita']);
        $media = StrategiPembelajaran::factory()->create(['nama' => 'Media visual']);

        // Tanggal relatif terhadap hari ini supaya "Pekan ini" selalu berisi 2 catatan
        // (server Dusk memakai jam asli, tidak bisa travelTo).
        $this->awalPekan = Carbon::now()->startOfWeek(Carbon::MONDAY);

        $baris = [
            [$this->awalPekan->toDateString(), $kognitif, $balok, KategoriRespons::KurangTertarik],
            [$this->awalPekan->toDateString(), $bahasa, $bercerita, KategoriRespons::TerlibatAktif],
            [$this->awalPekan->copy()->subWeeks(2)->toDateString(), $bahasa, $bercerita, KategoriRespons::TerlibatDenganPendampingan],
            [$this->awalPekan->copy()->subWeeks(3)->toDateString(), $kognitif, $balok, KategoriRespons::KurangTertarik],
        ];

        foreach ($baris as [$tanggal, $aspek, $aktivitas, $respons]) {
            Observasi::factory()->create([
                'anak_id' => $this->dedek->id,
                'aspek_id' => $aspek->id,
                'aktivitas_id' => $aktivitas->id,
                'strategi_id' => $media->id,
                'tanggal' => $tanggal,
                'kategori_respons' => $respons,
            ]);
        }
    }

    /**
     * Buka daftar anak lalu pilih anak lewat tautan di halaman (alur seperti guru asli).
     */
    private function pilihAnak(Browser $browser, Anak $anak): Browser
    {
        return $browser->loginAs($this->guru)
            ->visitRoute('guru.pola-respons.index')
            ->tap(fn (Browser $b) => $this->visualPause($b))
            ->assertSee('Ringkasan Pola Respons Anak')
            ->assertSee($anak->nama_panggilan)
            ->click('a[href="'.route('guru.pola-respons.anak', $anak).'"]')
            ->waitForLocation('/guru/anak/'.$anak->id.'/pola-respons')
            ->assertSee('Ringkasan Pola Respons')
            ->tap(fn (Browser $b) => $this->visualPause($b));
    }

    /**
     * Gulir elemen ke tengah layar agar tidak tertutup header sticky.
     */
    private function gulirKeTengah(Browser $browser, string $selector): Browser
    {
        $browser->script('document.querySelector('.json_encode($selector).").scrollIntoView({block: 'center'});");

        return $this->visualPause($browser);
    }

    private function tanggalTampil(Carbon $tanggal): string
    {
        return $tanggal->copy()->locale('id')->translatedFormat('j M Y');
    }

    public function test_tc_12_01_membuka_halaman_ringkasan_pola_respons(): void
    {
        $this->browse(function (Browser $browser) {
            $this->pilihAnak($browser, $this->dedek)
                ->assertSeeIn('#jumlah-observasi', '4 observasi')
                // Grafik batang Chart.js selesai digambar.
                ->waitFor('#grafik-kategori-respons[data-siap="1"]')
                ->waitFor('#grafik-pola-kelompok[data-siap="1"]')
                ->assertAttributeContains('@grafik-kategori-respons', 'aria-label', 'Kurang tertarik 2')
                ->assertAttributeContains('@grafik-kategori-respons', 'aria-label', 'Terlibat aktif 1')
                ->assertSeeIn('[data-kategori="kurang_tertarik"]', '2')
                ->tap(fn (Browser $b) => $this->visualPause($b));

            // Rekam jejak kategori respons.
            $this->gulirKeTengah($browser, self::REKAM_JEJAK)
                ->assertSeeIn(self::REKAM_JEJAK, 'Rekam jejak observasi')
                ->assertSeeIn(self::REKAM_JEJAK, 'Kurang tertarik')
                ->assertSeeIn(self::REKAM_JEJAK, 'Terlibat aktif')
                ->assertSeeIn(self::REKAM_JEJAK, 'Terlibat dengan pendampingan');

            $this->assertCount(4, $browser->elements(self::REKAM_JEJAK.' li'));

            // Tab aktivitas mengganti isi grafik dan tabel.
            $this->gulirKeTengah($browser, '[dusk="tab-aktivitas"]')
                ->click('@tab-aktivitas')
                ->waitFor('#grafik-pola-kelompok[data-kelompok="aktivitas"]')
                ->tap(fn (Browser $b) => $this->visualPause($b))
                ->assertVisible('#tabel-aktivitas')
                ->assertMissing('#tabel-aspek');
        });
    }

    public function test_tc_12_02_filter_periode_pekan_ini(): void
    {
        $this->browse(function (Browser $browser) {
            $this->pilihAnak($browser, $this->dedek)
                ->waitFor('#grafik-kategori-respons[data-siap="1"]')
                ->assertSeeIn('#jumlah-observasi', '4 observasi')
                ->select('@filter-periode', 'pekan-ini')
                ->waitForTextIn('#jumlah-observasi', '2 dari 4 observasi')
                ->assertQueryStringHas('periode', 'pekan-ini')
                ->assertSelected('@filter-periode', 'pekan-ini')
                ->waitFor('#grafik-kategori-respons[data-siap="1"]')
                ->tap(fn (Browser $b) => $this->visualPause($b))
                // Grafik dan kartu hanya menghitung catatan pekan ini.
                ->assertAttributeContains('@grafik-kategori-respons', 'aria-label', 'Kurang tertarik 1')
                ->assertAttributeContains('@grafik-kategori-respons', 'aria-label', 'Terlibat aktif 1')
                ->assertAttributeContains('@grafik-kategori-respons', 'aria-label', 'Terlibat dengan pendampingan 0')
                ->assertSeeIn('[data-kategori="kurang_tertarik"]', '50% dari 2 catatan');

            $this->gulirKeTengah($browser, self::REKAM_JEJAK)
                ->assertSeeIn(self::REKAM_JEJAK, $this->tanggalTampil($this->awalPekan))
                ->assertDontSeeIn(self::REKAM_JEJAK, $this->tanggalTampil($this->awalPekan->copy()->subWeeks(2)))
                ->assertDontSeeIn(self::REKAM_JEJAK, 'Terlibat dengan pendampingan');

            $this->assertCount(2, $browser->elements(self::REKAM_JEJAK.' li'));
        });
    }

    public function test_tc_12_03_penanda_non_labeling_tampil(): void
    {
        $this->browse(function (Browser $browser) {
            $this->pilihAnak($browser, $this->dedek)
                ->assertVisible('#penanda-non-label')
                ->assertAttribute('#penanda-non-label', 'role', 'note')
                ->assertSeeIn('#penanda-non-label', 'Ringkasan ini adalah rangkuman data observasi')
                ->assertSeeIn('#penanda-non-label', 'bukan penilaian tetap')
                ->assertSeeIn('#penanda-non-label', 'bukan label permanen')
                ->assertSeeIn('#penanda-non-label', 'bukan diagnosis')
                ->tap(fn (Browser $b) => $this->visualPause($b));

            // Penanda juga ada di halaman daftar anak.
            $browser->visitRoute('guru.pola-respons.index')
                ->assertSeeIn('#penanda-non-label', 'bukan penilaian tetap')
                ->tap(fn (Browser $b) => $this->visualPause($b));
        });
    }

    public function test_tc_12_04_anak_tanpa_observasi_menampilkan_status_kosong(): void
    {
        $this->browse(function (Browser $browser) {
            $this->pilihAnak($browser, $this->anakBaru)
                ->assertSeeIn('#jumlah-observasi', '0 observasi')
                ->assertVisible('#status-kosong')
                ->assertSeeIn('#status-kosong', 'Belum ada data observasi')
                ->assertSeeIn('#status-kosong', 'akan muncul setelah observasi pertama dicatat')
                ->assertSeeLink('Catat observasi pertama')
                // Penanda NFR-06 tetap tampil, grafik dan filter tidak tampil.
                ->assertSeeIn('#penanda-non-label', 'bukan penilaian tetap')
                ->assertMissing('@grafik-kategori-respons')
                ->assertMissing('@filter-periode')
                ->tap(fn (Browser $b) => $this->visualPause($b, 1000));
        });
    }
}
