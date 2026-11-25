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
        // Tombol keputusan PBI-16 tampil untuk alternatif berstatus disarankan.
        $respons->assertSee('Pilih Strategi Ini')->assertSee('Abaikan');
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

    // TC-15-25
    public function test_tc_15_25_alternatif_manual_tanpa_catatan_menampilkan_belum_ada_catatan(): void
    {
        AlternatifStrategi::factory()->create([
            'anak_id' => $this->anak1->id,
            'strategi_id' => $this->s['S4']->id,
            'aturan_id' => null,
            'dasar_saran' => null,
            'status' => 'disarankan',
        ]);

        $respons = $this->actingAs($this->guruA)->get('/guru/alternatif-strategi/anak/'.$this->anak1->id);

        $respons->assertOk()
            ->assertSee('dusk="catatan-kosong">Belum ada catatan.<', false);
    }

    // TC-15-26
    public function test_tc_15_26_alternatif_manual_dengan_catatan_tidak_menampilkan_belum_ada_catatan(): void
    {
        $this->tambahManual($this->anak1, 'Lebih antusias dengan teman dekat.');

        $this->actingAs($this->guruA)
            ->get('/guru/alternatif-strategi/anak/'.$this->anak1->id)
            ->assertOk()
            ->assertSee('Lebih antusias dengan teman dekat.')
            ->assertDontSee('Belum ada catatan.')
            ->assertDontSee('dusk="catatan-kosong"', false);
    }

    // TC-15-27
    public function test_tc_15_27_pesan_status_dirender_dengan_data_flash_dan_tombol_tutup(): void
    {
        $this->tambahManual($this->anak1, 'Catatan guru untuk anak satu.');

        $html = $this->actingAs($this->guruA)
            ->withSession(['status' => 'Alternatif strategi untuk Anak Satu berhasil ditambahkan.'])
            ->get('/guru/alternatif-strategi/anak/'.$this->anak1->id)
            ->assertOk()
            ->assertSee('Alternatif strategi untuk Anak Satu berhasil ditambahkan.')
            ->getContent();

        $this->assertMatchesRegularExpression('/<div[^>]*\bdata-flash\b[^>]*dusk="pesan-status"/', $html);
        $this->assertMatchesRegularExpression('/<button type="button"[^>]*aria-label="Tutup"[^>]*dusk="tutup-pesan-status"/', $html);
    }

    /** Observasi anak 1 dengan strategi saat ini (S1) dan catatan tertentu. */
    private function catatObservasiBercatatan(string $tanggal, ?string $catatan): void
    {
        Observasi::factory()->create([
            'anak_id' => $this->anak1->id,
            'strategi_id' => $this->s['S1']->id,
            'aspek_id' => $this->aspek['Kognitif']->id,
            'kategori_respons' => KategoriRespons::KurangTertarik,
            'tanggal' => $tanggal,
            'catatan_pendukung' => $catatan,
        ]);
    }

    // TC-15-28
    public function test_tc_15_28_strategi_saat_ini_menampilkan_catatan_observasi_terakhir(): void
    {
        $this->catatObservasi($this->anak1, 3);
        $this->catatObservasiBercatatan('2026-11-10', 'Catatan lama yang tidak tampil.');
        $this->catatObservasiBercatatan('2026-11-20', 'Anak tampak bosan saat cerita <b>panjang</b>.');

        $this->actingAs($this->guruA)
            ->get('/guru/alternatif-strategi/anak/'.$this->anak1->id)
            ->assertOk()
            ->assertSee('dusk="catatan-strategi-saat-ini">Anak tampak bosan saat cerita &lt;b&gt;panjang&lt;/b&gt;.<', false)
            ->assertDontSee('Catatan lama yang tidak tampil.')
            ->assertDontSee('dusk="catatan-strategi-saat-ini-kosong"', false);
    }

    // TC-15-29
    public function test_tc_15_29_strategi_saat_ini_tanpa_catatan_menampilkan_belum_ada_catatan(): void
    {
        $this->catatObservasi($this->anak1, 3);
        $this->catatObservasiBercatatan('2026-11-10', 'Catatan lama yang tidak tampil.');
        $this->catatObservasiBercatatan('2026-11-20', '   ');

        $this->actingAs($this->guruA)
            ->get('/guru/alternatif-strategi/anak/'.$this->anak1->id)
            ->assertOk()
            ->assertSee('dusk="catatan-strategi-saat-ini-kosong">Belum ada catatan.<', false)
            ->assertDontSee('Catatan lama yang tidak tampil.')
            ->assertDontSee('dusk="catatan-strategi-saat-ini"', false);
    }

    // TC-15-30
    public function test_tc_15_30_alternatif_manual_tanpa_catatan_tanpa_label_catatan_guru(): void
    {
        $saran = AlternatifStrategi::factory()->create([
            'anak_id' => $this->anak1->id,
            'strategi_id' => $this->s['S4']->id,
            'aturan_id' => null,
            'dasar_saran' => null,
            'status' => 'disarankan',
        ]);

        $html = $this->actingAs($this->guruA)
            ->get('/guru/alternatif-strategi/anak/'.$this->anak1->id)
            ->assertOk()
            ->getContent();

        $this->assertSame(1, preg_match('/<article dusk="kartu-saran-'.$saran->id.'".*?<\/article>/s', $html, $kartu));
        $this->assertStringContainsString('dusk="catatan-kosong">Belum ada catatan.<', $kartu[0]);
        $this->assertStringNotContainsString('Catatan guru:', $kartu[0]);
    }
}
