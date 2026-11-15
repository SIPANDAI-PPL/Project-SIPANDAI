<?php

namespace Tests\Feature\Admin;

use App\Models\Kelas;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class KelasTest extends TestCase
{
    use RefreshDatabase;

    private User $admin;

    private User $guruMia;

    private User $guruAyu;

    protected function setUp(): void
    {
        parent::setUp();

        $this->admin = User::factory()->admin()->create(['nama' => 'Admin SIPANDAI']);
        $this->guruMia = User::factory()->create(['nama' => 'Mia Stacia']);
        $this->guruAyu = User::factory()->create(['nama' => 'Ayu Rizki']);
    }

    /**
     * @param  array<string, mixed>  $ubah
     * @return array<string, mixed>
     */
    private function isianKelas(array $ubah = []): array
    {
        return array_merge([
            'nama_kelas' => 'Kelas Ceria',
            'kelompok' => 'TK A',
            'tahun_ajaran' => '2026/2027',
            'guru_id' => $this->guruMia->id,
        ], $ubah);
    }

    /**
     * @param  array<string, mixed>  $atribut
     */
    private function buatKelas(array $atribut = []): Kelas
    {
        return Kelas::factory()->create(array_merge([
            'guru_id' => $this->guruMia->id,
            'nama_kelas' => 'Melati',
            'kelompok' => 'TK A',
            'tahun_ajaran' => '2026/2027',
        ], $atribut));
    }

    // ---------- Otorisasi ----------

    public function test_tc_03_01_admin_dapat_melihat_halaman_kelola_kelas(): void
    {
        $this->buatKelas();
        $this->buatKelas(['guru_id' => $this->guruAyu->id, 'nama_kelas' => 'Cendrawasih', 'kelompok' => 'TK B']);

        $this->actingAs($this->admin)
            ->get('/classes?tahun_ajaran=2026/2027')
            ->assertOk()
            ->assertViewIs('admin.kelas.index')
            ->assertSee('Kelola Kelas dan Penugasan Guru')
            ->assertSee('Melati')
            ->assertSee('Cendrawasih')
            ->assertSee('Mia Stacia')
            ->assertSee('Ayu Rizki')
            // Data dummy lama tidak boleh tampil lagi.
            ->assertDontSee('Kelas Pelangi')
            ->assertDontSee('Dina Nugraha');

        $this->assertSame(url('/classes'), route('classes.index'));
    }

    public function test_tc_03_02_guru_tidak_dapat_mengakses_halaman_kelola_kelas(): void
    {
        $kelas = $this->buatKelas();

        $this->actingAs($this->guruMia)->get('/classes')->assertForbidden();
        $this->actingAs($this->guruMia)->post('/classes', $this->isianKelas(['nama_kelas' => 'Baru']))->assertForbidden();
        $this->actingAs($this->guruMia)->put(route('classes.update', $kelas), $this->isianKelas())->assertForbidden();
        $this->actingAs($this->guruMia)
            ->patch(route('classes.guru-pengampu', $kelas), ['guru_id' => $this->guruAyu->id])
            ->assertForbidden();

        $this->assertDatabaseCount('kelas', 1);
        $this->assertDatabaseHas('kelas', ['id' => $kelas->id, 'nama_kelas' => 'Melati', 'guru_id' => $this->guruMia->id]);
    }

    public function test_tamu_dialihkan_ke_login(): void
    {
        $kelas = $this->buatKelas();

        $this->get('/classes')->assertRedirect(route('login'));
        $this->post('/classes', $this->isianKelas())->assertRedirect(route('login'));
        $this->put(route('classes.update', $kelas), $this->isianKelas())->assertRedirect(route('login'));
        $this->patch(route('classes.guru-pengampu', $kelas), ['guru_id' => $this->guruAyu->id])->assertRedirect(route('login'));
    }

    // ---------- Read dan filter ----------

    public function test_filter_tahun_ajaran_hanya_menampilkan_kelas_tahun_terpilih(): void
    {
        $this->buatKelas(['nama_kelas' => 'Melati', 'tahun_ajaran' => '2026/2027']);
        $this->buatKelas(['nama_kelas' => 'Anggrek', 'tahun_ajaran' => '2025/2026']);

        $this->actingAs($this->admin)
            ->get('/classes?tahun_ajaran=2025/2026')
            ->assertOk()
            ->assertViewHas('tahunAjaran', '2025/2026')
            ->assertSee('Anggrek')
            ->assertDontSee('Melati');

        $this->actingAs($this->admin)
            ->get('/classes?tahun_ajaran=2026/2027')
            ->assertSee('Melati')
            ->assertDontSee('Anggrek');
    }

    public function test_filter_tahun_ajaran_tidak_dikenal_kembali_ke_tahun_berjalan(): void
    {
        $this->travelTo('2026-11-16');

        $this->actingAs($this->admin)
            ->get('/classes?tahun_ajaran=abc')
            ->assertOk()
            ->assertViewHas('tahunAjaran', '2026/2027')
            ->assertViewHas('daftarTahunAjaran', fn ($daftar) => $daftar->contains('2026/2027'));
    }

    public function test_daftar_guru_untuk_penugasan_hanya_guru_aktif(): void
    {
        User::factory()->nonaktif()->create(['nama' => 'Guru Cuti']);

        $this->actingAs($this->admin)
            ->get('/classes')
            ->assertViewHas('daftarGuruAktif', function ($daftar) {
                return $daftar->pluck('nama')->sort()->values()->all() === ['Ayu Rizki', 'Mia Stacia'];
            });
    }

    // ---------- Create ----------

    public function test_tc_03_03_admin_dapat_menambah_data_kelas_dan_tahun_ajaran(): void
    {
        $this->actingAs($this->admin)
            ->post('/classes', $this->isianKelas())
            ->assertRedirect(route('classes.index', ['tahun_ajaran' => '2026/2027']))
            ->assertSessionHasNoErrors()
            ->assertSessionHas('status', 'Kelas Kelas Ceria berhasil disimpan.');

        $this->assertDatabaseHas('kelas', [
            'nama_kelas' => 'Kelas Ceria',
            'kelompok' => 'TK A',
            'tahun_ajaran' => '2026/2027',
            'guru_id' => $this->guruMia->id,
        ]);

        $this->actingAs($this->admin)
            ->get('/classes?tahun_ajaran=2026/2027')
            ->assertSee('Kelas Ceria');
    }

    public function test_nama_kelas_sama_boleh_di_kelompok_atau_tahun_ajaran_lain(): void
    {
        $this->buatKelas($this->isianKelas());

        $this->actingAs($this->admin)
            ->post('/classes', $this->isianKelas(['kelompok' => 'TK B']))
            ->assertSessionHasNoErrors();

        $this->actingAs($this->admin)
            ->post('/classes', $this->isianKelas(['tahun_ajaran' => '2027/2028']))
            ->assertSessionHasNoErrors();

        $this->assertDatabaseCount('kelas', 3);
    }

    // ---------- Validasi gagal ----------

    public function test_tc_03_05_validasi_gagal_jika_form_kelas_kosong(): void
    {
        $this->actingAs($this->admin)
            ->from('/classes')
            ->post('/classes', [
                '_form' => 'tambah',
                'nama_kelas' => '',
                'kelompok' => '',
                'tahun_ajaran' => '',
                'guru_id' => '',
            ])
            ->assertRedirect('/classes')
            ->assertSessionHasErrors([
                'nama_kelas' => 'Nama kelas wajib diisi.',
                'kelompok' => 'Kelompok wajib dipilih.',
                'tahun_ajaran' => 'Tahun ajaran wajib diisi.',
                'guru_id' => 'Pilih satu guru pengampu.',
            ]);

        $this->assertDatabaseCount('kelas', 0);

        $this->actingAs($this->admin)
            ->get('/classes')
            ->assertSee('Nama kelas wajib diisi.')
            ->assertSee('Kelompok wajib dipilih.')
            ->assertSee('Pilih satu guru pengampu.');
    }

    public function test_format_tahun_ajaran_tidak_valid_ditolak(): void
    {
        $pesan = 'Tahun ajaran harus berformat YYYY/YYYY dengan tahun berurutan, contoh 2026/2027.';

        foreach (['2026-2027', '26/27', '2026/2028', '2027/2026', 'abcd/efgh'] as $tahun) {
            $this->actingAs($this->admin)
                ->post('/classes', $this->isianKelas(['tahun_ajaran' => $tahun]))
                ->assertSessionHasErrors(['tahun_ajaran' => $pesan]);
        }

        $this->assertDatabaseCount('kelas', 0);
    }

    public function test_kelompok_di_luar_tk_a_dan_tk_b_ditolak(): void
    {
        $this->actingAs($this->admin)
            ->post('/classes', $this->isianKelas(['kelompok' => 'TK C']))
            ->assertSessionHasErrors(['kelompok' => 'Kelompok tidak valid.']);

        $this->assertDatabaseCount('kelas', 0);
    }

    public function test_kombinasi_kelas_kelompok_tahun_ajaran_dobel_ditolak(): void
    {
        $this->buatKelas($this->isianKelas());

        $this->actingAs($this->admin)
            ->post('/classes', $this->isianKelas(['guru_id' => $this->guruAyu->id, 'nama_kelas' => '  Kelas Ceria ']))
            ->assertSessionHasErrors(['nama_kelas' => 'Kelas dengan nama, kelompok, dan tahun ajaran ini sudah ada.']);

        $this->assertDatabaseCount('kelas', 1);
    }

    public function test_guru_nonaktif_atau_admin_tidak_dapat_ditugaskan(): void
    {
        $guruNonaktif = User::factory()->nonaktif()->create();

        $this->actingAs($this->admin)
            ->post('/classes', $this->isianKelas(['guru_id' => $guruNonaktif->id]))
            ->assertSessionHasErrors(['guru_id' => 'Guru pengampu tidak valid atau sudah nonaktif.']);

        $this->actingAs($this->admin)
            ->post('/classes', $this->isianKelas(['guru_id' => $this->admin->id]))
            ->assertSessionHasErrors(['guru_id' => 'Guru pengampu tidak valid atau sudah nonaktif.']);

        $kelas = $this->buatKelas();

        foreach ([$guruNonaktif->id, $this->admin->id, 99999] as $guruId) {
            $this->actingAs($this->admin)
                ->patch(route('classes.guru-pengampu', $kelas), ['guru_id' => $guruId])
                ->assertSessionHasErrors('guru_id', null, 'penugasan');
        }

        $this->assertSame($this->guruMia->id, $kelas->fresh()->guru_id);
        $this->assertDatabaseCount('kelas', 1);
    }

    public function test_galat_penugasan_terpisah_dari_form_tambah_kelas(): void
    {
        $kelas = $this->buatKelas();

        $this->actingAs($this->admin)
            ->from('/classes')
            ->patch(route('classes.guru-pengampu', $kelas), ['guru_id' => '', '_kelas_id' => $kelas->id])
            ->assertRedirect('/classes')
            ->assertSessionHasErrors(['guru_id' => 'Pilih satu guru pengampu.'], null, 'penugasan')
            ->assertSessionDoesntHaveErrors('guru_id');

        // Galat penugasan tampil di dialog ganti guru, bukan di form tambah kelas.
        $this->actingAs($this->admin)
            ->get('/classes')
            ->assertSee('data-galat-penugasan', false)
            ->assertDontSee('data-galat-form="guru_id"', false)
            ->assertSee('Pilih satu guru pengampu.');
    }

    // ---------- Update ----------

    public function test_admin_dapat_mengubah_data_kelas_dan_tahun_ajaran(): void
    {
        $kelas = $this->buatKelas();

        $this->actingAs($this->admin)
            ->put(route('classes.update', $kelas), [
                'nama_kelas' => 'Melati Ceria',
                'kelompok' => 'TK B',
                'tahun_ajaran' => '2027/2028',
            ])
            ->assertRedirect(route('classes.index', ['tahun_ajaran' => '2027/2028']))
            ->assertSessionHasNoErrors()
            ->assertSessionHas('status', 'Kelas Melati Ceria berhasil diperbarui.');

        $this->assertDatabaseHas('kelas', [
            'id' => $kelas->id,
            'nama_kelas' => 'Melati Ceria',
            'kelompok' => 'TK B',
            'tahun_ajaran' => '2027/2028',
            // Ubah data kelas tidak mengganti guru pengampu.
            'guru_id' => $this->guruMia->id,
        ]);
        $this->assertDatabaseCount('kelas', 1);
    }

    public function test_ubah_kelas_tanpa_perubahan_nama_tidak_dianggap_dobel(): void
    {
        $kelas = $this->buatKelas();

        $this->actingAs($this->admin)
            ->put(route('classes.update', $kelas), [
                'nama_kelas' => 'Melati',
                'kelompok' => 'TK A',
                'tahun_ajaran' => '2026/2027',
            ])
            ->assertSessionHasNoErrors();
    }

    public function test_ubah_kelas_menjadi_kombinasi_dobel_atau_tidak_valid_ditolak(): void
    {
        $this->buatKelas(['nama_kelas' => 'Melati']);
        $kelas = $this->buatKelas(['nama_kelas' => 'Mawar']);

        $this->actingAs($this->admin)
            ->from('/classes')
            ->put(route('classes.update', $kelas), [
                '_form' => 'ubah',
                '_kelas_id' => $kelas->id,
                'nama_kelas' => 'Melati',
                'kelompok' => 'TK A',
                'tahun_ajaran' => '2026/2027',
            ])
            ->assertRedirect('/classes')
            ->assertSessionHasErrors(['nama_kelas' => 'Kelas dengan nama, kelompok, dan tahun ajaran ini sudah ada.']);

        $this->actingAs($this->admin)
            ->put(route('classes.update', $kelas), ['nama_kelas' => '', 'kelompok' => 'TK Z', 'tahun_ajaran' => '2026/2026'])
            ->assertSessionHasErrors(['nama_kelas', 'kelompok', 'tahun_ajaran']);

        $this->assertSame('Mawar', $kelas->fresh()->nama_kelas);
    }

    public function test_tc_03_04_admin_dapat_menugaskan_guru_pengampu_pada_kelas(): void
    {
        $this->actingAs($this->admin)->post('/classes', $this->isianKelas());

        $kelas = Kelas::where('nama_kelas', 'Kelas Ceria')->firstOrFail();
        $this->assertTrue($kelas->guru->is($this->guruMia));
        $this->assertTrue($this->guruMia->kelasDiampu()->whereKey($kelas->id)->exists());

        $this->actingAs($this->admin)
            ->patch(route('classes.guru-pengampu', $kelas), ['guru_id' => $this->guruAyu->id])
            ->assertRedirect(route('classes.index', ['tahun_ajaran' => '2026/2027']))
            ->assertSessionHasNoErrors()
            ->assertSessionHas('status', 'Guru pengampu Kelas Ceria berhasil diperbarui.');

        $kelas->refresh();
        $this->assertTrue($kelas->guru->is($this->guruAyu));
        $this->assertFalse($this->guruMia->kelasDiampu()->exists());
        $this->assertTrue($this->guruAyu->kelasDiampu()->whereKey($kelas->id)->exists());
        $this->assertSame('/classes/'.$kelas->id.'/guru-pengampu', parse_url(route('classes.guru-pengampu', $kelas), PHP_URL_PATH));
    }

    public function test_kelas_tidak_ditemukan_menghasilkan_404(): void
    {
        $this->actingAs($this->admin)
            ->patch('/classes/99999/guru-pengampu', ['guru_id' => $this->guruAyu->id])
            ->assertNotFound();

        $this->actingAs($this->admin)
            ->put('/classes/99999', $this->isianKelas())
            ->assertNotFound();
    }
}
