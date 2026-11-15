<?php

namespace Tests\Browser;

use App\Enums\KategoriRespons;
use App\Models\Anak;
use App\Models\AspekPerkembangan;
use App\Models\AturanStrategi;
use App\Models\Kelas;
use App\Models\Observasi;
use App\Models\StrategiPembelajaran;
use App\Models\User;
use Illuminate\Foundation\Testing\DatabaseMigrations;
use Illuminate\Support\Carbon;
use Laravel\Dusk\Browser;
use Tests\DuskTestCase;

class AlternatifStrategiTest extends DuskTestCase
{
    use DatabaseMigrations;

    private User $guruA;

    private Anak $anak1;

    /** @var array<string, StrategiPembelajaran> S1..S4 */
    private array $s = [];

    protected function setUp(): void
    {
        parent::setUp();

        $this->guruA = User::factory()->create(['nama' => 'Guru A']);
        $kelasA = Kelas::factory()->create(['guru_id' => $this->guruA->id]);
        Kelas::factory()->create(['guru_id' => User::factory()->create(['nama' => 'Guru B'])->id]);

        $this->anak1 = Anak::factory()->create(['kelas_id' => $kelasA->id, 'nama' => 'Anak Satu']);

        foreach (['S1' => 'Bercerita lisan', 'S2' => 'Media visual', 'S3' => 'Kelompok kecil', 'S4' => 'Permainan gerak'] as $kode => $nama) {
            $this->s[$kode] = StrategiPembelajaran::factory()->create(['nama' => $nama, 'aktif' => true]);
        }

        $kognitif = AspekPerkembangan::factory()->create(['nama' => 'Kognitif']);

        // R1
        AturanStrategi::factory()->create([
            'strategi_asal_id' => $this->s['S1']->id,
            'respons_pemicu' => KategoriRespons::KurangTertarik,
            'minimal_kejadian' => 3,
            'strategi_alternatif_id' => $this->s['S2']->id,
            'aspek_id' => null,
            'aktif' => true,
        ]);

        for ($i = 0; $i < 3; $i++) {
            Observasi::factory()->create([
                'anak_id' => $this->anak1->id,
                'strategi_id' => $this->s['S1']->id,
                'aspek_id' => $kognitif->id,
                'kategori_respons' => KategoriRespons::KurangTertarik,
                'tanggal' => Carbon::parse('2026-11-01')->addDays($i)->toDateString(),
            ]);
        }
    }

    // TC-15-25
    public function test_tc_15_25_klik_nama_anak_membuka_mode_fokus(): void
    {
        $this->browse(function (Browser $browser) {
            $browser->loginAs($this->guruA)
                ->visit('/guru/alternatif-strategi')
                ->assertSee('Anak Satu')
                ->click('@tautan-anak-'.$this->anak1->id)
                ->waitForLocation('/guru/alternatif-strategi/anak/'.$this->anak1->id)
                ->assertSee('Anak Satu')
                ->assertSee('Dari aturan')
                ->assertSee('Media visual')
                ->assertPresent('[dusk^="kartu-saran-"]');
        });
    }

    // TC-15-26
    public function test_tc_15_26_guru_menambah_alternatif_lewat_dialog(): void
    {
        $this->browse(function (Browser $browser) {
            $browser->loginAs($this->guruA)
                ->visit('/guru/alternatif-strategi/anak/'.$this->anak1->id)
                ->click('@tombol-tambah-alternatif')
                ->waitFor('@dialog-tambah-alternatif')
                ->within('@dialog-tambah-alternatif', function (Browser $dialog) {
                    $dialog->assertSeeIn('[data-ringkasan="anak"]', 'Anak Satu')
                        ->select('strategi_id', (string) $this->s['S3']->id)
                        ->waitForText('Kelompok kecil:')
                        ->type('catatan', 'Lebih antusias dengan teman dekat.')
                        ->click('@simpan-alternatif');
                })
                ->waitFor('@pesan-status')
                ->assertSeeIn('@pesan-status', 'Alternatif strategi untuk Anak Satu berhasil ditambahkan.')
                ->assertMissing('@dialog-tambah-alternatif')
                ->assertVisible('@penanda-manual')
                ->assertSee('Lebih antusias dengan teman dekat.');
        });

        $this->assertDatabaseHas('alternatif_strategi', [
            'anak_id' => $this->anak1->id,
            'strategi_id' => $this->s['S3']->id,
            'aturan_id' => null,
            'dasar_saran' => 'Lebih antusias dengan teman dekat.',
        ]);
    }
}
