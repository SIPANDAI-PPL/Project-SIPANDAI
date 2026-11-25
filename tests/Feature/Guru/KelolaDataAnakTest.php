<?php

namespace Tests\Feature\Guru;

use App\Enums\KategoriRespons;
use App\Models\Aktivitas;
use App\Models\Anak;
use App\Models\AspekPerkembangan;
use App\Models\Kelas;
use App\Models\Observasi;
use App\Models\StrategiPembelajaran;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Carbon;
use Tests\TestCase;

// Fitur: PBI-5 Kelola Data Dasar Anak
class KelolaDataAnakTest extends TestCase
{
    use RefreshDatabase;

    private User $admin;

    private User $g1;

    private User $g2;

    private User $g3;

    private Kelas $kelasB1;

    private Kelas $kelasB2;

    private Kelas $kelasG2;

    private Anak $c1;

    private Anak $c2;

    private Anak $c3;

    private Anak $c4;

    private Anak $c5;

    private Anak $c6;

    protected function setUp(): void
    {
        parent::setUp();

        Carbon::setTestNow('2026-11-16 09:00:00');

        $this->admin = User::factory()->admin()->create(['nama_pengguna' => 'admin']);
        $this->g1 = User::factory()->create(['nama' => 'Mia Stacia', 'nama_pengguna' => 'miastacia']);
        $this->g2 = User::factory()->create(['nama' => 'Ayu Rizki', 'nama_pengguna' => 'ayurizki']);
        $this->g3 = User::factory()->create(['nama' => 'Andi Pratama', 'nama_pengguna' => 'andipratama']);

        // Kelas pertama G1 (urut nama kelas) adalah Melati.
        $this->kelasB1 = Kelas::factory()->create(['guru_id' => $this->g1->id, 'nama_kelas' => 'Melati', 'kelompok' => 'TK B', 'tahun_ajaran' => '2026/2027']);
        $this->kelasB2 = Kelas::factory()->create(['guru_id' => $this->g1->id, 'nama_kelas' => 'Teratai', 'kelompok' => 'TK B', 'tahun_ajaran' => '2026/2027']);
        $this->kelasG2 = Kelas::factory()->create(['guru_id' => $this->g2->id, 'nama_kelas' => 'Anggrek', 'kelompok' => 'TK A', 'tahun_ajaran' => '2026/2027']);

        $this->c1 = $this->buatAnak($this->kelasB1, 'Alya Putri', 'Alya', 40);
        $this->c2 = $this->buatAnak($this->kelasB1, 'Bima Pratama', 'Bima', 45);
        $this->c3 = $this->buatAnak($this->kelasB1, 'Dedek Nandita', 'Dedek', 50);
        $this->c4 = $this->buatAnak($this->kelasB1, 'Faris Ramadhan', 'Faris', 52, aktif: false);
        $this->c5 = $this->buatAnak($this->kelasB2, 'Gita Anindya', 'Gita', 48);
        $this->c6 = $this->buatAnak($this->kelasG2, 'Hanif Pratama', 'Hanif', 36);

        $aktivitas = Aktivitas::factory()->create();
        $strategi = StrategiPembelajaran::factory()->create();
        $aspek = AspekPerkembangan::factory()->create();

        // C1: 2 observasi, terakhir hari ini, Terlibat aktif.
        $this->buatObservasi($this->c1, 1, '2026-11-10', KategoriRespons::KurangTertarik, $aktivitas, $strategi, $aspek);
        $this->buatObservasi($this->c1, 1, '2026-11-16', KategoriRespons::TerlibatAktif, $aktivitas, $strategi, $aspek);
        // C3: 14 observasi, terakhir hari ini, Kurang tertarik.
        $this->buatObservasi($this->c3, 13, '2026-11-01', KategoriRespons::TerlibatAktif, $aktivitas, $strategi, $aspek);
        $this->buatObservasi($this->c3, 1, '2026-11-16', KategoriRespons::KurangTertarik, $aktivitas, $strategi, $aspek);
        // C4: 3 observasi (nonaktif).
        $this->buatObservasi($this->c4, 3, '2026-10-20', KategoriRespons::TerlibatAktif, $aktivitas, $strategi, $aspek);
    }

    protected function tearDown(): void
    {
        Carbon::setTestNow();

        parent::tearDown();
    }

    private function buatAnak(Kelas $kelas, string $nama, string $panggilan, int $usiaBulan, bool $aktif = true): Anak
    {
        return Anak::factory()->create([
            'kelas_id' => $kelas->id,
            'nama' => $nama,
            'nama_panggilan' => $panggilan,
            'tanggal_lahir' => now()->subMonths($usiaBulan)->format('Y-m-d'),
            'aktif' => $aktif,
        ]);
    }

    private function buatObservasi(Anak $anak, int $jumlah, string $tanggal, KategoriRespons $respons, Aktivitas $aktivitas, StrategiPembelajaran $strategi, AspekPerkembangan $aspek): void
    {
        Observasi::factory()->count($jumlah)->create([
            'anak_id' => $anak->id,
            'guru_id' => $anak->kelas->guru_id,
            'aktivitas_id' => $aktivitas->id,
            'strategi_id' => $strategi->id,
            'aspek_id' => $aspek->id,
            'tanggal' => $tanggal,
            'kategori_respons' => $respons,
        ]);
    }

    /** @return array<string, mixed> */
    private function dataForm(array $ubah = []): array
    {
        return array_merge([
            'nama' => 'Rani Kartika',
            'nama_panggilan' => 'Rani',
            'tanggal_lahir' => now()->subMonths(42)->format('Y-m-d'),
            'kelas_id' => $this->kelasB1->id,
        ], $ubah);
    }

    public function test_tc_05_01_menampilkan_daftar_anak_aktif_pada_kelas_pertama(): void
    {
        $this->actingAs($this->g1)
            ->get(route('guru.anak.index'))
            ->assertOk()
            ->assertSee('Melati · 2026/2027 (4 anak)')
            ->assertSee('Teratai · 2026/2027 (1 anak)')
            ->assertDontSee('Anggrek')
            ->assertSee('Aktif (3)')
            ->assertSee('Nonaktif (1)')
            ->assertSeeInOrder(['Alya', '3 tahun', 'Terlibat aktif', 'Hari ini'])
            ->assertSeeInOrder(['Bima', '3 tahun', 'Belum diobservasi'])
            ->assertSeeInOrder(['Dedek', '4 tahun', 'Kurang tertarik', 'Hari ini'])
            ->assertDontSee('Faris')
            ->assertDontSee('Gita')
            ->assertDontSee('Hanif');
    }

    public function test_tc_05_02_mencari_anak_berdasarkan_nama_atau_nama_panggilan(): void
    {
        $this->actingAs($this->g1)
            ->get(route('guru.anak.index', ['cari' => 'alya']))
            ->assertOk()
            ->assertSee('Alya')
            ->assertDontSee('Bima')
            ->assertDontSee('Dedek')
            // Jumlah tab tetap per kelas, tidak ikut pencarian.
            ->assertSee('Aktif (3)');

        $this->actingAs($this->g1)
            ->get(route('guru.anak.index', ['cari' => 'dedek']))
            ->assertOk()
            ->assertSee('Dedek')
            ->assertDontSee('Alya')
            ->assertDontSee('Bima');

        // Pencarian juga mencocokkan nama lengkap.
        $this->actingAs($this->g1)
            ->get(route('guru.anak.index', ['cari' => 'Nandita']))
            ->assertSee('Dedek')
            ->assertDontSee('Alya');
    }

    public function test_tc_05_03_memfilter_anak_lewat_tab_nonaktif(): void
    {
        $this->actingAs($this->g1)
            ->get(route('guru.anak.index', ['status' => 'nonaktif']))
            ->assertOk()
            ->assertSee('Faris')
            ->assertDontSee('Alya')
            ->assertDontSee('Bima')
            ->assertDontSee('Dedek');
    }

    public function test_tc_05_04_berpindah_kelas_lewat_pilihan_kelas(): void
    {
        $this->actingAs($this->g1)
            ->get(route('guru.anak.index', ['kelas' => $this->kelasB2->id]))
            ->assertOk()
            ->assertSee('Gita')
            ->assertDontSee('Alya')
            ->assertSee('Aktif (1)')
            ->assertSee('Nonaktif (0)');
    }

    public function test_tc_05_05_menambah_data_anak_dengan_data_valid(): void
    {
        $this->actingAs($this->g1)
            ->post(route('guru.anak.store'), $this->dataForm())
            ->assertRedirect(route('guru.anak.index', ['kelas' => $this->kelasB1->id]))
            ->assertSessionHas('status', 'Data anak berhasil ditambahkan.');

        $this->assertDatabaseHas('anak', [
            'nama' => 'Rani Kartika',
            'nama_panggilan' => 'Rani',
            'kelas_id' => $this->kelasB1->id,
            // 42 bulan disimpan sebagai 3 tahun penuh.
            'usia' => 3,
            'aktif' => 1,
        ]);

        $this->actingAs($this->g1)
            ->get(route('guru.anak.index'))
            ->assertSee('Rani')
            ->assertSee('Aktif (4)')
            ->assertSee('Melati · 2026/2027 (5 anak)');
    }

    public function test_tc_05_05_menambah_anak_tanpa_nama_panggilan_menampilkan_nama_lengkap(): void
    {
        $this->actingAs($this->g1)
            ->post(route('guru.anak.store'), $this->dataForm(['nama_panggilan' => '']))
            ->assertSessionHasNoErrors();

        $this->assertDatabaseHas('anak', ['nama' => 'Rani Kartika', 'nama_panggilan' => null]);

        $this->actingAs($this->g1)
            ->get(route('guru.anak.index'))
            ->assertSee('Rani Kartika');
    }

    public function test_tc_05_06_mengubah_data_anak(): void
    {
        $this->actingAs($this->g1)
            ->get(route('guru.anak.show', $this->c1))
            ->assertOk()
            ->assertSee('Terhubung: ')
            ->assertSee('2 observasi')
            ->assertSee(route('guru.riwayat-observasi', $this->c1), false);

        $this->actingAs($this->g1)
            ->put(route('guru.anak.update', $this->c1), $this->dataForm([
                'nama' => 'Alya Putri',
                'nama_panggilan' => 'Aya',
                'tanggal_lahir' => $this->c1->tanggal_lahir->format('Y-m-d'),
            ]))
            ->assertRedirect(route('guru.anak.show', $this->c1))
            ->assertSessionHas('status', 'Data anak berhasil diubah.');

        $this->assertSame('Aya', $this->c1->fresh()->nama_panggilan);
        // Riwayat observasi tidak berubah.
        $this->assertSame(2, $this->c1->observasi()->count());
    }

    public function test_tc_05_06_mengubah_tanggal_lahir_menghitung_ulang_usia_dalam_tahun(): void
    {
        $this->actingAs($this->g1)
            ->put(route('guru.anak.update', $this->c1), $this->dataForm([
                'nama' => $this->c1->nama,
                'nama_panggilan' => $this->c1->nama_panggilan,
                'tanggal_lahir' => now()->subMonths(50)->format('Y-m-d'),
            ]))
            ->assertSessionHasNoErrors();

        $this->assertSame(4, $this->c1->fresh()->usia);

        $this->actingAs($this->g1)
            ->get(route('guru.anak.show', $this->c1))
            ->assertSee('4 tahun');
    }

    public function test_tc_05_07_memindahkan_anak_ke_kelas_lain_yang_diampu_guru_yang_sama(): void
    {
        $this->actingAs($this->g1)
            ->put(route('guru.anak.update', $this->c2), $this->dataForm([
                'nama' => 'Bima Pratama',
                'nama_panggilan' => 'Bima',
                'tanggal_lahir' => $this->c2->tanggal_lahir->format('Y-m-d'),
                'kelas_id' => $this->kelasB2->id,
            ]))
            ->assertSessionHasNoErrors();

        $this->assertSame($this->kelasB2->id, $this->c2->fresh()->kelas_id);

        $this->actingAs($this->g1)
            ->get(route('guru.anak.index'))
            ->assertDontSee('Bima');

        $this->actingAs($this->g1)
            ->get(route('guru.anak.index', ['kelas' => $this->kelasB2->id]))
            ->assertSee('Bima');
    }

    public function test_tc_05_08_menonaktifkan_anak(): void
    {
        $this->actingAs($this->g1)
            ->patch(route('guru.anak.nonaktifkan', $this->c2))
            ->assertRedirect(route('guru.anak.show', $this->c2))
            ->assertSessionHas('status', 'Anak berhasil dinonaktifkan.');

        // Data tetap ada di basis data, hanya berstatus nonaktif.
        $this->assertDatabaseHas('anak', ['id' => $this->c2->id, 'aktif' => 0]);

        $this->actingAs($this->g1)
            ->get(route('guru.anak.index', ['status' => 'nonaktif']))
            ->assertSee('Bima');
    }

    public function test_tc_05_09_mengaktifkan_kembali_anak(): void
    {
        $this->actingAs($this->g1)
            ->patch(route('guru.anak.aktifkan', $this->c4))
            ->assertRedirect(route('guru.anak.show', $this->c4))
            ->assertSessionHas('status', 'Anak berhasil diaktifkan kembali.');

        $this->assertDatabaseHas('anak', ['id' => $this->c4->id, 'aktif' => 1]);
        $this->assertSame(3, $this->c4->observasi()->count());

        $this->actingAs($this->g1)
            ->get(route('guru.anak.index'))
            ->assertSee('Faris');
    }

    public function test_tc_05_10_menonaktifkan_anak_yang_memiliki_riwayat_observasi(): void
    {
        $this->assertSame(14, $this->c3->observasi()->count());

        $this->actingAs($this->g1)
            ->patch(route('guru.anak.nonaktifkan', $this->c3))
            ->assertSessionHas('status');

        $this->assertDatabaseHas('anak', ['id' => $this->c3->id, 'aktif' => 0]);
        $this->assertSame(14, $this->c3->observasi()->count());

        $this->actingAs($this->g1)
            ->get(route('guru.anak.index', ['status' => 'nonaktif']))
            ->assertSee('Dedek');

        // Anak nonaktif tidak muncul sebagai pilihan pada formulir observasi (PBI-7).
        $this->actingAs($this->g1)
            ->get(route('guru.observasi.create'))
            ->assertOk()
            ->assertSee('Alya Putri')
            ->assertDontSee('Dedek Nandita');
    }

    public function test_tc_05_11_menyimpan_formulir_dengan_field_wajib_kosong(): void
    {
        $this->actingAs($this->g1)
            ->post(route('guru.anak.store'), $this->dataForm(['nama' => '', 'tanggal_lahir' => '', 'kelas_id' => '']))
            ->assertSessionHasErrors([
                'nama' => 'Nama lengkap wajib diisi.',
                'tanggal_lahir' => 'Tanggal lahir wajib diisi.',
                'kelas_id' => 'Kelas wajib dipilih.',
            ]);

        $this->assertDatabaseMissing('anak', ['nama_panggilan' => 'Rani']);
    }

    public function test_tc_05_12_menyimpan_tanggal_lahir_yang_tidak_valid_atau_di_masa_depan(): void
    {
        $this->actingAs($this->g1)
            ->post(route('guru.anak.store'), $this->dataForm(['tanggal_lahir' => '2024-02-31']))
            ->assertSessionHasErrors(['tanggal_lahir' => 'Tanggal lahir tidak valid.']);

        $this->actingAs($this->g1)
            ->post(route('guru.anak.store'), $this->dataForm(['tanggal_lahir' => '2026-11-17']))
            ->assertSessionHasErrors(['tanggal_lahir' => 'Tanggal lahir tidak valid.']);

        $this->assertDatabaseMissing('anak', ['nama_panggilan' => 'Rani']);
    }

    public function test_tc_05_13_menyimpan_anak_dengan_usia_kurang_dari_24_bulan(): void
    {
        $this->actingAs($this->g1)
            ->post(route('guru.anak.store'), $this->dataForm(['tanggal_lahir' => now()->subMonths(20)->format('Y-m-d')]))
            ->assertSessionHasErrors(['tanggal_lahir' => 'Usia anak harus 24–59 bulan.']);

        // 23 bulan ditolak, 24 bulan diterima.
        $this->actingAs($this->g1)
            ->post(route('guru.anak.store'), $this->dataForm(['tanggal_lahir' => now()->subMonths(23)->format('Y-m-d')]))
            ->assertSessionHasErrors(['tanggal_lahir' => 'Usia anak harus 24–59 bulan.']);

        $this->actingAs($this->g1)
            ->post(route('guru.anak.store'), $this->dataForm(['tanggal_lahir' => now()->subMonths(24)->format('Y-m-d')]))
            ->assertSessionHasNoErrors();
    }

    public function test_tc_05_14_menyimpan_anak_dengan_usia_lebih_dari_59_bulan(): void
    {
        $this->actingAs($this->g1)
            ->post(route('guru.anak.store'), $this->dataForm(['tanggal_lahir' => now()->subMonths(65)->format('Y-m-d')]))
            ->assertSessionHasErrors(['tanggal_lahir' => 'Usia anak harus 24–59 bulan.']);

        // 60 bulan ditolak, 59 bulan diterima.
        $this->actingAs($this->g1)
            ->post(route('guru.anak.store'), $this->dataForm(['tanggal_lahir' => now()->subMonths(60)->format('Y-m-d')]))
            ->assertSessionHasErrors(['tanggal_lahir' => 'Usia anak harus 24–59 bulan.']);

        $this->actingAs($this->g1)
            ->post(route('guru.anak.store'), $this->dataForm(['tanggal_lahir' => now()->subMonths(59)->format('Y-m-d')]))
            ->assertSessionHasNoErrors();
    }

    public function test_tc_05_14_mengubah_anak_lama_tanpa_mengganti_tanggal_lahir_tidak_dicek_usianya(): void
    {
        $lama = $this->buatAnak($this->kelasB1, 'Anak Lama', 'Lama', 70);

        $this->actingAs($this->g1)
            ->put(route('guru.anak.update', $lama), $this->dataForm([
                'nama' => 'Anak Lama Diperbaiki',
                'tanggal_lahir' => $lama->tanggal_lahir->format('Y-m-d'),
            ]))
            ->assertSessionHasNoErrors();

        $this->assertSame('Anak Lama Diperbaiki', $lama->fresh()->nama);

        // Jika tanggal lahir diganti, batas usia kembali dicek.
        $this->actingAs($this->g1)
            ->put(route('guru.anak.update', $lama), $this->dataForm([
                'tanggal_lahir' => now()->subMonths(65)->format('Y-m-d'),
            ]))
            ->assertSessionHasErrors(['tanggal_lahir' => 'Usia anak harus 24–59 bulan.']);
    }

    public function test_tc_05_15_menambah_atau_memindahkan_anak_ke_kelas_guru_lain_lewat_request_langsung(): void
    {
        $this->actingAs($this->g1)
            ->post(route('guru.anak.store'), $this->dataForm(['kelas_id' => $this->kelasG2->id]))
            ->assertSessionHasErrors(['kelas_id' => 'Kelas tidak valid.']);

        $this->assertDatabaseMissing('anak', ['nama_panggilan' => 'Rani']);

        $this->actingAs($this->g1)
            ->put(route('guru.anak.update', $this->c1), $this->dataForm([
                'tanggal_lahir' => $this->c1->tanggal_lahir->format('Y-m-d'),
                'kelas_id' => $this->kelasG2->id,
            ]))
            ->assertSessionHasErrors(['kelas_id' => 'Kelas tidak valid.']);

        $this->assertSame($this->kelasB1->id, $this->c1->fresh()->kelas_id);
    }

    public function test_tc_05_16_guru_mengubah_atau_menonaktifkan_anak_di_kelas_guru_lain(): void
    {
        $this->actingAs($this->g1)
            ->put(route('guru.anak.update', $this->c6), $this->dataForm(['nama' => 'Diubah Paksa']))
            ->assertForbidden();

        $this->actingAs($this->g1)
            ->patch(route('guru.anak.nonaktifkan', $this->c6))
            ->assertForbidden();

        $this->actingAs($this->g1)
            ->get(route('guru.anak.show', $this->c6))
            ->assertForbidden()
            ->assertDontSee('Hanif');

        $this->c6->refresh();
        $this->assertSame('Hanif Pratama', $this->c6->nama);
        $this->assertTrue($this->c6->aktif);

        // Anak yang sudah nonaktif juga tidak bisa diaktifkan guru lain.
        $this->c6->update(['aktif' => false]);
        $this->actingAs($this->g1)
            ->patch(route('guru.anak.aktifkan', $this->c6))
            ->assertForbidden();
        $this->assertFalse($this->c6->fresh()->aktif);
    }

    public function test_tc_05_17_admin_membuka_halaman_kelola_data_anak(): void
    {
        $this->actingAs($this->admin)->get(route('guru.anak.index'))->assertForbidden();
        $this->actingAs($this->admin)->get(route('guru.anak.show', $this->c1))->assertForbidden();
        $this->actingAs($this->admin)->post(route('guru.anak.store'), $this->dataForm())->assertForbidden();
        $this->actingAs($this->admin)->put(route('guru.anak.update', $this->c1), $this->dataForm())->assertForbidden();
        $this->actingAs($this->admin)->patch(route('guru.anak.nonaktifkan', $this->c1))->assertForbidden();

        $this->assertDatabaseMissing('anak', ['nama_panggilan' => 'Rani']);
    }

    public function test_tc_05_18_pengguna_yang_belum_login_membuka_halaman_kelola_data_anak(): void
    {
        $this->get(route('guru.anak.index'))->assertRedirect(route('login'));
        $this->post(route('guru.anak.store'), $this->dataForm())->assertRedirect(route('login'));
    }

    public function test_tc_05_19_guru_tanpa_kelas_membuka_halaman_kelola_data_anak(): void
    {
        $this->actingAs($this->g3)
            ->get(route('guru.anak.index'))
            ->assertOk()
            ->assertSee('Anda belum mengampu kelas, hubungi admin.')
            ->assertDontSee('Aktif (')
            // Tombol Tambah anak tampil tetapi tidak dapat diklik.
            ->assertSee('Tambah anak')
            ->assertSee('disabled', false);

        // Request tambah langsung ditolak.
        $this->actingAs($this->g3)
            ->post(route('guru.anak.store'), $this->dataForm())
            ->assertSessionHasErrors(['kelas_id' => 'Kelas tidak valid.']);

        $this->assertDatabaseMissing('anak', ['nama_panggilan' => 'Rani']);
    }

    public function test_tc_05_18_guru_wajib_ganti_kata_sandi_diarahkan_ke_ganti_kata_sandi(): void
    {
        $this->g1->update(['wajib_ganti_sandi' => true]);

        $this->actingAs($this->g1)
            ->get(route('guru.anak.index'))
            ->assertRedirect(route('kata-sandi.edit'));
    }

    public function test_tc_05_13_14_hitung_usia_tahun_pada_model_anak(): void
    {
        $this->assertSame(2, Anak::hitungUsiaTahun('2024-11-16'));
        $this->assertSame(1, Anak::hitungUsiaTahun('2024-11-17'));
        $this->assertSame(4, Anak::hitungUsiaTahun('2021-11-17'));
        $this->assertSame(5, Anak::hitungUsiaTahun('2021-11-16'));
        $this->assertSame(0, Anak::hitungUsiaTahun('2026-11-17'));
    }

    public function test_tc_05_13_14_hitung_usia_bulan_pada_model_anak(): void
    {
        $this->assertSame(24, Anak::hitungUsiaBulan('2024-11-16'));
        $this->assertSame(23, Anak::hitungUsiaBulan('2024-11-17'));
        $this->assertSame(59, Anak::hitungUsiaBulan('2021-11-17'));
        $this->assertSame(60, Anak::hitungUsiaBulan('2021-11-16'));
        $this->assertSame(-1, Anak::hitungUsiaBulan('2026-11-17'));
    }
}
