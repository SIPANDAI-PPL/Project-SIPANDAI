<?php

namespace Tests\Feature\DataMaster;

use App\Models\Aktivitas;
use App\Models\Observasi;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\Support\DataUjiDataMaster;
use Tests\TestCase;

class AktivitasTest extends TestCase
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
        return route('data-master.aktivitas.index', $parameter);
    }

    public function test_tc_02_01_menampilkan_daftar_aktivitas_dan_jumlah_pemakaian(): void
    {
        $respons = $this->actingAs($this->data['admin'])->get($this->indexUrl());

        $respons->assertOk()
            ->assertSee('Data Master')
            ->assertSeeInOrder(['Bercerita', 'Menyusun balok', 'Permainan gerak', 'Menggambar &amp; mewarnai', 'Bermain peran'], false)
            ->assertSee('5 Aktivitas')
            ->assertSee('2 observasi')
            ->assertSee('Belum dipakai')
            ->assertSee('Nonaktif');

        $respons->assertViewHas('daftar', function ($daftar) {
            $jumlah = collect($daftar->items())->pluck('observasi_count', 'id')->map(fn ($n) => (int) $n);

            return $jumlah[$this->data['ak1']->id] === 2
                && $jumlah[$this->data['ak2']->id] === 1
                && $jumlah[$this->data['ak3']->id] === 1
                && $jumlah[$this->data['ak4']->id] === 1
                && $jumlah[$this->data['ak5']->id] === 0;
        });
    }

    public function test_tc_02_03_mencari_aktivitas_balok_hanya_menampilkan_menyusun_balok(): void
    {
        $respons = $this->actingAs($this->data['admin'])->get($this->indexUrl(['q' => 'balok']));

        $respons->assertOk()
            ->assertSee('Menyusun balok')
            ->assertDontSee('Bercerita')
            ->assertDontSee('Permainan gerak')
            ->assertDontSee('Bermain peran')
            ->assertSee('1 Aktivitas');

        $respons->assertViewHas('daftar', fn ($daftar) => collect($daftar->items())->pluck('id')->all() === [$this->data['ak2']->id]);
    }

    public function test_tc_02_04_filter_status_nonaktif_hanya_menampilkan_menggambar_dan_mewarnai(): void
    {
        $respons = $this->actingAs($this->data['admin'])->get($this->indexUrl(['status' => 'nonaktif']));

        $respons->assertOk()
            ->assertSee('Menggambar &amp; mewarnai', false)
            ->assertDontSee('Bercerita')
            ->assertDontSee('Menyusun balok')
            ->assertDontSee('Bermain peran');

        $respons->assertViewHas('daftar', fn ($daftar) => collect($daftar->items())->pluck('id')->all() === [$this->data['ak4']->id]);
        $respons->assertViewHas('jumlah', ['semua' => 5, 'aktif' => 4, 'nonaktif' => 1]);
    }

    public function test_filter_status_tidak_dikenal_dianggap_semua(): void
    {
        $this->actingAs($this->data['admin'])
            ->get($this->indexUrl(['status' => 'apa-saja']))
            ->assertOk()
            ->assertViewHas('status', 'semua')
            ->assertViewHas('daftar', fn ($daftar) => $daftar->total() === 5);
    }

    public function test_tc_02_05_menambah_aktivitas_bernyanyi(): void
    {
        $this->actingAs($this->data['admin'])
            ->from($this->indexUrl())
            ->post(route('data-master.aktivitas.store'), [
                'nama' => '  Bernyanyi  ',
                'deskripsi' => 'Bernyanyi lagu anak bersama-sama.',
            ])
            ->assertRedirect($this->indexUrl())
            ->assertSessionHasNoErrors()
            ->assertSessionHas('status', 'Aktivitas Bernyanyi berhasil ditambahkan.');

        $this->assertDatabaseHas('aktivitas', [
            'nama' => 'Bernyanyi',
            'deskripsi' => 'Bernyanyi lagu anak bersama-sama.',
            'aktif' => true,
        ]);

        $this->get($this->indexUrl())->assertSee('Bernyanyi')->assertSee('6 Aktivitas');
    }

    public function test_tc_02_07_mengubah_deskripsi_menyusun_balok(): void
    {
        $ak2 = $this->data['ak2'];

        $this->actingAs($this->data['admin'])
            ->from($this->indexUrl())
            ->put(route('data-master.aktivitas.update', $ak2), [
                'nama' => 'Menyusun balok',
                'deskripsi' => 'Menyusun balok kayu menjadi menara dan bangunan sederhana.',
            ])
            ->assertRedirect($this->indexUrl())
            ->assertSessionHasNoErrors()
            ->assertSessionHas('status', 'Aktivitas Menyusun balok berhasil diperbarui.');

        $this->assertDatabaseHas('aktivitas', [
            'id' => $ak2->id,
            'nama' => 'Menyusun balok',
            'deskripsi' => 'Menyusun balok kayu menjadi menara dan bangunan sederhana.',
            'aktif' => true,
        ]);
    }

    public function test_tc_02_08_menonaktifkan_bercerita_observasi_tetap_ada(): void
    {
        $ak1 = $this->data['ak1'];

        $this->actingAs($this->data['admin'])
            ->patch(route('data-master.aktivitas.nonaktifkan', $ak1))
            ->assertRedirect($this->indexUrl())
            ->assertSessionHas('status', 'Aktivitas Bercerita dinonaktifkan.');

        $this->assertDatabaseHas('aktivitas', ['id' => $ak1->id, 'aktif' => false]);
        $this->assertSame(2, Observasi::where('aktivitas_id', $ak1->id)->count());
    }

    public function test_aktivitas_nonaktif_dapat_diaktifkan_kembali(): void
    {
        $ak4 = $this->data['ak4'];

        $this->actingAs($this->data['admin'])
            ->patch(route('data-master.aktivitas.aktifkan', $ak4))
            ->assertRedirect($this->indexUrl())
            ->assertSessionHas('status', 'Aktivitas Menggambar & mewarnai diaktifkan kembali.');

        $this->assertDatabaseHas('aktivitas', ['id' => $ak4->id, 'aktif' => true]);
    }

    public function test_tc_02_10_menghapus_bermain_peran_yang_belum_dipakai(): void
    {
        $ak5 = $this->data['ak5'];

        $this->actingAs($this->data['admin'])
            ->get($this->indexUrl())
            ->assertSee('dusk="aksi-hapus-'.$ak5->id.'"', false);

        $this->delete(route('data-master.aktivitas.destroy', $ak5))
            ->assertRedirect($this->indexUrl())
            ->assertSessionHasNoErrors()
            ->assertSessionHas('status', 'Aktivitas Bermain peran berhasil dihapus.');

        $this->assertDatabaseMissing('aktivitas', ['id' => $ak5->id]);
    }

    public function test_guru_dapat_membuka_daftar_aktivitas(): void
    {
        $this->actingAs($this->data['guru'])
            ->get($this->indexUrl())
            ->assertOk()
            ->assertSee('Aktivitas &amp; Strategi', false)
            ->assertSee('Bercerita');
    }

    public function test_tc_02_14_nama_kosong_ditolak(): void
    {
        $this->actingAs($this->data['admin']);

        foreach (['', '   '] as $nama) {
            $this->from($this->indexUrl())
                ->post(route('data-master.aktivitas.store'), ['nama' => $nama, 'deskripsi' => 'Tanpa nama.', '_form' => 'tambah'])
                ->assertRedirect($this->indexUrl())
                ->assertSessionHasErrors(['nama' => 'Nama wajib diisi.']);
        }

        $this->assertSame(5, Aktivitas::count());

        // Modal tambah terbuka lagi dengan pesan error.
        $this->get($this->indexUrl())
            ->assertSee('data-buka-otomatis', false)
            ->assertSee('Nama wajib diisi.');
    }

    public function test_tc_02_15_nama_bercerita_duplikat_ditolak_saat_tambah_dan_ubah(): void
    {
        $this->actingAs($this->data['admin']);

        $this->from($this->indexUrl())
            ->post(route('data-master.aktivitas.store'), ['nama' => 'bercerita', '_form' => 'tambah'])
            ->assertRedirect($this->indexUrl())
            ->assertSessionHasErrors(['nama' => 'Nama aktivitas sudah terdaftar.']);

        $this->assertSame(1, Aktivitas::where('nama', 'bercerita')->count());

        $ak2 = $this->data['ak2'];
        $this->from($this->indexUrl())
            ->put(route('data-master.aktivitas.update', $ak2), ['nama' => 'bercerita', '_form' => 'ubah', '_id' => $ak2->id])
            ->assertRedirect($this->indexUrl())
            ->assertSessionHasErrors(['nama' => 'Nama aktivitas sudah terdaftar.']);

        $this->assertDatabaseHas('aktivitas', ['id' => $ak2->id, 'nama' => 'Menyusun balok']);
    }

    public function test_ubah_nama_ke_nama_sendiri_dengan_huruf_berbeda_diterima(): void
    {
        $ak1 = $this->data['ak1'];

        $this->actingAs($this->data['admin'])
            ->put(route('data-master.aktivitas.update', $ak1), ['nama' => 'BERCERITA'])
            ->assertSessionHasNoErrors();

        $this->assertDatabaseHas('aktivitas', ['id' => $ak1->id, 'nama' => 'BERCERITA']);
    }

    public function test_tc_02_16_nama_101_karakter_ditolak(): void
    {
        $this->actingAs($this->data['admin'])
            ->from($this->indexUrl())
            ->post(route('data-master.aktivitas.store'), ['nama' => str_repeat('a', 101)])
            ->assertSessionHasErrors(['nama' => 'Nama maksimal 100 karakter.']);

        $this->assertDatabaseMissing('aktivitas', ['nama' => str_repeat('a', 101)]);

        $this->post(route('data-master.aktivitas.store'), ['nama' => str_repeat('b', 100)])
            ->assertSessionHasNoErrors();

        $this->assertDatabaseHas('aktivitas', ['nama' => str_repeat('b', 100)]);
    }

    public function test_tc_02_17_hapus_bercerita_yang_dipakai_ditolak_server(): void
    {
        $ak1 = $this->data['ak1'];

        // Menu Hapus tidak ditampilkan untuk data yang sudah dipakai.
        $this->actingAs($this->data['admin'])
            ->get($this->indexUrl())
            ->assertDontSee('dusk="aksi-hapus-'.$ak1->id.'"', false);

        // Permintaan langsung ke server tetap ditolak.
        $this->delete(route('data-master.aktivitas.destroy', $ak1))
            ->assertRedirect($this->indexUrl())
            ->assertSessionHasErrors(['hapus' => 'Data sudah dipakai pada 2 observasi dan hanya dapat dinonaktifkan.']);

        $this->assertDatabaseHas('aktivitas', ['id' => $ak1->id, 'aktif' => true]);
        $this->assertSame(2, Observasi::where('aktivitas_id', $ak1->id)->count());
    }

    public function test_tc_02_19_tamu_diarahkan_ke_login(): void
    {
        $ak5 = $this->data['ak5'];

        $this->get($this->indexUrl())->assertRedirect('/login');
        $this->post(route('data-master.aktivitas.store'), ['nama' => 'Bernyanyi'])->assertRedirect('/login');
        $this->put(route('data-master.aktivitas.update', $ak5), ['nama' => 'Ubah'])->assertRedirect('/login');
        $this->patch(route('data-master.aktivitas.nonaktifkan', $ak5))->assertRedirect('/login');
        $this->patch(route('data-master.aktivitas.aktifkan', $ak5))->assertRedirect('/login');
        $this->delete(route('data-master.aktivitas.destroy', $ak5))->assertRedirect('/login');

        $this->assertDatabaseMissing('aktivitas', ['nama' => 'Bernyanyi']);
        $this->assertDatabaseHas('aktivitas', ['id' => $ak5->id, 'nama' => 'Bermain peran', 'aktif' => true]);
    }
}
