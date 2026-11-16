<?php

namespace Tests\Feature\AlternatifStrategi;

use App\Enums\KategoriRespons;
use App\Models\AlternatifStrategi;
use App\Models\Anak;
use App\Models\AspekPerkembangan;
use App\Models\AturanStrategi;
use App\Models\Kelas;
use App\Models\Observasi;
use App\Models\StrategiPembelajaran;
use App\Models\User;
use App\Services\AlternatifStrategiService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Carbon;
use Illuminate\Support\Collection;
use Tests\TestCase;

class AlternatifStrategiServiceTest extends TestCase
{
    use RefreshDatabase;

    private AlternatifStrategiService $layanan;

    private Kelas $kelasA;

    private Anak $anak1;

    private Anak $anak2;

    /** @var array<string, StrategiPembelajaran> S1..S4 */
    private array $s = [];

    /** @var array<string, AspekPerkembangan> */
    private array $aspek = [];

    private AturanStrategi $r1;

    private AturanStrategi $r2;

    protected function setUp(): void
    {
        parent::setUp();

        $this->layanan = app(AlternatifStrategiService::class);

        $guruA = User::factory()->create();
        $guruB = User::factory()->create();
        $this->kelasA = Kelas::factory()->create(['guru_id' => $guruA->id]);
        $kelasB = Kelas::factory()->create(['guru_id' => $guruB->id]);

        $this->anak1 = Anak::factory()->create(['kelas_id' => $this->kelasA->id, 'nama' => 'Anak Satu']);
        $this->anak2 = Anak::factory()->create(['kelas_id' => $this->kelasA->id, 'nama' => 'Anak Dua']);
        Anak::factory()->create(['kelas_id' => $this->kelasA->id, 'nama' => 'Anak Tiga']);
        Anak::factory()->create(['kelas_id' => $kelasB->id, 'nama' => 'Anak Lima']);

        foreach (['S1' => 'Bercerita lisan', 'S2' => 'Media visual', 'S3' => 'Kelompok kecil', 'S4' => 'Permainan gerak'] as $kode => $nama) {
            $this->s[$kode] = StrategiPembelajaran::factory()->create(['nama' => $nama, 'aktif' => true]);
        }

        foreach (['Kognitif', 'Bahasa', 'Seni'] as $nama) {
            $this->aspek[$nama] = AspekPerkembangan::factory()->create(['nama' => $nama]);
        }

        $this->r1 = AturanStrategi::factory()->create([
            'strategi_asal_id' => $this->s['S1']->id,
            'respons_pemicu' => KategoriRespons::KurangTertarik,
            'minimal_kejadian' => 3,
            'strategi_alternatif_id' => $this->s['S2']->id,
            'aspek_id' => null,
            'aktif' => true,
        ]);
        $this->r2 = AturanStrategi::factory()->create([
            'strategi_asal_id' => $this->s['S1']->id,
            'respons_pemicu' => KategoriRespons::BelumTerlibat,
            'minimal_kejadian' => 2,
            'strategi_alternatif_id' => $this->s['S3']->id,
            'aspek_id' => $this->aspek['Bahasa']->id,
            'aktif' => true,
        ]);
    }

    /**
     * Catat observasi berurutan per hari. Bawaan: strategi S1, Kurang tertarik, aspek Kognitif (cocok R1).
     */
    private function catatObservasi(
        Anak $anak,
        int $jumlah,
        KategoriRespons $respons = KategoriRespons::KurangTertarik,
        string $strategi = 'S1',
        string $aspek = 'Kognitif',
        string $tanggalAwal = '2026-11-01',
    ): void {
        for ($i = 0; $i < $jumlah; $i++) {
            Observasi::factory()->create([
                'anak_id' => $anak->id,
                'strategi_id' => $this->s[$strategi]->id,
                'aspek_id' => $this->aspek[$aspek]->id,
                'kategori_respons' => $respons,
                'tanggal' => Carbon::parse($tanggalAwal)->addDays($i)->toDateString(),
            ]);
        }
    }

    private function polaUntuk(Collection $daftarPola, Anak $anak, AturanStrategi $aturan): ?array
    {
        return $daftarPola->first(fn (array $pola) => $pola['anak']->is($anak) && $pola['aturan']->is($aturan));
    }

    // TC-15-01
    public function test_tc_15_01_tiga_observasi_cocok_membuat_satu_saran(): void
    {
        $this->catatObservasi($this->anak1, 3);

        $this->layanan->susunUntukKelas($this->kelasA);

        $this->assertDatabaseCount('alternatif_strategi', 1);
        $this->assertDatabaseHas('alternatif_strategi', [
            'anak_id' => $this->anak1->id,
            'strategi_id' => $this->s['S2']->id,
            'aturan_id' => $this->r1->id,
            'status' => 'disarankan',
        ]);
    }

    // TC-15-02
    public function test_tc_15_02_dua_observasi_cocok_belum_membuat_saran(): void
    {
        $this->catatObservasi($this->anak1, 2);

        $daftarPola = $this->layanan->susunUntukKelas($this->kelasA);

        $this->assertDatabaseCount('alternatif_strategi', 0);
        $pola = $this->polaUntuk($daftarPola, $this->anak1, $this->r1);
        $this->assertNotNull($pola);
        $this->assertSame(2, $pola['jumlah']);
        $this->assertNull($pola['saran']);
    }

    // TC-15-03
    public function test_tc_15_03_tepat_minimal_kejadian_membuat_saran(): void
    {
        $this->catatObservasi($this->anak1, 3);

        $pola = $this->polaUntuk($this->layanan->susunUntukKelas($this->kelasA), $this->anak1, $this->r1);

        $this->assertSame(3, $pola['jumlah']);
        $this->assertNotNull($pola['saran']);
        $this->assertDatabaseCount('alternatif_strategi', 1);
    }

    // TC-15-04
    public function test_tc_15_04_strategi_berbeda_tidak_dihitung(): void
    {
        $this->catatObservasi($this->anak1, 3, strategi: 'S4');

        $daftarPola = $this->layanan->susunUntukKelas($this->kelasA);

        $this->assertCount(0, $daftarPola);
        $this->assertDatabaseCount('alternatif_strategi', 0);
    }

    // TC-15-05
    public function test_tc_15_05_respons_lain_tidak_ikut_dihitung(): void
    {
        $this->catatObservasi($this->anak1, 2);
        $this->catatObservasi($this->anak1, 2, KategoriRespons::TerlibatAktif, tanggalAwal: '2026-11-05');

        $daftarPola = $this->layanan->susunUntukKelas($this->kelasA);

        $this->assertDatabaseCount('alternatif_strategi', 0);
        $this->assertSame(2, $this->polaUntuk($daftarPola, $this->anak1, $this->r1)['jumlah']);
    }

    // TC-15-06
    public function test_tc_15_06_aturan_nonaktif_tidak_dipakai(): void
    {
        $this->r1->update(['aktif' => false]);
        $this->catatObservasi($this->anak1, 3);

        $daftarPola = $this->layanan->susunUntukKelas($this->kelasA);

        $this->assertNull($this->polaUntuk($daftarPola, $this->anak1, $this->r1));
        $this->assertCount(0, $daftarPola);
        $this->assertDatabaseCount('alternatif_strategi', 0);
    }

    // TC-15-07
    public function test_tc_15_07_strategi_alternatif_nonaktif_tidak_disarankan(): void
    {
        $this->s['S2']->update(['aktif' => false]);
        $this->catatObservasi($this->anak1, 3);

        $pola = $this->polaUntuk($this->layanan->susunUntukKelas($this->kelasA), $this->anak1, $this->r1);

        $this->assertDatabaseCount('alternatif_strategi', 0);
        $this->assertNotNull($pola);
        $this->assertSame(3, $pola['jumlah']);
        $this->assertNull($pola['saran']);
    }

    // TC-15-08
    public function test_tc_15_08_aturan_beraspek_tidak_cocok_dengan_aspek_lain(): void
    {
        $this->catatObservasi($this->anak1, 2, KategoriRespons::BelumTerlibat, aspek: 'Seni');

        $daftarPola = $this->layanan->susunUntukKelas($this->kelasA);

        $this->assertNull($this->polaUntuk($daftarPola, $this->anak1, $this->r2));
        $this->assertDatabaseCount('alternatif_strategi', 0);
    }

    // TC-15-09
    public function test_tc_15_09_aturan_beraspek_cocok_dengan_aspek_yang_sama(): void
    {
        $this->catatObservasi($this->anak1, 2, KategoriRespons::BelumTerlibat, aspek: 'Bahasa');

        $this->layanan->susunUntukKelas($this->kelasA);

        $this->assertDatabaseCount('alternatif_strategi', 1);
        $this->assertDatabaseHas('alternatif_strategi', [
            'anak_id' => $this->anak1->id,
            'strategi_id' => $this->s['S3']->id,
            'aturan_id' => $this->r2->id,
        ]);
    }

    // TC-15-10
    public function test_tc_15_10_observasi_antaranak_tidak_digabung(): void
    {
        $this->catatObservasi($this->anak1, 2);
        $this->catatObservasi($this->anak2, 2);

        $daftarPola = $this->layanan->susunUntukKelas($this->kelasA);

        $this->assertDatabaseCount('alternatif_strategi', 0);
        $this->assertSame(2, $this->polaUntuk($daftarPola, $this->anak1, $this->r1)['jumlah']);
        $this->assertSame(2, $this->polaUntuk($daftarPola, $this->anak2, $this->r1)['jumlah']);
    }

    // TC-15-11
    public function test_tc_15_11_tidak_membuat_saran_ganda(): void
    {
        $this->catatObservasi($this->anak1, 3);

        $this->layanan->susunUntukKelas($this->kelasA);
        $this->layanan->susunUntukKelas($this->kelasA);

        $this->assertDatabaseCount('alternatif_strategi', 1);
    }

    // TC-15-12
    public function test_tc_15_12_dasar_saran_memuat_jumlah_strategi_aspek_dan_tanggal(): void
    {
        $this->catatObservasi($this->anak1, 3, tanggalAwal: '2026-11-01');

        $this->layanan->susunUntukKelas($this->kelasA);

        $dasar = AlternatifStrategi::query()->sole()->dasar_saran;
        $this->assertStringContainsString('3 kali', $dasar);
        $this->assertStringContainsString('Bercerita lisan', $dasar);
        $this->assertStringContainsString('Semua aspek', $dasar);
        $this->assertStringContainsString('01/11/2026, 02/11/2026, 03/11/2026', $dasar);
    }

    // TC-15-13
    public function test_tc_15_13_dua_aturan_terpenuhi_membuat_dua_saran(): void
    {
        $this->catatObservasi($this->anak1, 3);
        $this->catatObservasi($this->anak1, 2, KategoriRespons::BelumTerlibat, aspek: 'Bahasa', tanggalAwal: '2026-11-05');

        $this->layanan->susunUntukKelas($this->kelasA);

        $this->assertDatabaseCount('alternatif_strategi', 2);
        $this->assertDatabaseHas('alternatif_strategi', ['anak_id' => $this->anak1->id, 'aturan_id' => $this->r1->id, 'strategi_id' => $this->s['S2']->id]);
        $this->assertDatabaseHas('alternatif_strategi', ['anak_id' => $this->anak1->id, 'aturan_id' => $this->r2->id, 'strategi_id' => $this->s['S3']->id]);
    }

    // TC-15-14
    public function test_tc_15_14_kelompokkan_per_anak_satu_item_per_anak(): void
    {
        $this->catatObservasi($this->anak1, 3);
        $this->catatObservasi($this->anak1, 2, KategoriRespons::BelumTerlibat, aspek: 'Bahasa', tanggalAwal: '2026-11-05');
        $this->layanan->tambahManual($this->anak1, $this->s['S4']->id, 'Catatan guru');

        $daftarAnak = $this->layanan->kelompokkanPerAnak(
            $this->layanan->susunUntukKelas($this->kelasA),
            $this->layanan->saranManual($this->kelasA),
        );

        $itemAnak1 = $daftarAnak->filter(fn (array $item) => $item['anak']->is($this->anak1));
        $this->assertCount(1, $itemAnak1);
        $this->assertCount(2, $itemAnak1->first()['pola']);
        $this->assertCount(1, $itemAnak1->first()['manual']);
    }
}
