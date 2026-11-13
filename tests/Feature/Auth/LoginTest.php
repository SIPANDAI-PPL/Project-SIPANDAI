<?php

namespace Tests\Feature\Auth;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Tests\TestCase;

class LoginTest extends TestCase
{
    use RefreshDatabase;

    public function test_menampilkan_halaman_login(): void
    {
        $this->get('/login')
            ->assertOk()
            ->assertSee('Email atau nama pengguna')
            ->assertSee('Kata sandi');
    }

    public function test_login_dengan_nama_pengguna(): void
    {
        $guru = User::factory()->create([
            'nama_pengguna' => 'miastacia',
            'kata_sandi' => 'GuruMia#2026',
        ]);

        $this->post('/login', [
            'login' => 'miastacia',
            'kata_sandi' => 'GuruMia#2026',
        ])->assertRedirect('/guru/dashboard');

        $this->assertAuthenticatedAs($guru);
    }

    public function test_login_dengan_email(): void
    {
        $guru = User::factory()->create([
            'email' => 'mia.stacia@tkceria.sch.id',
            'kata_sandi' => 'GuruMia#2026',
        ]);

        $this->post('/login', [
            'login' => 'mia.stacia@tkceria.sch.id',
            'kata_sandi' => 'GuruMia#2026',
        ])->assertRedirect('/guru/dashboard');

        $this->assertAuthenticatedAs($guru);
    }

    public function test_admin_diarahkan_ke_dashboard_admin_setelah_login(): void
    {
        User::factory()->admin()->create([
            'nama_pengguna' => 'admin',
            'kata_sandi' => 'Admin#2026',
        ]);

        $this->post('/login', [
            'login' => 'admin',
            'kata_sandi' => 'Admin#2026',
        ])->assertRedirect('/admin/dashboard');
    }

    public function test_guru_diarahkan_ke_dashboard_guru_setelah_login(): void
    {
        User::factory()->create([
            'nama_pengguna' => 'miastacia',
            'kata_sandi' => 'GuruMia#2026',
        ]);

        $this->post('/login', [
            'login' => 'miastacia',
            'kata_sandi' => 'GuruMia#2026',
        ])->assertRedirect('/guru/dashboard');
    }

    public function test_guru_membuka_dashboard_admin_mendapat_403(): void
    {
        $this->actingAs(User::factory()->create())
            ->get('/admin/dashboard')
            ->assertForbidden();
    }

    public function test_admin_membuka_dashboard_guru_mendapat_403(): void
    {
        $this->actingAs(User::factory()->admin()->create())
            ->get('/guru/dashboard')
            ->assertForbidden();
    }

    public function test_tamu_membuka_dashboard_admin_diarahkan_ke_login(): void
    {
        $this->get('/admin/dashboard')->assertRedirect('/login');
    }

    public function test_tamu_membuka_dashboard_guru_diarahkan_ke_login(): void
    {
        $this->get('/guru/dashboard')->assertRedirect('/login');
    }

    public function test_dashboard_mengalihkan_berdasarkan_peran(): void
    {
        $this->actingAs(User::factory()->admin()->create())
            ->get('/dashboard')
            ->assertRedirect('/admin/dashboard');

        $this->actingAs(User::factory()->create())
            ->get('/dashboard')
            ->assertRedirect('/guru/dashboard');
    }

    public function test_setiap_peran_dapat_membuka_dashboardnya(): void
    {
        $this->actingAs(User::factory()->admin()->create(['nama' => 'Admin SIPANDAI']))
            ->get('/admin/dashboard')
            ->assertOk()
            ->assertSee('Selamat datang, Admin SIPANDAI');

        $this->actingAs(User::factory()->create(['nama' => 'Mia Stacia']))
            ->get('/guru/dashboard')
            ->assertOk()
            ->assertSee('Selamat datang, Mia Stacia');
    }

    public function test_login_dengan_kata_sandi_salah_ditolak(): void
    {
        User::factory()->create(['nama_pengguna' => 'miastacia']);

        $this->from('/login')->post('/login', [
            'login' => 'miastacia',
            'kata_sandi' => 'salah-sandi',
        ])
            ->assertRedirect('/login')
            ->assertSessionHasErrors(['login' => 'Email/nama pengguna atau kata sandi salah.']);

        $this->assertGuest();
    }

    public function test_email_tidak_dicocokkan_dengan_kolom_nama_pengguna(): void
    {
        User::factory()->create([
            'nama_pengguna' => 'tamu@tkceria.sch.id',
            'email' => 'lain@tkceria.sch.id',
            'kata_sandi' => 'GuruMia#2026',
        ]);

        $this->post('/login', [
            'login' => 'tamu@tkceria.sch.id',
            'kata_sandi' => 'GuruMia#2026',
        ])->assertSessionHasErrors(['login' => 'Email/nama pengguna atau kata sandi salah.']);

        $this->assertGuest();
    }

    public function test_login_dengan_field_kosong_menampilkan_pesan_wajib_diisi(): void
    {
        $this->post('/login', [])
            ->assertSessionHasErrors([
                'login' => 'Email atau nama pengguna wajib diisi.',
                'kata_sandi' => 'Kata sandi wajib diisi.',
            ]);

        $this->assertGuest();
    }

    public function test_tc_01_15_akun_nonaktif_tidak_dapat_login(): void
    {
        $guru = User::factory()->nonaktif()->create([
            'nama_pengguna' => 'dinanugraha',
            'kata_sandi' => 'GuruDina#2026',
        ]);

        $this->post('/login', [
            'login' => 'dinanugraha',
            'kata_sandi' => 'GuruDina#2026',
        ])->assertSessionHasErrors(['login' => 'Akun tidak aktif, hubungi admin.']);

        $this->assertGuest();
        $this->assertDatabaseHas('users', ['id' => $guru->id, 'aktif' => false]);
    }

    public function test_akun_nonaktif_dengan_kata_sandi_salah_mendapat_pesan_kredensial(): void
    {
        User::factory()->nonaktif()->create(['nama_pengguna' => 'dinanugraha']);

        $this->post('/login', [
            'login' => 'dinanugraha',
            'kata_sandi' => 'salah-sandi',
        ])->assertSessionHasErrors(['login' => 'Email/nama pengguna atau kata sandi salah.']);
    }

    public function test_tc_01_18_login_dengan_kata_sandi_lama_setelah_reset_ditolak(): void
    {
        $admin = User::factory()->admin()->create();
        $guru = User::factory()->create([
            'nama_pengguna' => 'ayurizki',
            'kata_sandi' => 'SandiLama#2026',
        ]);

        $this->actingAs($admin)
            ->post("/admin/guru/{$guru->id}/reset-kata-sandi")
            ->assertRedirect('/admin/guru');

        $sandiSementara = session('sandi_sementara')['kata_sandi'];

        $this->post('/logout');
        $this->assertGuest();

        $this->post('/login', [
            'login' => 'ayurizki',
            'kata_sandi' => 'SandiLama#2026',
        ])->assertSessionHasErrors(['login' => 'Email/nama pengguna atau kata sandi salah.']);

        $this->assertGuest();

        $this->post('/login', [
            'login' => 'ayurizki',
            'kata_sandi' => $sandiSementara,
        ])->assertRedirect('/ganti-kata-sandi');

        $this->assertAuthenticatedAs($guru);
    }

    public function test_kata_sandi_tersimpan_dalam_bentuk_hash(): void
    {
        $guru = User::factory()->create(['kata_sandi' => 'GuruMia#2026']);

        $this->assertNotSame('GuruMia#2026', $guru->getAuthPassword());
        $this->assertTrue(Hash::check('GuruMia#2026', $guru->getAuthPassword()));
    }

    public function test_login_dibatasi_setelah_lima_percobaan_gagal(): void
    {
        User::factory()->create([
            'nama_pengguna' => 'miastacia',
            'kata_sandi' => 'GuruMia#2026',
        ]);

        for ($i = 0; $i < 5; $i++) {
            $this->post('/login', [
                'login' => 'miastacia',
                'kata_sandi' => 'salah-sandi',
            ]);
        }

        $respons = $this->post('/login', [
            'login' => 'miastacia',
            'kata_sandi' => 'GuruMia#2026',
        ]);

        $respons->assertSessionHasErrors('login');
        $this->assertStringContainsString(
            'Terlalu banyak percobaan',
            session('errors')->first('login'),
        );
        $this->assertGuest();
    }

    public function test_lima_percobaan_gagal_campuran_email_dan_nama_pengguna_mengunci_akun(): void
    {
        User::factory()->create([
            'nama_pengguna' => 'miastacia',
            'email' => 'mia.stacia@tkceria.sch.id',
            'kata_sandi' => 'GuruMia#2026',
        ]);

        foreach (['miastacia', 'mia.stacia@tkceria.sch.id', 'miastacia', 'mia.stacia@tkceria.sch.id', 'miastacia'] as $login) {
            $this->post('/login', [
                'login' => $login,
                'kata_sandi' => 'salah-sandi',
            ]);
        }

        $respons = $this->post('/login', [
            'login' => 'mia.stacia@tkceria.sch.id',
            'kata_sandi' => 'GuruMia#2026',
        ]);

        $respons->assertSessionHasErrors('login');
        $this->assertStringContainsString(
            'Terlalu banyak percobaan',
            session('errors')->first('login'),
        );
        $this->assertGuest();
    }

    public function test_tamu_membuka_dashboard_admin_lalu_login_sebagai_guru_diarahkan_ke_dashboard_guru(): void
    {
        User::factory()->create([
            'nama_pengguna' => 'miastacia',
            'kata_sandi' => 'GuruMia#2026',
        ]);

        $this->get('/admin/dashboard')->assertRedirect('/login');

        $this->post('/login', [
            'login' => 'miastacia',
            'kata_sandi' => 'GuruMia#2026',
        ])->assertRedirect('/guru/dashboard');
    }

    public function test_pengguna_belum_login_diarahkan_ke_halaman_login(): void
    {
        $this->get('/dashboard')->assertRedirect('/login');
    }

    public function test_pengguna_yang_sudah_login_tidak_bisa_membuka_halaman_login(): void
    {
        $this->actingAs(User::factory()->create())
            ->get('/login')
            ->assertRedirect('/dashboard');
    }

    public function test_logout_mengakhiri_sesi(): void
    {
        $this->actingAs(User::factory()->create())
            ->post('/logout')
            ->assertRedirect('/login');

        $this->assertGuest();
    }
}
