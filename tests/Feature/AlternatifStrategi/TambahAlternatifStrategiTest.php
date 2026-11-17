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

class TambahAlternatifStrategiTest extends TestCase
{
    use RefreshDatabase;

    private User $guruA;

    private Anak $anak1;

    private Anak $anak5;

    /** @var array<string, StrategiPembelajaran> S1..S4 */
    private array $s = [];

    protected function setUp(): void
    {
        parent::setUp();

        $this->guruA = User::factory()->create();
        $guruB = User::factory()->create();
        $kelasA = Kelas::factory()->create(['guru_id' => $this->guruA->id]);
        $kelasB = Kelas::factory()->create(['guru_id' => $guruB->id]);

        $this->anak1 = Anak::factory()->create(['kelas_id' => $kelasA->id, 'nama' => 'Anak Satu']);
        Anak::factory()->create(['kelas_id' => $kelasA->id, 'nama' => 'Anak Dua']);
        Anak::factory()->create(['kelas_id' => $kelasA->id, 'nama' => 'Anak Tiga']);
        $this->anak5 = Anak::factory()->create(['kelas_id' => $kelasB->id, 'nama' => 'Anak Lima']);

        foreach (['S1' => 'Bercerita lisan', 'S2' => 'Media visual', 'S3' => 'Kelompok kecil', 'S4' => 'Permainan gerak'] as $kode => $nama) {
            $this->s[$kode] = StrategiPembelajaran::factory()->create(['nama' => $nama, 'aktif' => true]);
        }

        $kognitif = AspekPerkembangan::factory()->create(['nama' => 'Kognitif']);
        $bahasa = AspekPerkembangan::factory()->create(['nama' => 'Bahasa']);

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
            'aspek_id' => $bahasa->id,
            'aktif' => true,
        ]);

        // Anak 1 dan Anak 5 masing-masing punya 3 observasi cocok R1.
        foreach ([$this->anak1, $this->anak5] as $anak) {
            for ($i = 0; $i < 3; $i++) {
                Observasi::factory()->create([
                    'anak_id' => $anak->id,
                    'strategi_id' => $this->s['S1']->id,
                    'aspek_id' => $kognitif->id,
                    'kategori_respons' => KategoriRespons::KurangTertarik,
                    'tanggal' => Carbon::parse('2026-11-01')->addDays($i)->toDateString(),
                ]);
            }
        }
    }

    private function urlTambah(Anak $anak): string
    {
        return '/guru/alternatif-strategi/anak/'.$anak->id;
    }

    // TC-15-20
    public function test_tc_15_20_guru_menambah_alternatif_dengan_catatan(): void
    {
        $respons = $this->actingAs($this->guruA)->post($this->urlTambah($this->anak1), [
            'strategi_id' => $this->s['S3']->id,
            'catatan' => 'Lebih antusias dengan teman dekat.',
        ]);

        $respons->assertRedirect(route('guru.alternatif-strategi.anak', $this->anak1))
            ->assertSessionHas('status', 'Alternatif strategi untuk Anak Satu berhasil ditambahkan.');
        $this->assertDatabaseHas('alternatif_strategi', [
            'anak_id' => $this->anak1->id,
            'strategi_id' => $this->s['S3']->id,
            'aturan_id' => null,
            'status' => 'disarankan',
            'dasar_saran' => 'Lebih antusias dengan teman dekat.',
        ]);
    }

    // TC-15-21
    public function test_tc_15_21_catatan_kosong_disimpan_null(): void
    {
        // Strategi dibedakan per percobaan: strategi yang masih disarankan tidak boleh ditambah lagi.
        foreach ([['S3', ''], ['S4', '   ']] as [$kode, $catatan]) {
            $this->actingAs($this->guruA)
                ->post($this->urlTambah($this->anak1), ['strategi_id' => $this->s[$kode]->id, 'catatan' => $catatan])
                ->assertSessionHasNoErrors();
        }

        $this->assertDatabaseCount('alternatif_strategi', 2);
        $this->assertSame(2, AlternatifStrategi::query()->whereNull('dasar_saran')->whereNull('aturan_id')->count());
    }

    // TC-15-22
    public function test_tc_15_22_strategi_wajib_dipilih(): void
    {
        $this->actingAs($this->guruA)
            ->post($this->urlTambah($this->anak1), ['strategi_id' => '', 'catatan' => 'Catatan'])
            ->assertSessionHasErrors(['strategi_id' => 'Strategi wajib dipilih.']);

        $this->assertDatabaseCount('alternatif_strategi', 0);
    }

    // TC-15-23
    public function test_tc_15_23_strategi_tidak_valid_ditolak(): void
    {
        $nonaktif = StrategiPembelajaran::factory()->nonaktif()->create(['nama' => 'Strategi nonaktif']);

        foreach ([999999, $nonaktif->id, 'abc'] as $strategiId) {
            $this->actingAs($this->guruA)
                ->post($this->urlTambah($this->anak1), ['strategi_id' => $strategiId])
                ->assertSessionHasErrors('strategi_id');
        }

        $this->assertDatabaseCount('alternatif_strategi', 0);
    }

    // TC-15-24
    public function test_tc_15_24_guru_tidak_boleh_menambah_untuk_anak_kelas_lain(): void
    {
        $this->actingAs($this->guruA)
            ->post($this->urlTambah($this->anak5), ['strategi_id' => $this->s['S3']->id])
            ->assertForbidden();

        $this->assertDatabaseCount('alternatif_strategi', 0);
    }
}
