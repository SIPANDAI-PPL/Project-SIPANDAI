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
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Carbon;
use Tests\TestCase;

class TampilanAlternatifStrategiTest extends TestCase
{
    use RefreshDatabase;

    private User $guruA;

    private Anak $anak1;

    private Anak $anak2;

    private Anak $anak3;

    /** @var array<string, StrategiPembelajaran> S1..S4 */
    private array $s = [];

    /** @var array<string, AspekPerkembangan> */
    private array $aspek = [];

    protected function setUp(): void
    {
        parent::setUp();

        $this->guruA = User::factory()->create();
        $kelasA = Kelas::factory()->create(['guru_id' => $this->guruA->id]);
        $kelasB = Kelas::factory()->create(['guru_id' => User::factory()->create()->id]);

        $this->anak1 = Anak::factory()->create(['kelas_id' => $kelasA->id, 'nama' => 'Anak Satu']);
        $this->anak2 = Anak::factory()->create(['kelas_id' => $kelasA->id, 'nama' => 'Anak Dua']);
        $this->anak3 = Anak::factory()->create(['kelas_id' => $kelasA->id, 'nama' => 'Anak Tiga']);
        Anak::factory()->create(['kelas_id' => $kelasB->id, 'nama' => 'Anak Lima']);

        foreach (['S1' => 'Bercerita lisan', 'S2' => 'Media visual', 'S3' => 'Kelompok kecil', 'S4' => 'Permainan gerak'] as $kode => $nama) {
            $this->s[$kode] = StrategiPembelajaran::factory()->create(['nama' => $nama, 'aktif' => true]);
        }

        foreach (['Kognitif', 'Bahasa'] as $nama) {
            $this->aspek[$nama] = AspekPerkembangan::factory()->create(['nama' => $nama]);
        }

        // R1
        AturanStrategi::factory()->create([
            'strategi_asal_id' => $this->s['S1']->id,
            'respons_pemicu' => KategoriRespons::KurangTertarik,
            'minimal_kejadian' => 3,
            'strategi_alternatif_id' => $this->s['S2']->id,
            'aspek_id' => null,
            'aktif' => true,
        ]);
        // R2
        AturanStrategi::factory()->create([
            'strategi_asal_id' => $this->s['S1']->id,
            'respons_pemicu' => KategoriRespons::BelumTerlibat,
            'minimal_kejadian' => 2,
            'strategi_alternatif_id' => $this->s['S3']->id,
            'aspek_id' => $this->aspek['Bahasa']->id,
            'aktif' => true,
        ]);
    }

    private function catatObservasi(Anak $anak, int $jumlah, KategoriRespons $respons = KategoriRespons::KurangTertarik, string $aspek = 'Kognitif'): void
    {
        for ($i = 0; $i < $jumlah; $i++) {
            Observasi::factory()->create([
                'anak_id' => $anak->id,
                'strategi_id' => $this->s['S1']->id,
                'aspek_id' => $this->aspek[$aspek]->id,
                'kategori_respons' => $respons,
                'tanggal' => Carbon::parse('2026-11-01')->addDays($i)->toDateString(),
            ]);
        }
    }

    private function tambahManual(Anak $anak, string $catatan): void
    {
        AlternatifStrategi::factory()->create([
            'anak_id' => $anak->id,
            'strategi_id' => $this->s['S4']->id,
            'aturan_id' => null,
            'dasar_saran' => $catatan,
            'status' => 'disarankan',
        ]);
    }

    /** Anak 1: pola R1 (3x) dan pola R2 (2x, Bahasa), keduanya terpenuhi. */
    private function siapkanAnak1DenganDuaPola(): void
    {
        $this->catatObservasi($this->anak1, 3);
        $this->catatObservasi($this->anak1, 2, KategoriRespons::BelumTerlibat, 'Bahasa');
    }

    // TC-15-15
    public function test_tc_15_15_anak_hanya_muncul_di_satu_baris(): void
    {
        $this->siapkanAnak1DenganDuaPola();
        $this->tambahManual($this->anak1, 'Catatan guru untuk anak satu.');

        $respons = $this->actingAs($this->guruA)->get('/guru/alternatif-strategi');

        $respons->assertOk();
        $this->assertSame(1, substr_count($respons->getContent(), 'id="baris-anak-'.$this->anak1->id.'"'));
    }

    // TC-15-16
    public function test_tc_15_16_kartu_ringkasan_menghitung_anak(): void
    {
        $this->siapkanAnak1DenganDuaPola();
        $this->catatObservasi($this->anak2, 1);
        $this->tambahManual($this->anak3, 'Alternatif dari guru.');

        $respons = $this->actingAs($this->guruA)->get('/guru/alternatif-strategi');

        $respons->assertOk()
            ->assertSee('dusk="jumlah-pola">2<', false)
            ->assertSee('dusk="jumlah-saran">2<', false)
            ->assertSee('dusk="jumlah-belum-cukup">1<', false);
    }

    // TC-15-17
    public function test_tc_15_17_pola_di_bawah_minimal_tampil_tanpa_saran(): void
    {
        $this->catatObservasi($this->anak2, 1);

        $respons = $this->actingAs($this->guruA)->get('/guru/alternatif-strategi');

        $respons->assertOk()
            ->assertSee('Belum ada alternatif')
            ->assertSee('1 dari minimal 3 kejadian');
        $this->assertDatabaseCount('alternatif_strategi', 0);
    }

    // TC-15-18
    public function test_tc_15_18_halaman_fokus_menampilkan_semua_alternatif_anak(): void
    {
        $this->siapkanAnak1DenganDuaPola();
        $this->tambahManual($this->anak1, 'Lebih antusias dengan teman dekat.');

        $respons = $this->actingAs($this->guruA)->get('/guru/alternatif-strategi/anak/'.$this->anak1->id);

        $respons->assertOk()
            ->assertSee('Lebih antusias dengan teman dekat.');
        $html = $respons->getContent();
        $this->assertSame(3, substr_count($html, 'dusk="kartu-saran-'));
        $this->assertSame(2, substr_count($html, '>Dari aturan</span>'));
        $this->assertSame(2, substr_count($html, 'Dasar saran:'));
        $this->assertSame(1, substr_count($html, 'dusk="penanda-manual"'));
        $this->assertSame(1, substr_count($html, 'Catatan guru:'));
        // Tombol keputusan milik PBI-16.
        $this->assertDoesNotMatchRegularExpression('/<button[^>]*>\s*(Pilih|Abaikan|Tinjau Ulang)\b/i', $html);
        $respons->assertDontSee('Abaikan')->assertDontSee('Tinjau Ulang');
    }

    // TC-15-19
    public function test_tc_15_19_halaman_fokus_anak_belum_cukup_data(): void
    {
        $this->catatObservasi($this->anak2, 1);

        $this->actingAs($this->guruA)
            ->get('/guru/alternatif-strategi/anak/'.$this->anak2->id)
            ->assertOk()
            ->assertSee('Belum ada alternatif')
            ->assertSee('1 dari minimal 3 kejadian');
    }
}
