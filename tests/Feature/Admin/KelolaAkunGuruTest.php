<?php

namespace Tests\Feature\Admin;

use App\Models\Kelas;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Tests\TestCase;

class KelolaAkunGuruTest extends TestCase
{
    use RefreshDatabase;

    private User $admin;

    private User $mia;

    private User $ayu;

    private User $dina;

    private User $andi;

    protected function setUp(): void
    {
        parent::setUp();

        // Data uji PBI-1.
        $this->admin = User::factory()->admin()->create([
            'nama' => 'Admin SIPANDAI',
            'nama_pengguna' => 'admin',
            'email' => 'admin@tkceria.sch.id',
        ]);

        $this->mia = User::factory()->create([
            'nama' => 'Mia Stacia',
            'nama_pengguna' => 'miastacia',
            'email' => 'mia.stacia@tkceria.sch.id',
            'nip' => '199203152021022001',
        ]);
        Kelas::factory()->create(['guru_id' => $this->mia->id, 'kelompok' => 'TK A', 'nama_kelas' => 'Melati']);

        $this->ayu = User::factory()->create([
            'nama' => 'Ayu Rizki',
            'nama_pengguna' => 'ayurizki',
            'email' => 'ayu.rizki@tkceria.sch.id',
            'nip' => '199408222022032002',
            'kata_sandi' => 'SandiLama#2026',
        ]);
        Kelas::factory()->create(['guru_id' => $this->ayu->id, 'kelompok' => 'TK B', 'nama_kelas' => 'Cendrawasih']);

        $this->dina = User::factory()->nonaktif()->create([
            'nama' => 'Dina Nugraha',
            'nama_pengguna' => 'dinanugraha',
            'email' => 'dina.nugraha@tkceria.sch.id',
            'nip' => '199011052019012003',
        ]);

        $this->andi = User::factory()->create([
            'nama' => 'Andi Pratama',
            'nama_pengguna' => 'andipratama',
            'email' => 'andi.pratama@tkceria.sch.id',
            'nip' => '199512012023011004',
        ]);
    }

    private function dataGuruBaru(array $ubah = []): array
    {
        return array_merge([
            'nama' => 'Rina Kartika',
            'nama_pengguna' => 'rinakartika',
            'email' => 'rina.kartika@tkceria.sch.id',
            'nip' => '',
        ], $ubah);
    }

    public function test_tc_01_01_menampilkan_daftar_guru_dengan_jumlah_status(): void
    {
        $this->actingAs($this->admin)
            ->get('/admin/guru')
            ->assertOk()
            ->assertSee('Mia Stacia')
            ->assertSee('Ayu Rizki')
            ->assertSee('Dina Nugraha')
            ->assertSee('Andi Pratama')
            ->assertDontSee('admin@tkceria.sch.id')
            ->assertSee('TK A - Melati')
            ->assertSee('TK B - Cendrawasih')
            ->assertSee('Tanpa Kelas')
            ->assertViewHas('jumlah', ['semua' => 4, 'aktif' => 3, 'nonaktif' => 1])
            ->assertSee('Semua (4)')
            ->assertSee('Aktif (3)')
            ->assertSee('Nonaktif (1)');
    }

    public function test_tc_01_02_mencari_guru_berdasarkan_nama_dan_nip(): void
    {
        $this->actingAs($this->admin)
            ->get('/admin/guru?q=mia')
            ->assertOk()
            ->assertSee('Mia Stacia')
            ->assertDontSee('Ayu Rizki')
            ->assertDontSee('Dina Nugraha')
            ->assertDontSee('Andi Pratama');

        $this->get('/admin/guru?q=199408')
            ->assertOk()
            ->assertSee('Ayu Rizki')
            ->assertDontSee('Mia Stacia')
            ->assertDontSee('Dina Nugraha')
            ->assertDontSee('Andi Pratama');
    }

    public function test_tc_01_03_tab_nonaktif_hanya_menampilkan_guru_nonaktif(): void
    {
        $this->actingAs($this->admin)
            ->get('/admin/guru?status=nonaktif')
            ->assertOk()
            ->assertSee('Dina Nugraha')
            ->assertDontSee('Mia Stacia')
            ->assertDontSee('Ayu Rizki')
            ->assertDontSee('Andi Pratama');
    }

    public function test_tc_01_04_menambah_akun_guru_valid(): void
    {
        $respons = $this->actingAs($this->admin)
            ->post('/admin/guru', $this->dataGuruBaru())
            ->assertRedirect('/admin/guru')
            ->assertSessionHasNoErrors()
            ->assertSessionHas('sandi_sementara');

        $sandi = session('sandi_sementara')['kata_sandi'];
        $this->assertSame(12, strlen($sandi));

        $rina = User::where('nama_pengguna', 'rinakartika')->firstOrFail();
        $this->assertSame('guru', $rina->peran);
        $this->assertTrue($rina->aktif);
        $this->assertTrue($rina->wajib_ganti_sandi);
        $this->assertNull($rina->nip);
        $this->assertNotSame($sandi, $rina->getAuthPassword());
        $this->assertTrue(Hash::check($sandi, $rina->getAuthPassword()));

        $respons->assertSessionHas('sandi_sementara.nama', 'Rina Kartika');
    }

    public function test_tc_01_05_mengubah_data_guru_tanpa_mengubah_kata_sandi(): void
    {
        $hashLama = $this->mia->getAuthPassword();

        $this->actingAs($this->admin)
            ->put("/admin/guru/{$this->mia->id}", [
                'nama' => 'Mia Stacia, S.Pd.',
                'nama_pengguna' => 'miastacia',
                'email' => 'mia.stacia@tkceria.sch.id',
                'nip' => '199203152021022001',
            ])
            ->assertRedirect('/admin/guru')
            ->assertSessionHasNoErrors()
            ->assertSessionMissing('sandi_sementara');

        $this->mia->refresh();
        $this->assertSame('Mia Stacia, S.Pd.', $this->mia->nama);
        $this->assertSame($hashLama, $this->mia->getAuthPassword());
    }

    public function test_tc_01_06_menonaktifkan_guru_tanpa_kelas(): void
    {
        $this->actingAs($this->admin)
            ->patch("/admin/guru/{$this->andi->id}/nonaktifkan")
            ->assertRedirect('/admin/guru')
            ->assertSessionHasNoErrors();

        $this->assertDatabaseHas('users', ['id' => $this->andi->id, 'aktif' => false]);
    }

    public function test_tc_01_07_mengaktifkan_kembali_guru_nonaktif(): void
    {
        $this->actingAs($this->admin)
            ->patch("/admin/guru/{$this->dina->id}/aktifkan")
            ->assertRedirect('/admin/guru')
            ->assertSessionHasNoErrors();

        $this->assertDatabaseHas('users', ['id' => $this->dina->id, 'aktif' => true]);
    }

    public function test_tc_01_08_reset_kata_sandi_guru(): void
    {
        $hashLama = $this->ayu->getAuthPassword();

        $this->actingAs($this->admin)
            ->post("/admin/guru/{$this->ayu->id}/reset-kata-sandi")
            ->assertRedirect('/admin/guru')
            ->assertSessionHas('sandi_sementara');

        $sandi = session('sandi_sementara')['kata_sandi'];
        $this->assertSame(12, strlen($sandi));

        $this->ayu->refresh();
        $this->assertNotSame($hashLama, $this->ayu->getAuthPassword());
        $this->assertTrue(Hash::check($sandi, $this->ayu->getAuthPassword()));
        $this->assertFalse(Hash::check('SandiLama#2026', $this->ayu->getAuthPassword()));
        $this->assertDatabaseHas('users', ['id' => $this->ayu->id, 'wajib_ganti_sandi' => 1]);
    }

    public function test_tc_01_10_field_wajib_kosong_ditolak(): void
    {
        $this->actingAs($this->admin)
            ->from('/admin/guru')
            ->post('/admin/guru', ['nama' => '', 'nama_pengguna' => '', 'email' => '', 'nip' => ''])
            ->assertRedirect('/admin/guru')
            ->assertSessionHasErrors([
                'nama' => 'Nama lengkap wajib diisi.',
                'nama_pengguna' => 'Nama pengguna wajib diisi.',
                'email' => 'Email wajib diisi.',
            ]);

        $this->assertSame(5, User::count());
    }

    public function test_tc_01_11_format_email_tidak_valid_ditolak(): void
    {
        $this->actingAs($this->admin)
            ->from('/admin/guru')
            ->post('/admin/guru', $this->dataGuruBaru(['email' => 'rina.kartika']))
            ->assertSessionHasErrors(['email' => 'Format email tidak valid.']);

        $this->assertDatabaseMissing('users', ['nama_pengguna' => 'rinakartika']);
    }

    public function test_tc_01_12_email_duplikat_ditolak(): void
    {
        $this->actingAs($this->admin)
            ->from('/admin/guru')
            ->post('/admin/guru', $this->dataGuruBaru(['email' => 'mia.stacia@tkceria.sch.id']))
            ->assertSessionHasErrors(['email' => 'Email sudah terdaftar.']);

        $this->assertDatabaseMissing('users', ['nama_pengguna' => 'rinakartika']);
    }

    public function test_tc_01_13_nama_pengguna_duplikat_ditolak(): void
    {
        $this->actingAs($this->admin)
            ->from('/admin/guru')
            ->post('/admin/guru', $this->dataGuruBaru(['nama_pengguna' => 'miastacia']))
            ->assertSessionHasErrors(['nama_pengguna' => 'Nama pengguna sudah terdaftar.']);

        $this->assertDatabaseMissing('users', ['email' => 'rina.kartika@tkceria.sch.id']);
    }

    public function test_ubah_menolak_email_dan_nama_pengguna_milik_guru_lain(): void
    {
        $this->actingAs($this->admin)
            ->from('/admin/guru')
            ->put("/admin/guru/{$this->mia->id}", [
                'nama' => 'Mia Stacia',
                'nama_pengguna' => 'ayurizki',
                'email' => 'ayu.rizki@tkceria.sch.id',
            ])
            ->assertSessionHasErrors([
                'nama_pengguna' => 'Nama pengguna sudah terdaftar.',
                'email' => 'Email sudah terdaftar.',
            ]);

        $this->assertSame('miastacia', $this->mia->fresh()->nama_pengguna);
    }

    public function test_tc_01_14_menonaktifkan_guru_yang_masih_mengampu_kelas_ditolak(): void
    {
        $this->actingAs($this->admin)
            ->from('/admin/guru')
            ->patch("/admin/guru/{$this->ayu->id}/nonaktifkan")
            ->assertRedirect('/admin/guru')
            ->assertSessionHasErrors([
                'nonaktifkan' => 'Guru masih mengampu 1 kelas (TK B - Cendrawasih). Alihkan kelasnya ke guru lain dulu.',
            ]);

        $this->assertDatabaseHas('users', ['id' => $this->ayu->id, 'aktif' => 1]);
        $this->assertDatabaseHas('kelas', ['guru_id' => $this->ayu->id]);
    }

    public function test_aksi_terhadap_akun_admin_ditolak(): void
    {
        $this->actingAs($this->admin)
            ->post("/admin/guru/{$this->admin->id}/reset-kata-sandi")
            ->assertNotFound();

        $this->patch("/admin/guru/{$this->admin->id}/nonaktifkan")->assertNotFound();
        $this->assertTrue($this->admin->fresh()->aktif);
    }

    public function test_tc_01_16_guru_membuka_kelola_akun_guru_mendapat_403(): void
    {
        $this->actingAs($this->mia)->get('/admin/guru')->assertForbidden();
        $this->post('/admin/guru', $this->dataGuruBaru())->assertForbidden();
        $this->post("/admin/guru/{$this->ayu->id}/reset-kata-sandi")->assertForbidden();

        $this->assertDatabaseMissing('users', ['nama_pengguna' => 'rinakartika']);
    }

    public function test_tc_01_17_tamu_diarahkan_ke_login(): void
    {
        $this->get('/admin/guru')->assertRedirect('/login');
        $this->post('/admin/guru', $this->dataGuruBaru())->assertRedirect('/login');
    }

    public function test_dashboard_admin_memuat_link_kelola_akun_guru(): void
    {
        $this->actingAs($this->admin)
            ->get('/admin/dashboard')
            ->assertOk()
            ->assertSee('Kelola Akun Guru')
            ->assertSee(route('admin.guru.index'));
    }
}
