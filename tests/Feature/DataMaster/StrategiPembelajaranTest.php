<?php

namespace Tests\Feature\DataMaster;

use App\Models\AlternatifStrategi;
use App\Models\AturanStrategi;
use App\Models\StrategiPembelajaran;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\Support\DataUjiDataMaster;
use Tests\TestCase;

class StrategiPembelajaranTest extends TestCase
{
    use DataUjiDataMaster;
    use RefreshDatabase;

    /** @var array<string, mixed> */
    private array $data;

    protected function setUp(): void
    {
        parent::setUp();

        $this->data = $this->buatDataUjiDataMaster();
    }

    private function indexUrl(array $parameter = []): string
    {
        return route('data-master.strategi.index', $parameter);
    }

    public function test_tc_02_02_menampilkan_daftar_strategi_dan_jumlah_pemakaian(): void
    {
        $respons = $this->actingAs($this->data['admin'])->get($this->indexUrl());

        $respons->assertOk()
            ->assertSeeInOrder(['Media visual', 'Kelompok kecil', 'Pendampingan individu', 'Bernyanyi bersama'])
            ->assertSee('4 Strategi')
            ->assertSee('3 observasi')
            ->assertSee('1 observasi · 1 aturan')
            ->assertSee('Nonaktif');

        $respons->assertViewHas('daftar', function ($daftar) {
            $item = collect($daftar->items())->keyBy('id');
            $st1 = $item[$this->data['st1']->id];
            $st2 = $item[$this->data['st2']->id];
            $st5 = $item[$this->data['st5']->id];
            $st6 = $item[$this->data['st6']->id];

            return (int) $st1->observasi_count === 3 && $st1->jumlahAturan() === 0
                && (int) $st2->observasi_count === 1 && $st2->jumlahAturan() === 1
                && (int) $st5->observasi_count === 1 && $st5->jumlahAturan() === 0
                && (int) $st6->observasi_count === 0 && $st6->jumlahAturan() === 1;
        });
    }

    public function test_strategi_yang_hanya_dipakai_alternatif_strategi_terhitung(): void
    {
        $baru = StrategiPembelajaran::factory()->create(['nama' => 'Tanya jawab']);
        AlternatifStrategi::factory()->create([
            'anak_id' => $this->data['anak']->id,
            'strategi_id' => $baru->id,
        ]);

        $this->actingAs($this->data['admin'])
            ->get($this->indexUrl())
            ->assertSee('1 alternatif')
            ->assertDontSee('dusk="aksi-hapus-'.$baru->id.'"', false);

        $this->delete(route('data-master.strategi.destroy', $baru))
            ->assertSessionHasErrors(['hapus' => 'Data sudah dipakai pada 1 alternatif strategi dan hanya dapat dinonaktifkan.']);

        $this->assertDatabaseHas('strategi_pembelajaran', ['id' => $baru->id]);
    }

    public function test_tc_02_06_menambah_strategi_tutor_sebaya(): void
    {
        $this->actingAs($this->data['admin'])
            ->from($this->indexUrl())
            ->post(route('data-master.strategi.store'), [
                'nama' => 'Tutor sebaya',
                'deskripsi' => 'Anak yang sudah mampu membantu teman sebaya.',
            ])
            ->assertRedirect($this->indexUrl())
            ->assertSessionHasNoErrors()
            ->assertSessionHas('status', 'Strategi Tutor sebaya berhasil ditambahkan.');

        $this->assertDatabaseHas('strategi_pembelajaran', [
            'nama' => 'Tutor sebaya',
            'deskripsi' => 'Anak yang sudah mampu membantu teman sebaya.',
            'aktif' => true,
        ]);

        $this->get($this->indexUrl())->assertSee('Tutor sebaya')->assertSee('5 Strategi');
    }

    public function test_tc_02_09_mengaktifkan_pendampingan_individu(): void
    {
        $st5 = $this->data['st5'];

        $this->actingAs($this->data['admin'])
            ->get($this->indexUrl())
            ->assertSee('dusk="aksi-aktifkan-'.$st5->id.'"', false);

        $this->patch(route('data-master.strategi.aktifkan', $st5))
            ->assertRedirect($this->indexUrl())
            ->assertSessionHas('status', 'Strategi Pendampingan individu diaktifkan kembali.');

        $this->assertDatabaseHas('strategi_pembelajaran', ['id' => $st5->id, 'aktif' => true]);
        $this->assertTrue(StrategiPembelajaran::aktif()->whereKey($st5->id)->exists());
    }

    public function test_strategi_dapat_dinonaktifkan_dan_diubah(): void
    {
        $st1 = $this->data['st1'];

        $this->actingAs($this->data['admin'])
            ->patch(route('data-master.strategi.nonaktifkan', $st1))
            ->assertSessionHas('status', 'Strategi Media visual dinonaktifkan.');
        $this->assertDatabaseHas('strategi_pembelajaran', ['id' => $st1->id, 'aktif' => false]);

        $this->put(route('data-master.strategi.update', $st1), ['nama' => 'Media visual', 'deskripsi' => 'Kartu gambar dan poster.'])
            ->assertSessionHasNoErrors()
            ->assertSessionHas('status', 'Strategi Media visual berhasil diperbarui.');
        $this->assertDatabaseHas('strategi_pembelajaran', ['id' => $st1->id, 'deskripsi' => 'Kartu gambar dan poster.', 'aktif' => false]);
    }

    public function test_tc_02_11_guru_menambah_strategi_dan_tidak_melihat_tab_aspek(): void
    {
        $guru = $this->data['guru'];
        $this->assertSame('miastacia', $guru->nama_pengguna);

        $this->actingAs($guru)
            ->get($this->indexUrl())
            ->assertOk()
            ->assertSee('Aktivitas &amp; Strategi', false)
            ->assertSee('Strategi Pembelajaran')
            ->assertDontSee('Aspek Perkembangan');

        $this->from($this->indexUrl())
            ->post(route('data-master.strategi.store'), [
                'nama' => 'Belajar sambil bermain',
                'deskripsi' => 'Materi dikemas dalam permainan.',
            ])
            ->assertRedirect($this->indexUrl())
            ->assertSessionHasNoErrors();

        $this->assertDatabaseHas('strategi_pembelajaran', ['nama' => 'Belajar sambil bermain', 'aktif' => true]);
    }

    public function test_tc_02_15_nama_strategi_duplikat_ditolak(): void
    {
        $this->actingAs($this->data['admin'])
            ->from($this->indexUrl())
            ->post(route('data-master.strategi.store'), ['nama' => 'media VISUAL'])
            ->assertSessionHasErrors(['nama' => 'Nama strategi sudah terdaftar.']);

        $this->put(route('data-master.strategi.update', $this->data['st2']), ['nama' => 'Media visual'])
            ->assertSessionHasErrors(['nama' => 'Nama strategi sudah terdaftar.']);

        $this->assertDatabaseHas('strategi_pembelajaran', ['id' => $this->data['st2']->id, 'nama' => 'Kelompok kecil']);
    }

    public function test_nama_strategi_kosong_dan_terlalu_panjang_ditolak(): void
    {
        $this->actingAs($this->data['admin'])
            ->post(route('data-master.strategi.store'), ['nama' => ''])
            ->assertSessionHasErrors(['nama' => 'Nama wajib diisi.']);

        $this->post(route('data-master.strategi.store'), ['nama' => str_repeat('a', 101)])
            ->assertSessionHasErrors(['nama' => 'Nama maksimal 100 karakter.']);

        $this->assertSame(4, StrategiPembelajaran::count());
    }

    public function test_tc_02_18_hapus_bernyanyi_bersama_yang_dipakai_aturan_ditolak(): void
    {
        $st6 = $this->data['st6'];

        $this->actingAs($this->data['admin'])
            ->get($this->indexUrl())
            ->assertDontSee('dusk="aksi-hapus-'.$st6->id.'"', false);

        $this->delete(route('data-master.strategi.destroy', $st6))
            ->assertRedirect($this->indexUrl())
            ->assertSessionHasErrors(['hapus' => 'Data sudah dipakai pada 1 aturan strategi dan hanya dapat dinonaktifkan.']);

        $this->assertDatabaseHas('strategi_pembelajaran', ['id' => $st6->id]);
        $this->assertSame(1, AturanStrategi::where('strategi_alternatif_id', $st6->id)->count());
    }

    public function test_hapus_kelompok_kecil_yang_dipakai_observasi_ditolak(): void
    {
        $this->actingAs($this->data['admin'])
            ->delete(route('data-master.strategi.destroy', $this->data['st2']))
            ->assertSessionHasErrors(['hapus' => 'Data sudah dipakai pada 1 observasi dan hanya dapat dinonaktifkan.']);

        $this->assertDatabaseHas('strategi_pembelajaran', ['id' => $this->data['st2']->id]);
    }

    public function test_hapus_strategi_yang_belum_dipakai_berhasil(): void
    {
        $baru = StrategiPembelajaran::factory()->create(['nama' => 'Demonstrasi']);

        $this->actingAs($this->data['admin'])
            ->delete(route('data-master.strategi.destroy', $baru))
            ->assertSessionHasNoErrors()
            ->assertSessionHas('status', 'Strategi Demonstrasi berhasil dihapus.');

        $this->assertDatabaseMissing('strategi_pembelajaran', ['id' => $baru->id]);
    }

    public function test_tc_02_19_tamu_diarahkan_ke_login(): void
    {
        $st6 = $this->data['st6'];

        $this->get($this->indexUrl())->assertRedirect('/login');
        $this->post(route('data-master.strategi.store'), ['nama' => 'Tutor sebaya'])->assertRedirect('/login');
        $this->patch(route('data-master.strategi.nonaktifkan', $st6))->assertRedirect('/login');
        $this->delete(route('data-master.strategi.destroy', $st6))->assertRedirect('/login');

        $this->assertDatabaseMissing('strategi_pembelajaran', ['nama' => 'Tutor sebaya']);
        $this->assertDatabaseHas('strategi_pembelajaran', ['id' => $st6->id, 'aktif' => true]);
    }
}
