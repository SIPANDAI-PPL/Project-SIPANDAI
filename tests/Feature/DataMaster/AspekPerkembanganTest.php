<?php

namespace Tests\Feature\DataMaster;

use App\Models\AspekPerkembangan;
use App\Models\AturanStrategi;
use App\Models\Observasi;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class AspekPerkembanganTest extends TestCase
{
    use RefreshDatabase;

    private User $admin;

    private AspekPerkembangan $bahasa;

    private AspekPerkembangan $seni;

    private AspekPerkembangan $kemandirian;

    protected function setUp(): void
    {
        parent::setUp();

        $this->admin = User::factory()->admin()->create(['nama' => 'Admin SIPANDAI']);

        // AS4 Bahasa: dipakai 2 observasi dan 1 aturan.
        $this->bahasa = AspekPerkembangan::factory()->create([
            'nama' => 'Bahasa',
            'deskripsi' => 'Kemampuan memahami bahasa reseptif dan keaksaraan awal.',
        ]);
        Observasi::factory()->count(2)->create(['aspek_id' => $this->bahasa->id]);
        AturanStrategi::factory()->create(['aspek_id' => $this->bahasa->id]);

        // AS6 Seni dan AS7 Kemandirian: belum dipakai.
        $this->seni = AspekPerkembangan::factory()->create([
            'nama' => 'Seni',
            'deskripsi' => 'Eksplorasi estetika.',
        ]);
        $this->kemandirian = AspekPerkembangan::factory()->create([
            'nama' => 'Kemandirian',
            'deskripsi' => 'Inisiatif merawat diri.',
        ]);
    }

    public function test_tc_04_01_menampilkan_daftar_aspek_dengan_jumlah_pemakaian(): void
    {
        $this->actingAs($this->admin)
            ->get(route('data-master.aspek.index'))
            ->assertOk()
            ->assertSee('Daftar Aspek Perkembangan')
            ->assertSee('3 Aspek')
            ->assertSeeInOrder(['Bahasa', 'Seni', 'Kemandirian'])
            ->assertSee('Kemampuan memahami bahasa reseptif dan keaksaraan awal.')
            ->assertSee('2 observasi · 1 aturan')
            ->assertSee('Belum dipakai');
    }

    public function test_tc_04_01_pencarian_aspek(): void
    {
        $this->actingAs($this->admin)
            ->get(route('data-master.aspek.index', ['q' => 'estetika']))
            ->assertOk()
            ->assertViewHas('daftarAspek', fn ($daftar) => $daftar->pluck('id')->all() === [$this->seni->id]);
    }

    public function test_tc_04_02_menambah_aspek_baru(): void
    {
        $this->actingAs($this->admin)
            ->post(route('data-master.aspek.store'), [
                '_form' => 'tambah',
                'nama' => '  Kemampuan literasi awal  ',
                'deskripsi' => 'Mengenal huruf dan bunyi.',
            ])
            ->assertRedirect(route('data-master.aspek.index'))
            ->assertSessionHasNoErrors()
            ->assertSessionHas('status', 'Aspek Kemampuan literasi awal berhasil ditambahkan.');

        $this->assertDatabaseHas('aspek_perkembangan', [
            'nama' => 'Kemampuan literasi awal',
            'deskripsi' => 'Mengenal huruf dan bunyi.',
        ]);
    }

    public function test_tc_04_03_mengubah_deskripsi_aspek_seni(): void
    {
        $this->actingAs($this->admin)
            ->put(route('data-master.aspek.update', $this->seni), [
                '_form' => 'ubah',
                '_aspek_id' => $this->seni->id,
                'nama' => 'Seni',
                'deskripsi' => 'Eksplorasi estetika, musik, tari, dan karya seni rupa anak.',
            ])
            ->assertRedirect(route('data-master.aspek.index'))
            ->assertSessionHasNoErrors();

        $this->assertDatabaseHas('aspek_perkembangan', [
            'id' => $this->seni->id,
            'nama' => 'Seni',
            'deskripsi' => 'Eksplorasi estetika, musik, tari, dan karya seni rupa anak.',
        ]);
    }

    public function test_tc_04_04_menghapus_aspek_yang_belum_dipakai(): void
    {
        $this->actingAs($this->admin)
            ->delete(route('data-master.aspek.destroy', $this->kemandirian))
            ->assertRedirect(route('data-master.aspek.index'))
            ->assertSessionHasNoErrors()
            ->assertSessionHas('status', 'Aspek Kemandirian berhasil dihapus.');

        $this->assertDatabaseMissing('aspek_perkembangan', ['id' => $this->kemandirian->id]);
    }

    public function test_tc_04_14_nama_aspek_kosong_ditolak(): void
    {
        $this->actingAs($this->admin)
            ->post(route('data-master.aspek.store'), ['_form' => 'tambah', 'nama' => '   ', 'deskripsi' => ''])
            ->assertSessionHasErrors(['nama' => 'Nama wajib diisi.']);

        $this->assertDatabaseCount('aspek_perkembangan', 3);
    }

    public function test_tc_04_14_nama_aspek_duplikat_tanpa_beda_huruf_ditolak(): void
    {
        $this->actingAs($this->admin)
            ->post(route('data-master.aspek.store'), ['_form' => 'tambah', 'nama' => 'bahasa'])
            ->assertSessionHasErrors(['nama' => 'Nama aspek sudah terdaftar.']);

        $this->actingAs($this->admin)
            ->put(route('data-master.aspek.update', $this->seni), ['_form' => 'ubah', 'nama' => ' BAHASA '])
            ->assertSessionHasErrors(['nama' => 'Nama aspek sudah terdaftar.']);

        $this->assertDatabaseCount('aspek_perkembangan', 3);
        $this->assertDatabaseHas('aspek_perkembangan', ['id' => $this->seni->id, 'nama' => 'Seni']);
    }

    public function test_tc_04_15_menghapus_aspek_yang_sudah_dipakai_ditolak(): void
    {
        $this->actingAs($this->admin)
            ->delete(route('data-master.aspek.destroy', $this->bahasa))
            ->assertRedirect(route('data-master.aspek.index'))
            ->assertSessionHasErrors([
                'hapus' => 'Aspek sudah dipakai pada 2 observasi dan 1 aturan sehingga tidak dapat dihapus.',
            ]);

        $this->assertDatabaseHas('aspek_perkembangan', ['id' => $this->bahasa->id]);
    }

    public function test_guru_tidak_dapat_mengelola_aspek(): void
    {
        $guru = User::factory()->create();

        $this->actingAs($guru)->get(route('data-master.aspek.index'))->assertForbidden();
        $this->actingAs($guru)->post(route('data-master.aspek.store'), ['nama' => 'Baru'])->assertForbidden();
        $this->actingAs($guru)->delete(route('data-master.aspek.destroy', $this->kemandirian))->assertForbidden();

        $this->assertDatabaseHas('aspek_perkembangan', ['id' => $this->kemandirian->id]);
    }

    public function test_tamu_diarahkan_ke_login(): void
    {
        $this->get(route('data-master.aspek.index'))->assertRedirect('/login');
    }
}
