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

    private User $guru;

    private Anak $dedek;

    private Anak $anakBaru;

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
        $awalPekan = Carbon::now()->startOfWeek(Carbon::MONDAY);

        $baris = [
            [$awalPekan->toDateString(), $kognitif, $balok, KategoriRespons::KurangTertarik],
            [$awalPekan->toDateString(), $bahasa, $bercerita, KategoriRespons::TerlibatAktif],
            [$awalPekan->copy()->subWeeks(2)->toDateString(), $bahasa, $bercerita, KategoriRespons::TerlibatDenganPendampingan],
            [$awalPekan->copy()->subWeeks(3)->toDateString(), $kognitif, $balok, KategoriRespons::KurangTertarik],
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

    public function test_tc_12_01_grafik_chart_js_tampil(): void
    {
        $this->browse(function (Browser $browser) {
            $browser->loginAs($this->guru)
                ->visit(route('guru.pola-respons.anak', $this->dedek))
                ->assertSee('Ringkasan Pola Respons')
                ->waitFor('#grafik-kategori-respons[data-siap="1"]')
                ->waitFor('#grafik-pola-kelompok[data-siap="1"]')
                ->assertSee('4 observasi');

            // Tab aktivitas mengganti isi grafik dan tabel.
            $browser->click('@tab-aktivitas')
                ->waitFor('#grafik-pola-kelompok[data-kelompok="aktivitas"]')
                ->assertVisible('#tabel-aktivitas')
                ->assertMissing('#tabel-aspek');
        });
    }

    public function test_tc_12_02_filter_pekan_ini_memperbarui_ringkasan(): void
    {
        $this->browse(function (Browser $browser) {
            $browser->loginAs($this->guru)
                ->visit(route('guru.pola-respons.anak', $this->dedek))
                ->select('@filter-periode', 'pekan-ini')
                ->waitForText('2 dari 4 observasi')
                ->assertQueryStringHas('periode', 'pekan-ini')
                ->waitFor('#grafik-kategori-respons[data-siap="1"]');
        });
    }

    public function test_tc_12_03_dan_tc_12_04_penanda_dan_status_kosong(): void
    {
        $this->browse(function (Browser $browser) {
            $browser->loginAs($this->guru)
                ->visit(route('guru.pola-respons.anak', $this->anakBaru))
                ->assertSee('bukan penilaian tetap')
                ->assertSee('bukan diagnosis')
                ->assertSee('Belum ada data observasi')
                ->assertSee('0 observasi')
                ->assertMissing('#grafik-kategori-respons');
        });
    }
}
