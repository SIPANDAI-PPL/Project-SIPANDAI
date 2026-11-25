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
use Illuminate\Support\Facades\DB;
use Tests\TestCase;

// Fitur: PBI-07 Formulir Pencatatan Observasi per Anak
class ObservasiTest extends TestCase
{
    use RefreshDatabase;

    private User $guru;

    private User $guruLain;

    private Kelas $kelasGuru;

    private Kelas $kelasGuruLain;

    private Anak $anak1;

    private Anak $anak2;

    private Anak $anakKelasLain;

    private Anak $anakNonaktif;

    private Aktivitas $aktivitas;

    private AspekPerkembangan $aspek;

    private StrategiPembelajaran $strategi;

    protected function setUp(): void
    {
        parent::setUp();

        // Menggunakan tanggal tetap agar pengujian konsisten.
        Carbon::setTestNow('2026-11-14 09:00:00');

        // Membuat akun guru.
        $this->guru = User::factory()->create([
            'nama' => 'Mia Stacia',
            'nama_pengguna' => 'miastacia',
        ]);

        $this->guruLain = User::factory()->create([
            'nama' => 'Ayu Rizki',
            'nama_pengguna' => 'ayurizki',
        ]);

        // Kelas yang menjadi tanggung jawab guru.
        $this->kelasGuru = Kelas::factory()->create([
            'guru_id' => $this->guru->id,
            'nama_kelas' => 'Melati',
            'kelompok' => 'TK A',
        ]);

        // Kelas milik guru lain.
        $this->kelasGuruLain = Kelas::factory()->create([
            'guru_id' => $this->guruLain->id,
            'nama_kelas' => 'Cendrawasih',
            'kelompok' => 'TK B',
        ]);

        // Data master observasi.
        $this->aktivitas = Aktivitas::factory()->create([
            'nama' => 'Menyusun balok',
            'aktif' => true,
        ]);

        $this->aspek = AspekPerkembangan::factory()->create([
            'nama' => 'Kognitif',
        ]);

        $this->strategi = StrategiPembelajaran::factory()->create([
            'nama' => 'Media visual',
            'aktif' => true,
        ]);

        // Anak pada kelas guru.
        $this->anak1 = Anak::factory()->create([
            'kelas_id' => $this->kelasGuru->id,
            'nama' => 'Dedek Tirta',
            'nama_panggilan' => 'Tirta',
            'aktif' => true,
        ]);

        $this->anak2 = Anak::factory()->create([
            'kelas_id' => $this->kelasGuru->id,
            'nama' => 'Waluyo Putra',
            'nama_panggilan' => 'Waluyo',
            'aktif' => true,
        ]);

        // Anak pada kelas guru lain.
        $this->anakKelasLain = Anak::factory()->create([
            'kelas_id' => $this->kelasGuruLain->id,
            'nama' => 'Cagak Putra',
            'nama_panggilan' => 'Cagak',
            'aktif' => true,
        ]);

        // Anak nonaktif tidak boleh muncul pada formulir baru.
        $this->anakNonaktif = Anak::factory()->create([
            'kelas_id' => $this->kelasGuru->id,
            'nama' => 'Sadina Putri',
            'nama_panggilan' => 'Sadina',
            'aktif' => false,
        ]);
    }

    protected function tearDown(): void
    {
        // Mengembalikan waktu ke kondisi semula.
        Carbon::setTestNow();

        parent::tearDown();
    }

    /**
     * TC-07-01
     * Menampilkan daftar anak sesuai kelas yang menjadi tanggung jawab guru.
     */
    public function test_tc_07_01_menampilkan_daftar_anak_sesuai_kelas_guru(): void
    {
        $this->actingAs($this->guru)
            ->get(route('guru.observasi.create'))
            ->assertOk()
            ->assertSee('Pilih Kelas')
            ->assertSee('Melati')
            ->assertSee('Dedek Tirta')
            ->assertSee('Waluyo Putra')
            ->assertDontSee('Cendrawasih')
            ->assertDontSee('Cagak Putra')
            ->assertDontSee('Sadina Putri');
    }

    /**
     * TC-07-02
     * Memilih anak yang akan diobservasi.
     */
    public function test_tc_07_02_memilih_anak_yang_akan_diobservasi(): void
    {
        $respons = $this->actingAs($this->guru)
            ->get(route('guru.observasi.create'))
            ->assertOk()
            ->assertSee('Pilih Anak')
            ->assertSee('name="anak_id"', false)
            ->assertSee('Dedek Tirta');

        // Memastikan pilihan anak disesuaikan dengan kelas yang dipilih.
        $respons->assertSee(
            'String(a.kelas_id) === kelas.value',
            false
        );
    }

    /**
     * TC-07-03
     * Menampilkan formulir pencatatan observasi.
     */
    public function test_tc_07_03_menampilkan_formulir_observasi(): void
    {
        $this->actingAs($this->guru)
            ->get(route('guru.observasi.create'))
            ->assertOk()
            ->assertSee('Formulir Observasi Perkembangan')
            ->assertSee('name="tanggal"', false)
            ->assertSee('name="aktivitas_id"', false)
            ->assertSee('name="strategi_id"', false)
            ->assertSee('name="aspek_id"', false)
            ->assertSee('name="kategori_respons"', false)
            ->assertSee('name="catatan_pendukung"', false);
    }

    /**
     * TC-07-04
     * Memilih aktivitas pembelajaran.
     */
    public function test_tc_07_04_memilih_aktivitas_pembelajaran(): void
    {
        $this->actingAs($this->guru)
            ->get(route('guru.observasi.create'))
            ->assertOk()
            ->assertSee('Aktivitas Observasi')
            ->assertSee('Menyusun balok');
    }

    /**
     * TC-07-05
     * Memilih strategi pembelajaran.
     */
    public function test_tc_07_05_memilih_strategi_pembelajaran(): void
    {
        $this->actingAs($this->guru)
            ->get(route('guru.observasi.create'))
            ->assertOk()
            ->assertSee('Strategi Pembelajaran')
            ->assertSee('Media visual');
    }

    /**
     * TC-07-06
     * Memilih kategori respons.
     */
    public function test_tc_07_06_menampilkan_pilihan_kategori_respons(): void
    {
        $this->actingAs($this->guru)
            ->get(route('guru.observasi.create'))
            ->assertOk()
            ->assertSee('Terlibat aktif')
            ->assertSee('Terlibat dengan pendampingan')
            ->assertSee('Kurang tertarik')
            ->assertSee('Belum terlibat');

        // Memastikan seluruh kategori enum tersedia pada formulir.
        $this->assertCount(4, KategoriRespons::cases());
    }

    /**
     * TC-07-07
     * Menyimpan observasi dengan data lengkap.
     */
    public function test_tc_07_07_menyimpan_observasi_dengan_data_lengkap(): void
    {
        $data = $this->dataObservasi();

        $this->actingAs($this->guru)
            ->post(route('guru.observasi.store'), $data)
            ->assertRedirect(route('guru.observasi.index'))
            ->assertSessionHas(
                'notifikasi.judul',
                'Observasi berhasil disimpan'
            );

        $this->assertDatabaseHas('observasi', [
            'guru_id' => $this->guru->id,
            'anak_id' => $this->anak1->id,
            'tanggal' => '2026-11-14',
            'aktivitas_id' => $this->aktivitas->id,
            'aspek_id' => $this->aspek->id,
            'strategi_id' => $this->strategi->id,
            'kategori_respons' => KategoriRespons::TerlibatAktif->value,
            'catatan_pendukung' => 'Anak mengikuti kegiatan dengan baik.',
        ]);
    }

    /**
     * TC-07-08
     * Menyimpan observasi tanpa catatan tambahan.
     */
    public function test_tc_07_08_menyimpan_observasi_tanpa_catatan_tambahan(): void
    {
        $data = $this->dataObservasi();

        // Catatan pendukung tidak diisi karena bersifat opsional.
        unset($data['catatan_pendukung']);

        $this->actingAs($this->guru)
            ->post(route('guru.observasi.store'), $data)
            ->assertRedirect(route('guru.observasi.index'))
            ->assertSessionHas(
                'notifikasi.judul',
                'Observasi berhasil disimpan'
            );

        $this->assertDatabaseHas('observasi', [
            'guru_id' => $this->guru->id,
            'anak_id' => $this->anak1->id,
            'catatan_pendukung' => null,
        ]);
    }

    /**
     * TC-07-09
     * Menolak penyimpanan apabila kolom wajib kosong.
     */
    public function test_tc_07_09_menolak_observasi_dengan_kolom_wajib_kosong(): void
    {
        $data = $this->dataObservasi([
            'kategori_respons' => '',
        ]);

        $this->actingAs($this->guru)
            ->from(route('guru.observasi.create'))
            ->post(route('guru.observasi.store'), $data)
            ->assertRedirect(route('guru.observasi.create'))
            ->assertSessionHasErrors('kategori_respons');

        $this->assertDatabaseCount('observasi', 0);
    }

    /**
     * TC-07-10
     * Memastikan data observasi terhubung dengan anak yang dipilih.
     */
    public function test_tc_07_10_observasi_terhubung_dengan_anak_yang_dipilih(): void
    {
        $tanggal = '2026-10-20';

        $data = $this->dataObservasi([
            'anak_id' => $this->anak2->id,
            'tanggal' => $tanggal,
            'kategori_respons' => KategoriRespons::KurangTertarik->value,
            'catatan_pendukung' => 'Anak membutuhkan pendampingan.',
        ]);

        $this->actingAs($this->guru)
            ->post(route('guru.observasi.store'), $data)
            ->assertRedirect(route('guru.observasi.index'));

        $this->assertDatabaseHas('observasi', [
            'guru_id' => $this->guru->id,
            'anak_id' => $this->anak2->id,
            'tanggal' => $tanggal,
            'kategori_respons' => KategoriRespons::KurangTertarik->value,
            'catatan_pendukung' => 'Anak membutuhkan pendampingan.',
        ]);

        // Memastikan observasi tidak terhubung ke anak yang berbeda.
        $this->assertDatabaseMissing('observasi', [
            'anak_id' => $this->anak1->id,
            'tanggal' => $tanggal,
        ]);
    }

    /**
     * TC-07-11
     * Menampilkan observasi yang sudah disimpan pada riwayat anak.
     */
    public function test_tc_07_11_menampilkan_observasi_pada_riwayat_anak(): void
    {
        $this->actingAs($this->guru)
            ->post(
                route('guru.observasi.store'),
                $this->dataObservasi([
                    'catatan_pendukung' => 'Catatan observasi PBI-07.',
                ])
            )
            ->assertRedirect(route('guru.observasi.index'));

        // Membuka kembali halaman riwayat anak.
        $this->actingAs($this->guru)
            ->get(route('guru.riwayat-observasi', $this->anak1->id))
            ->assertOk()
            ->assertSee($this->anak1->nama_panggilan)
            ->assertSee('Catatan observasi PBI-07.')
            ->assertSee('Menyusun balok')
            ->assertSee('Terlibat aktif');
    }

    /**
     * TC-07-12
     * Menangani kegagalan penyimpanan observasi.
     */
    public function test_tc_07_12_menampilkan_notifikasi_ketika_penyimpanan_gagal(): void
    {
        // Mensimulasikan kegagalan saat query INSERT observasi dijalankan.
        DB::connection()->beforeExecuting(
            function (string $query): void {
                if (
                    preg_match(
                        '/insert\s+into\s+[\`"\\[]?observasi[\`"\\]]?/i',
                        $query
                    )
                ) {
                    throw new \RuntimeException(
                        'Simulasi kegagalan penyimpanan observasi.'
                    );
                }
            }
        );

        $this->actingAs($this->guru)
            ->post(
                route('guru.observasi.store'),
                $this->dataObservasi()
            )
            ->assertRedirect(route('guru.observasi.index'))
            ->assertSessionHas('gagal');

        // Memastikan pesan kegagalan ditampilkan pada halaman observasi.
        $this->actingAs($this->guru)
            ->get(route('guru.observasi.index'))
            ->assertOk()
            ->assertSee('Data observasi gagal disimpan');

        $this->assertDatabaseCount('observasi', 0);
    }

    /**
     * TC-07-13
     * Menolak pencatatan observasi pada kelas milik guru lain.
     */
    public function test_tc_07_13_menolak_pencatatan_anak_di_kelas_guru_lain(): void
    {
        $data = $this->dataObservasi([
            'kelas_id' => $this->kelasGuruLain->id,
            'anak_id' => $this->anakKelasLain->id,
        ]);

        $this->actingAs($this->guru)
            ->from(route('guru.observasi.create'))
            ->post(route('guru.observasi.store'), $data)
            ->assertRedirect(route('guru.observasi.create'))
            ->assertSessionHasErrors('kelas_id');

        $this->assertDatabaseCount('observasi', 0);
    }

    /**
     * TC-07-14
     * Menolak anak dari kelas guru lain meskipun kelas yang dikirim
     * adalah kelas milik guru yang sedang login.
     */
    public function test_tc_07_14_menolak_anak_dari_kelas_guru_lain(): void
    {
        $data = $this->dataObservasi([
            'kelas_id' => $this->kelasGuru->id,
            'anak_id' => $this->anakKelasLain->id,
        ]);

        $this->actingAs($this->guru)
            ->from(route('guru.observasi.create'))
            ->post(route('guru.observasi.store'), $data)
            ->assertRedirect(route('guru.observasi.create'))
            ->assertSessionHasErrors('anak_id');

        $this->assertDatabaseCount('observasi', 0);
    }

    /**
     * Menyiapkan data valid untuk pengujian penyimpanan observasi.
     */
    private function dataObservasi(array $tambahan = []): array
    {
        $data = [
            'kelas_id' => $this->kelasGuru->id,
            'anak_id' => $this->anak1->id,
            'tanggal' => '2026-11-14',
            'aktivitas_id' => $this->aktivitas->id,
            'aspek_id' => $this->aspek->id,
            'strategi_id' => $this->strategi->id,
            'kategori_respons' => KategoriRespons::TerlibatAktif->value,
            'catatan_pendukung' => 'Anak mengikuti kegiatan dengan baik.',
        ];

        return array_merge($data, $tambahan);
    }
}
