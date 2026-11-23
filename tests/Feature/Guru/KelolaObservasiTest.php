<?php

namespace Tests\Feature\Guru;

use App\Enums\KategoriRespons;
use App\Models\Aktivitas;
use App\Models\Anak;
use App\Models\AspekPerkembangan;
use App\Models\Kelas;
use App\Models\Observasi;
use App\Models\RiwayatPerubahan;
use App\Models\StrategiPembelajaran;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Carbon;
use Tests\TestCase;

// Fitur: PBI-8 Ubah dan Hapus Observasi beserta Riwayat Perubahan
class KelolaObservasiTest extends TestCase
{
    use RefreshDatabase;

    private User $guru;

    private User $guruLain;

    private Kelas $kelasGuru;

    private Kelas $kelasGuruLain;

    private Anak $anak;

    private Anak $anakKelasLain;

    private Aktivitas $aktivitas;

    private Aktivitas $aktivitasLain;

    private AspekPerkembangan $aspek;

    private StrategiPembelajaran $strategi;

    private Observasi $observasi;

    protected function setUp(): void
    {
        parent::setUp();

        // Tanggal tetap agar hasil pengujian konsisten.
        Carbon::setTestNow('2026-11-14 09:00:00');

        $this->guru = User::factory()->create([
            'nama' => 'Mia Stacia',
            'nama_pengguna' => 'miastacia',
        ]);

        $this->guruLain = User::factory()->create([
            'nama' => 'Ayu Rizki',
            'nama_pengguna' => 'ayurizki',
        ]);

        $this->kelasGuru = Kelas::factory()->create([
            'guru_id' => $this->guru->id,
            'nama_kelas' => 'Melati',
            'kelompok' => 'TK A',
        ]);

        $this->kelasGuruLain = Kelas::factory()->create([
            'guru_id' => $this->guruLain->id,
            'nama_kelas' => 'Cendrawasih',
            'kelompok' => 'TK B',
        ]);

        $this->aktivitas = Aktivitas::factory()->create([
            'nama' => 'Menyusun balok',
            'aktif' => true,
        ]);

        $this->aktivitasLain = Aktivitas::factory()->create([
            'nama' => 'Bercerita bersama',
            'aktif' => true,
        ]);

        $this->aspek = AspekPerkembangan::factory()->create([
            'nama' => 'Kognitif',
        ]);

        $this->strategi = StrategiPembelajaran::factory()->create([
            'nama' => 'Media visual',
            'aktif' => true,
        ]);

        $this->anak = Anak::factory()->create([
            'kelas_id' => $this->kelasGuru->id,
            'nama' => 'Dedek Tirta',
            'nama_panggilan' => 'Tirta',
            'aktif' => true,
        ]);

        $this->anakKelasLain = Anak::factory()->create([
            'kelas_id' => $this->kelasGuruLain->id,
            'nama' => 'Cagak Putra',
            'nama_panggilan' => 'Cagak',
            'aktif' => true,
        ]);

        $this->observasi = Observasi::create([
            'anak_id' => $this->anak->id,
            'guru_id' => $this->guru->id,
            'aktivitas_id' => $this->aktivitas->id,
            'strategi_id' => $this->strategi->id,
            'aspek_id' => $this->aspek->id,
            'tanggal' => '2026-11-10',
            'kategori_respons' => KategoriRespons::TerlibatAktif->value,
            'catatan_pendukung' => 'Catatan awal pengamatan.',
        ]);
    }

    protected function tearDown(): void
    {
        Carbon::setTestNow();

        parent::tearDown();
    }

    /**
     * TC-08-01
     * Menampilkan detail observasi milik guru yang sedang login.
     */
    public function test_tc_08_01_menampilkan_detail_observasi_milik_guru(): void
    {
        $this->actingAs($this->guru)
            ->get(route('guru.observasi.show', $this->observasi))
            ->assertOk()
            ->assertSee('Detail Observasi Perkembangan')
            ->assertSee('Dedek Tirta')
            ->assertSee('Menyusun balok')
            ->assertSee('Kognitif')
            ->assertSee('Media visual')
            ->assertSee('Terlibat aktif')
            ->assertSee('Catatan awal pengamatan.');
    }

    /**
     * TC-08-02
     * Guru lain tidak boleh membuka detail observasi guru pemilik (404).
     */
    public function test_tc_08_02_guru_lain_mendapat_404_saat_membuka_detail(): void
    {
        $this->actingAs($this->guruLain)
            ->get(route('guru.observasi.show', $this->observasi))
            ->assertNotFound();
    }

    /**
     * TC-08-03
     * Menampilkan formulir ubah dengan nilai observasi yang sudah tersimpan.
     */
    public function test_tc_08_03_menampilkan_formulir_ubah_dengan_data_observasi(): void
    {
        $this->actingAs($this->guru)
            ->get(route('guru.observasi.edit', $this->observasi))
            ->assertOk()
            ->assertSee('Ubah Observasi')
            ->assertSee('Dedek Tirta')
            ->assertSee('name="tanggal"', false)
            ->assertSee('value="2026-11-10"', false)
            ->assertSee('name="kategori_respons"', false)
            ->assertSee('name="_method" value="PUT"', false)
            ->assertSee(route('guru.observasi.update', $this->observasi), false);
    }

    /**
     * TC-08-04
     * Guru lain tidak boleh membuka formulir ubah observasi guru pemilik (404).
     */
    public function test_tc_08_04_guru_lain_mendapat_404_saat_membuka_formulir_ubah(): void
    {
        $this->actingAs($this->guruLain)
            ->get(route('guru.observasi.edit', $this->observasi))
            ->assertNotFound();
    }

    /**
     * TC-08-05
     * Menyimpan perubahan observasi milik guru sendiri.
     */
    public function test_tc_08_05_menyimpan_perubahan_observasi(): void
    {
        $this->actingAs($this->guru)
            ->put(route('guru.observasi.update', $this->observasi), $this->dataUbah([
                'tanggal' => '2026-11-12',
                'aktivitas_id' => $this->aktivitasLain->id,
                'kategori_respons' => KategoriRespons::KurangTertarik->value,
                'catatan_pendukung' => 'Catatan setelah diubah.',
            ]))
            ->assertRedirect(route('guru.observasi.show', $this->observasi))
            ->assertSessionHas('notifikasi');

        $this->assertDatabaseHas('observasi', [
            'id' => $this->observasi->id,
            'aktivitas_id' => $this->aktivitasLain->id,
            'kategori_respons' => KategoriRespons::KurangTertarik->value,
            'catatan_pendukung' => 'Catatan setelah diubah.',
        ]);
    }

    /**
     * TC-08-06
     * Perubahan observasi tercatat pada riwayat_perubahan dengan format
     * "nama_field: nilai_sebelum -> nilai_sesudah".
     */
    public function test_tc_08_06_mencatat_riwayat_perubahan_saat_update(): void
    {
        $this->actingAs($this->guru)
            ->put(route('guru.observasi.update', $this->observasi), $this->dataUbah([
                'kategori_respons' => KategoriRespons::KurangTertarik->value,
                'catatan_pendukung' => 'Catatan setelah diubah.',
            ]))
            ->assertRedirect(route('guru.observasi.show', $this->observasi));

        $riwayat = RiwayatPerubahan::query()
            ->where('observasi_id', $this->observasi->id)
            ->get();

        $this->assertCount(1, $riwayat);

        $entri = $riwayat->first();

        $this->assertSame($this->guru->id, $entri->dilakukan_oleh);
        $this->assertSame('ubah', $entri->aksi);
        $this->assertStringContainsString(
            'Respons anak: Terlibat aktif -> Kurang tertarik',
            (string) $entri->keterangan
        );
        $this->assertStringContainsString(
            'Catatan pendukung: Catatan awal pengamatan. -> Catatan setelah diubah.',
            (string) $entri->keterangan
        );
    }

    /**
     * TC-08-07
     * Menyimpan tanpa perubahan tidak membuat baris riwayat baru.
     */
    public function test_tc_08_07_tidak_mencatat_riwayat_bila_tidak_ada_perubahan(): void
    {
        $this->actingAs($this->guru)
            ->put(route('guru.observasi.update', $this->observasi), $this->dataUbah())
            ->assertRedirect(route('guru.observasi.show', $this->observasi));

        $this->assertDatabaseCount('riwayat_perubahan', 0);
    }

    /**
     * TC-08-08
     * Kategori respons di luar enum KategoriRespons ditolak.
     */
    public function test_tc_08_08_menolak_kategori_respons_di_luar_enum(): void
    {
        // Nilai lama (BSB) tidak boleh lagi diterima.
        $this->actingAs($this->guru)
            ->from(route('guru.observasi.edit', $this->observasi))
            ->put(route('guru.observasi.update', $this->observasi), $this->dataUbah([
                'kategori_respons' => 'BSB',
            ]))
            ->assertRedirect(route('guru.observasi.edit', $this->observasi))
            ->assertSessionHasErrors('kategori_respons');

        $this->assertDatabaseHas('observasi', [
            'id' => $this->observasi->id,
            'kategori_respons' => KategoriRespons::TerlibatAktif->value,
        ]);
        $this->assertDatabaseCount('riwayat_perubahan', 0);
    }

    /**
     * TC-08-09
     * Tanggal observasi setelah hari ini ditolak.
     */
    public function test_tc_08_09_menolak_tanggal_setelah_hari_ini(): void
    {
        $this->actingAs($this->guru)
            ->from(route('guru.observasi.edit', $this->observasi))
            ->put(route('guru.observasi.update', $this->observasi), $this->dataUbah([
                'tanggal' => '2026-11-15',
            ]))
            ->assertRedirect(route('guru.observasi.edit', $this->observasi))
            ->assertSessionHasErrors('tanggal');

        $this->assertDatabaseHas('observasi', [
            'id' => $this->observasi->id,
            'tanggal' => '2026-11-10',
        ]);
    }

    /**
     * TC-08-10
     * Guru lain tidak boleh mengubah observasi guru pemilik (404)
     * dan data observasi tidak berubah.
     */
    public function test_tc_08_10_guru_lain_tidak_bisa_mengubah_observasi(): void
    {
        $this->actingAs($this->guruLain)
            ->put(route('guru.observasi.update', $this->observasi), $this->dataUbah([
                'catatan_pendukung' => 'Diubah guru lain.',
            ]))
            ->assertNotFound();

        $this->assertDatabaseHas('observasi', [
            'id' => $this->observasi->id,
            'catatan_pendukung' => 'Catatan awal pengamatan.',
        ]);
        $this->assertDatabaseCount('riwayat_perubahan', 0);
    }

    /**
     * TC-08-11
     * Guru lain tidak boleh menghapus observasi guru pemilik (404).
     */
    public function test_tc_08_11_guru_lain_tidak_bisa_menghapus_observasi(): void
    {
        $this->actingAs($this->guruLain)
            ->delete(route('guru.observasi.destroy', $this->observasi))
            ->assertNotFound();

        $this->assertDatabaseHas('observasi', ['id' => $this->observasi->id]);
    }

    /**
     * TC-08-12
     * Menghapus observasi milik guru sendiri menghapus data beserta
     * riwayat perubahannya.
     */
    public function test_tc_08_12_menghapus_observasi_beserta_riwayatnya(): void
    {
        RiwayatPerubahan::create([
            'dilakukan_oleh' => $this->guru->id,
            'observasi_id' => $this->observasi->id,
            'aksi' => 'ubah',
            'keterangan' => 'Catatan pendukung: a -> b',
        ]);

        $this->actingAs($this->guru)
            ->delete(route('guru.observasi.destroy', $this->observasi))
            ->assertRedirect(route('guru.observasi.index'))
            ->assertSessionHas('notifikasi');

        $this->assertDatabaseMissing('observasi', ['id' => $this->observasi->id]);
        $this->assertDatabaseMissing('riwayat_perubahan', [
            'observasi_id' => $this->observasi->id,
        ]);
        $this->assertDatabaseCount('observasi', 0);

        // Observasi yang sudah dihapus tidak dapat dibuka lagi.
        $this->actingAs($this->guru)
            ->get(route('guru.observasi.show', $this->observasi->id))
            ->assertNotFound();
    }

    /**
     * TC-08-13
     * Menampilkan halaman riwayat perubahan observasi.
     */
    public function test_tc_08_13_menampilkan_riwayat_perubahan_observasi(): void
    {
        RiwayatPerubahan::create([
            'dilakukan_oleh' => $this->guru->id,
            'observasi_id' => $this->observasi->id,
            'aksi' => 'ubah',
            'keterangan' => "Respons anak: Terlibat aktif -> Kurang tertarik\nCatatan pendukung: a -> b",
        ]);

        $this->actingAs($this->guru)
            ->get(route('guru.observasi.riwayat', $this->observasi))
            ->assertOk()
            ->assertSee('Riwayat Perubahan')
            ->assertSee('Mia Stacia')
            ->assertSee('Diubah')
            ->assertSee('Respons anak: Terlibat aktif -&gt; Kurang tertarik', false)
            ->assertSee('Catatan pendukung: a -&gt; b', false);
    }

    /**
     * TC-08-14
     * Guru lain tidak boleh membuka riwayat perubahan observasi (404).
     */
    public function test_tc_08_14_guru_lain_mendapat_404_saat_membuka_riwayat(): void
    {
        $this->actingAs($this->guruLain)
            ->get(route('guru.observasi.riwayat', $this->observasi))
            ->assertNotFound();
    }

    /**
     * TC-08-15
     * Daftar observasi menampilkan tautan aksi Detail, Ubah, dan Riwayat.
     */
    public function test_tc_08_15_daftar_observasi_menampilkan_tautan_aksi(): void
    {
        $this->actingAs($this->guru)
            ->get(route('guru.observasi.index'))
            ->assertOk()
            ->assertSee(route('guru.observasi.show', $this->observasi), false)
            ->assertSee(route('guru.observasi.edit', $this->observasi), false)
            ->assertSee(route('guru.observasi.riwayat', $this->observasi), false);
    }

    /**
     * Menyiapkan data ubah observasi. Kelas dan anak tidak diubah pada PBI-8.
     *
     * @param  array<string, mixed>  $tambahan
     * @return array<string, mixed>
     */
    private function dataUbah(array $tambahan = []): array
    {
        return array_merge([
            'kelas_id' => $this->kelasGuru->id,
            'anak_id' => $this->anak->id,
            'tanggal' => '2026-11-10',
            'aktivitas_id' => $this->observasi->aktivitas_id,
            'aspek_id' => $this->observasi->aspek_id,
            'strategi_id' => $this->observasi->strategi_id,
            'kategori_respons' => $this->observasi->kategori_respons->value,
            'catatan_pendukung' => $this->observasi->catatan_pendukung,
        ], $tambahan);
    }
}
