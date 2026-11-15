<?php

namespace Tests\Feature\Auth;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Tests\TestCase;

class GantiKataSandiTest extends TestCase
{
    use RefreshDatabase;

    private const SANDI_SEMENTARA = 'Sementara#2026';

    private function guruWajibGanti(): User
    {
        return User::factory()->wajibGantiSandi()->create([
            'nama' => 'Ayu Rizki',
            'nama_pengguna' => 'ayurizki',
            'kata_sandi' => self::SANDI_SEMENTARA,
        ]);
    }

    public function test_tc_01_09_guru_login_dengan_kata_sandi_sementara_lalu_menggantinya(): void
    {
        $guru = $this->guruWajibGanti();
        $hashLama = $guru->getAuthPassword();

        $this->post('/login', [
            'login' => 'ayurizki',
            'kata_sandi' => self::SANDI_SEMENTARA,
        ])->assertRedirect('/ganti-kata-sandi');

        $this->get('/ganti-kata-sandi')
            ->assertOk()
            ->assertSee('Kata sandi baru')
            ->assertSee('Konfirmasi kata sandi baru');

        $this->put('/ganti-kata-sandi', [
            'kata_sandi_baru' => 'GuruAyu#2026',
            'kata_sandi_baru_confirmation' => 'GuruAyu#2026',
        ])
            ->assertRedirect('/guru/dashboard')
            ->assertSessionHas('status', 'Kata sandi berhasil diganti.');

        $guru->refresh();
        $this->assertFalse($guru->wajib_ganti_sandi);
        $this->assertNotSame($hashLama, $guru->getAuthPassword());
        $this->assertTrue(Hash::check('GuruAyu#2026', $guru->getAuthPassword()));

        $this->get('/guru/dashboard')
            ->assertOk()
            ->assertSee('Kata sandi berhasil diganti.');

        $this->post('/logout');
        $this->assertGuest();

        $this->post('/login', [
            'login' => 'ayurizki',
            'kata_sandi' => 'GuruAyu#2026',
        ])->assertRedirect('/guru/dashboard');

        $this->assertAuthenticatedAs($guru);
    }

    public function test_tc_01_19_guru_wajib_ganti_sandi_membuka_dashboard_diarahkan_ke_ganti_kata_sandi(): void
    {
        $this->actingAs($this->guruWajibGanti())
            ->get('/guru/dashboard')
            ->assertRedirect('/ganti-kata-sandi');
    }

    public function test_kata_sandi_baru_kurang_dari_delapan_karakter_ditolak(): void
    {
        $guru = $this->guruWajibGanti();

        $this->actingAs($guru)
            ->from('/ganti-kata-sandi')
            ->put('/ganti-kata-sandi', [
                'kata_sandi_baru' => 'Ayu#26',
                'kata_sandi_baru_confirmation' => 'Ayu#26',
            ])
            ->assertRedirect('/ganti-kata-sandi')
            ->assertSessionHasErrors(['kata_sandi_baru' => 'Kata sandi baru minimal 8 karakter.']);

        $this->assertTrue($guru->fresh()->wajib_ganti_sandi);
    }

    public function test_konfirmasi_kata_sandi_tidak_cocok_ditolak(): void
    {
        $guru = $this->guruWajibGanti();

        $this->actingAs($guru)
            ->from('/ganti-kata-sandi')
            ->put('/ganti-kata-sandi', [
                'kata_sandi_baru' => 'GuruAyu#2026',
                'kata_sandi_baru_confirmation' => 'GuruAyu#2027',
            ])
            ->assertRedirect('/ganti-kata-sandi')
            ->assertSessionHasErrors(['kata_sandi_baru' => 'Konfirmasi kata sandi baru tidak cocok.']);

        $this->assertTrue($guru->fresh()->wajib_ganti_sandi);
    }

    public function test_field_kosong_menampilkan_pesan_wajib_diisi(): void
    {
        $this->actingAs($this->guruWajibGanti())
            ->from('/ganti-kata-sandi')
            ->put('/ganti-kata-sandi', [])
            ->assertRedirect('/ganti-kata-sandi')
            ->assertSessionHasErrors(['kata_sandi_baru' => 'Kata sandi baru wajib diisi.']);
    }

    public function test_kata_sandi_baru_sama_dengan_sandi_sementara_ditolak(): void
    {
        $guru = $this->guruWajibGanti();

        $this->actingAs($guru)
            ->from('/ganti-kata-sandi')
            ->put('/ganti-kata-sandi', [
                'kata_sandi_baru' => self::SANDI_SEMENTARA,
                'kata_sandi_baru_confirmation' => self::SANDI_SEMENTARA,
            ])
            ->assertRedirect('/ganti-kata-sandi')
            ->assertSessionHasErrors(['kata_sandi_baru' => 'Kata sandi baru harus berbeda dari kata sandi sementara.']);

        $this->assertTrue($guru->fresh()->wajib_ganti_sandi);
    }

    public function test_logout_tetap_bisa_saat_wajib_ganti_sandi(): void
    {
        $this->actingAs($this->guruWajibGanti())
            ->post('/logout')
            ->assertRedirect('/login');

        $this->assertGuest();
    }

    public function test_tamu_membuka_ganti_kata_sandi_diarahkan_ke_login(): void
    {
        $this->get('/ganti-kata-sandi')->assertRedirect('/login');
        $this->put('/ganti-kata-sandi', [])->assertRedirect('/login');
    }

    public function test_tamu_tetap_bisa_membuka_halaman_login(): void
    {
        $this->get('/login')->assertOk();
    }

    public function test_admin_wajib_ganti_sandi_juga_diarahkan(): void
    {
        $admin = User::factory()->admin()->wajibGantiSandi()->create();

        $this->actingAs($admin)
            ->get('/admin/dashboard')
            ->assertRedirect('/ganti-kata-sandi');
    }

    public function test_pengguna_tanpa_kewajiban_membuka_ganti_kata_sandi_diarahkan_ke_dashboardnya(): void
    {
        $this->actingAs(User::factory()->create())
            ->get('/ganti-kata-sandi')
            ->assertRedirect('/guru/dashboard');

        $this->actingAs(User::factory()->admin()->create())
            ->get('/ganti-kata-sandi')
            ->assertRedirect('/admin/dashboard');
    }

    public function test_akun_dinonaktifkan_saat_login_dikeluarkan_pada_request_berikutnya(): void
    {
        $guru = User::factory()->create();

        $this->actingAs($guru)->get('/guru/dashboard')->assertOk();

        $guru->update(['aktif' => false]);

        $this->get('/guru/dashboard')
            ->assertRedirect('/login')
            ->assertSessionHasErrors(['login' => 'Akun tidak aktif, hubungi admin.']);

        $this->assertGuest();
    }
}
