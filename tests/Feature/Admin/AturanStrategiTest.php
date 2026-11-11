<?php

namespace Tests\Feature\Admin;

use App\Enums\KategoriRespons;
use App\Models\AlternatifStrategi;
use App\Models\Anak;
use App\Models\AspekPerkembangan;
use App\Models\AturanStrategi;
use App\Models\StrategiPembelajaran;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class AturanStrategiTest extends TestCase
{
    use RefreshDatabase;

    private User $admin;

    /** @var array<string, AspekPerkembangan> */
    private array $aspek = [];

    /** @var array<string, StrategiPembelajaran> ST1..ST6 */
    private array $st = [];

    /** @var array<string, AturanStrategi> AT1..AT4 */
    private array $at = [];

    protected function setUp(): void
    {
        parent::setUp();

        $this->admin = User::factory()->admin()->create();

        foreach (['Fisik-motorik', 'Kognitif', 'Bahasa', 'Sosial-emosional'] as $nama) {
            $this->aspek[$nama] = AspekPerkembangan::factory()->create(['nama' => $nama]);
        }

        foreach ([
            'ST1' => ['Media visual', true],
            'ST2' => ['Kelompok kecil', true],
            'ST3' => ['Permainan gerak', true],
            'ST4' => ['Bercerita dengan gambar', true],
            'ST5' => ['Pendampingan individu', false],
            'ST6' => ['Bernyanyi bersama', true],
        ] as $kode => [$nama, $aktif]) {
            $this->st[$kode] = StrategiPembelajaran::factory()->create(['nama' => $nama, 'aktif' => $aktif]);
        }

        $anak = Anak::factory()->create();

        foreach ([
            'AT1' => [KategoriRespons::KurangTertarik, 3, 'ST1', 'ST2', null, 5, true],
            'AT2' => [KategoriRespons::BelumTerlibat, 2, 'ST4', 'ST3', 'Bahasa', 8, true],
            'AT3' => [KategoriRespons::BelumTerlibat, 3, 'ST2', 'ST5', 'Sosial-emosional', 2, false],
            'AT4' => [KategoriRespons::TerlibatDenganPendampingan, 2, 'ST3', 'ST6', 'Kognitif', 0, true],
        ] as $kode => [$respons, $minimal, $asal, $alternatif, $aspek, $jumlahSaran, $aktif]) {
            $this->at[$kode] = AturanStrategi::factory()->create([
                'respons_pemicu' => $respons,
                'minimal_kejadian' => $minimal,
                'strategi_asal_id' => $this->st[$asal]->id,
                'strategi_alternatif_id' => $this->st[$alternatif]->id,
                'aspek_id' => $aspek ? $this->aspek[$aspek]->id : null,
                'aktif' => $aktif,
            ]);

            if ($jumlahSaran > 0) {
                AlternatifStrategi::factory()->count($jumlahSaran)->create([
                    'anak_id' => $anak->id,
                    'strategi_id' => $this->st[$alternatif]->id,
                    'aturan_id' => $this->at[$kode]->id,
                ]);
            }
        }
    }

    /**
     * @param  array<string, mixed>  $ubah
     * @return array<string, mixed>
     */
    private function isianAturan(array $ubah = []): array
    {
        return array_merge([
            '_form' => 'tambah',
            'respons_pemicu' => 'kurang_tertarik',
            'minimal_kejadian' => 3,
            'strategi_asal_id' => $this->st['ST3']->id,
            'strategi_alternatif_id' => $this->st['ST1']->id,
            'aspek_id' => $this->aspek['Fisik-motorik']->id,
            'keterangan' => '',
            'aktif' => '1',
        ], $ubah);
    }

    /**
     * @param  list<string>  $kode
     */
    private function idAturan(array $kode): array
    {
        return array_map(fn ($k) => $this->at[$k]->id, $kode);
    }

    public function test_tc_04_05_aturan_ditampilkan_sebagai_kalimat(): void
    {
        $this->actingAs($this->admin)
            ->get(route('admin.aturan-strategi.index'))
            ->assertOk()
            ->assertSee('4 Aturan')
            ->assertSee('Jika respons Kurang tertarik muncul 3x pada strategi Media visual, sarankan Kelompok kecil')
            ->assertSee('Jika respons Belum terlibat muncul 2x pada strategi Bercerita dengan gambar, sarankan Permainan gerak')
            ->assertSee('Jika respons Belum terlibat muncul 3x pada strategi Kelompok kecil, sarankan Pendampingan individu (nonaktif)')
            ->assertSee('Jika respons Terlibat dengan pendampingan muncul 2x pada strategi Permainan gerak, sarankan Bernyanyi bersama')
            ->assertSee('Aspek: Semua aspek')
            ->assertSee('Aspek: Bahasa')
            ->assertSee('Dipakai untuk 5 saran')
            ->assertSee('Dipakai untuk 8 saran')
            ->assertSee('Belum menghasilkan saran')
            ->assertSee('Nonaktif');
    }

    public function test_tc_04_06_filter_aspek_dan_status(): void
    {
        $this->actingAs($this->admin)
            ->get(route('admin.aturan-strategi.index', ['aspek' => $this->aspek['Bahasa']->id]))
            ->assertOk()
            ->assertViewHas('daftarAturan', fn ($daftar) => $daftar->pluck('id')->all() === $this->idAturan(['AT2']));

        $this->actingAs($this->admin)
            ->get(route('admin.aturan-strategi.index', ['status' => 'nonaktif']))
            ->assertOk()
            ->assertViewHas('daftarAturan', fn ($daftar) => $daftar->pluck('id')->all() === $this->idAturan(['AT3']));

        $this->actingAs($this->admin)
            ->get(route('admin.aturan-strategi.index', ['status' => 'aktif']))
            ->assertViewHas('daftarAturan', fn ($daftar) => $daftar->pluck('id')->all() === $this->idAturan(['AT1', 'AT2', 'AT4']));
    }

    public function test_tc_04_06_cari_berdasarkan_nama_strategi(): void
    {
        // "Permainan gerak" adalah alternatif AT2 dan strategi asal AT4.
        $this->actingAs($this->admin)
            ->get(route('admin.aturan-strategi.index', ['q' => 'permainan']))
            ->assertOk()
            ->assertViewHas('daftarAturan', fn ($daftar) => $daftar->pluck('id')->all() === $this->idAturan(['AT2', 'AT4']));
    }

    public function test_tc_04_07_menambah_aturan_baru(): void
    {
        $this->actingAs($this->admin)
            ->post(route('admin.aturan-strategi.store'), $this->isianAturan())
            ->assertRedirect(route('admin.aturan-strategi.index'))
            ->assertSessionHasNoErrors()
            ->assertSessionHas('status', 'Aturan strategi berhasil ditambahkan.');

        $this->assertDatabaseCount('aturan_strategi', 5);
        $this->assertDatabaseHas('aturan_strategi', [
            'respons_pemicu' => 'kurang_tertarik',
            'minimal_kejadian' => 3,
            'strategi_asal_id' => $this->st['ST3']->id,
            'strategi_alternatif_id' => $this->st['ST1']->id,
            'aspek_id' => $this->aspek['Fisik-motorik']->id,
            'aktif' => true,
        ]);
    }

    public function test_minimal_kejadian_kosong_memakai_nilai_bawaan_3(): void
    {
        $this->actingAs($this->admin)
            ->post(route('admin.aturan-strategi.store'), $this->isianAturan(['minimal_kejadian' => '']))
            ->assertSessionHasNoErrors();

        $this->assertDatabaseHas('aturan_strategi', [
            'strategi_asal_id' => $this->st['ST3']->id,
            'respons_pemicu' => 'kurang_tertarik',
            'minimal_kejadian' => 3,
        ]);
    }

    public function test_tc_04_08_mengubah_minimal_kejadian_aturan(): void
    {
        $at1 = $this->at['AT1'];

        $this->actingAs($this->admin)
            ->put(route('admin.aturan-strategi.update', $at1), $this->isianAturan([
                '_form' => 'ubah',
                '_aturan_id' => $at1->id,
                'respons_pemicu' => 'kurang_tertarik',
                'minimal_kejadian' => 4,
                'strategi_asal_id' => $this->st['ST1']->id,
                'strategi_alternatif_id' => $this->st['ST2']->id,
                'aspek_id' => '',
            ]))
            ->assertRedirect(route('admin.aturan-strategi.index'))
            ->assertSessionHasNoErrors();

        $this->assertDatabaseHas('aturan_strategi', [
            'id' => $at1->id,
            'minimal_kejadian' => 4,
            'aspek_id' => null,
            'aktif' => true,
        ]);
        // Saran lama tidak ikut berubah.
        $this->assertSame(5, $at1->alternatifStrategi()->count());
    }

    public function test_tc_04_09_menonaktifkan_aturan(): void
    {
        $this->actingAs($this->admin)
            ->patch(route('admin.aturan-strategi.nonaktifkan', $this->at['AT2']))
            ->assertRedirect(route('admin.aturan-strategi.index'))
            ->assertSessionHas('status', 'Aturan strategi berhasil dinonaktifkan.');

        $this->assertFalse($this->at['AT2']->fresh()->aktif);
        $this->assertSame(8, $this->at['AT2']->alternatifStrategi()->count());
    }

    public function test_tc_04_10_mengaktifkan_kembali_aturan(): void
    {
        $this->actingAs($this->admin)
            ->patch(route('admin.aturan-strategi.aktifkan', $this->at['AT3']))
            ->assertRedirect(route('admin.aturan-strategi.index'))
            ->assertSessionHas('status', 'Aturan strategi berhasil diaktifkan kembali.');

        $this->assertTrue($this->at['AT3']->fresh()->aktif);
    }

    public function test_tc_04_11_menghapus_aturan_yang_belum_menghasilkan_saran(): void
    {
        $this->actingAs($this->admin)
            ->delete(route('admin.aturan-strategi.destroy', $this->at['AT4']))
            ->assertRedirect(route('admin.aturan-strategi.index'))
            ->assertSessionHasNoErrors()
            ->assertSessionHas('status', 'Aturan strategi berhasil dihapus.');

        $this->assertDatabaseMissing('aturan_strategi', ['id' => $this->at['AT4']->id]);
    }

    public function test_tc_04_12_pilihan_strategi_hanya_strategi_aktif(): void
    {
        $this->actingAs($this->admin)
            ->get(route('admin.aturan-strategi.index'))
            ->assertOk()
            ->assertViewHas('pilihanStrategi', function ($pilihan) {
                $id = $pilihan->pluck('id');

                return $id->count() === 5
                    && ! $id->contains($this->st['ST5']->id)
                    && $id->contains($this->st['ST6']->id);
            });
    }

    public function test_tc_04_12_strategi_nonaktif_ditolak_saat_disimpan(): void
    {
        $this->actingAs($this->admin)
            ->post(route('admin.aturan-strategi.store'), $this->isianAturan([
                'strategi_alternatif_id' => $this->st['ST5']->id,
            ]))
            ->assertSessionHasErrors(['strategi_alternatif_id' => 'Strategi alternatif tidak ditemukan atau sudah nonaktif.']);

        $this->assertDatabaseCount('aturan_strategi', 4);
    }

    public function test_tc_04_16_strategi_asal_sama_dengan_alternatif_ditolak(): void
    {
        $this->actingAs($this->admin)
            ->post(route('admin.aturan-strategi.store'), $this->isianAturan([
                'strategi_asal_id' => $this->st['ST1']->id,
                'strategi_alternatif_id' => $this->st['ST1']->id,
                'aspek_id' => '',
            ]))
            ->assertSessionHasErrors(['strategi_alternatif_id' => 'Strategi asal dan strategi alternatif tidak boleh sama.']);

        $this->assertDatabaseCount('aturan_strategi', 4);
    }

    public function test_tc_04_17_minimal_kejadian_kurang_dari_2_ditolak(): void
    {
        $this->actingAs($this->admin)
            ->post(route('admin.aturan-strategi.store'), $this->isianAturan(['minimal_kejadian' => 1]))
            ->assertSessionHasErrors(['minimal_kejadian' => 'Minimal kejadian paling sedikit 2 kali.']);

        $this->assertDatabaseCount('aturan_strategi', 4);
    }

    public function test_tc_04_18_aturan_duplikat_ditolak(): void
    {
        // Sama dengan AT1: ST1 + kurang_tertarik + semua aspek, walau alternatifnya berbeda.
        $this->actingAs($this->admin)
            ->post(route('admin.aturan-strategi.store'), $this->isianAturan([
                'strategi_asal_id' => $this->st['ST1']->id,
                'strategi_alternatif_id' => $this->st['ST3']->id,
                'aspek_id' => '',
            ]))
            ->assertSessionHasErrors(['aturan' => 'Aturan dengan strategi asal, respons pemicu, dan aspek yang sama sudah ada.']);

        $this->assertDatabaseCount('aturan_strategi', 4);
    }

    public function test_tc_04_18_ubah_aturan_menjadi_duplikat_aturan_lain_ditolak(): void
    {
        // AT4 diubah agar sama dengan AT1 (ST1 + kurang_tertarik + semua aspek).
        $this->actingAs($this->admin)
            ->put(route('admin.aturan-strategi.update', $this->at['AT4']), $this->isianAturan([
                '_form' => 'ubah',
                'strategi_asal_id' => $this->st['ST1']->id,
                'strategi_alternatif_id' => $this->st['ST6']->id,
                'aspek_id' => '',
            ]))
            ->assertSessionHasErrors(['aturan' => 'Aturan dengan strategi asal, respons pemicu, dan aspek yang sama sudah ada.']);

        $this->assertDatabaseHas('aturan_strategi', [
            'id' => $this->at['AT4']->id,
            'strategi_asal_id' => $this->st['ST3']->id,
        ]);
    }

    public function test_tc_04_19_respons_di_luar_kategori_ditolak(): void
    {
        $this->actingAs($this->admin)
            ->post(route('admin.aturan-strategi.store'), $this->isianAturan(['respons_pemicu' => 'sangat_aktif']))
            ->assertSessionHasErrors('respons_pemicu');

        $this->assertDatabaseCount('aturan_strategi', 4);
        $this->assertDatabaseMissing('aturan_strategi', ['respons_pemicu' => 'sangat_aktif']);
    }

    public function test_tc_04_20_menghapus_aturan_yang_sudah_menghasilkan_saran_ditolak(): void
    {
        $this->actingAs($this->admin)
            ->delete(route('admin.aturan-strategi.destroy', $this->at['AT1']))
            ->assertRedirect(route('admin.aturan-strategi.index'))
            ->assertSessionHasErrors([
                'hapus' => 'Aturan sudah menghasilkan 5 saran dan hanya dapat dinonaktifkan.',
            ]);

        $this->assertDatabaseHas('aturan_strategi', ['id' => $this->at['AT1']->id]);
    }

    public function test_tc_04_21_guru_tidak_dapat_mengakses_aturan_strategi(): void
    {
        $guru = User::factory()->create();

        $this->actingAs($guru)->get(route('admin.aturan-strategi.index'))->assertForbidden();
        $this->actingAs($guru)->post(route('admin.aturan-strategi.store'), $this->isianAturan())->assertForbidden();
        $this->actingAs($guru)->put(route('admin.aturan-strategi.update', $this->at['AT1']), $this->isianAturan())->assertForbidden();
        $this->actingAs($guru)->patch(route('admin.aturan-strategi.nonaktifkan', $this->at['AT1']))->assertForbidden();
        $this->actingAs($guru)->patch(route('admin.aturan-strategi.aktifkan', $this->at['AT3']))->assertForbidden();
        $this->actingAs($guru)->delete(route('admin.aturan-strategi.destroy', $this->at['AT4']))->assertForbidden();

        $this->assertDatabaseCount('aturan_strategi', 4);
        $this->assertTrue($this->at['AT1']->fresh()->aktif);
        $this->assertFalse($this->at['AT3']->fresh()->aktif);
    }

    public function test_tc_04_22_tamu_diarahkan_ke_login(): void
    {
        $this->get(route('admin.aturan-strategi.index'))->assertRedirect('/login');
        $this->post(route('admin.aturan-strategi.store'), $this->isianAturan())->assertRedirect('/login');
        $this->delete(route('admin.aturan-strategi.destroy', $this->at['AT4']))->assertRedirect('/login');

        $this->assertDatabaseCount('aturan_strategi', 4);
    }
}
