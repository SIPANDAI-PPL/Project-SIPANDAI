<?php

namespace Tests\Feature\Guru;

use App\Enums\KategoriRespons;
use App\Models\AlternatifStrategi;
use App\Models\Anak;
use App\Models\Kelas;
use App\Models\RiwayatPerubahan;
use App\Models\StrategiPembelajaran;
use App\Models\TindakLanjut;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Carbon;
use Tests\TestCase;

// Fitur: PBI-17 Pencatatan Tindak Lanjut
class TindakLanjutTest extends TestCase
{
    use RefreshDatabase;

    private User $guru;

    private Anak $anak;

    private AlternatifStrategi $alternatif;

    protected function setUp(): void
    {
        parent::setUp();

        Carbon::setTestNow('2026-11-22 09:00:00');

        // Data dummy: satu guru, satu anak, satu alternatif strategi berstatus dipilih tanpa tindak lanjut.
        $this->guru = User::factory()->create(['nama' => 'Guru A']);
        $kelas = Kelas::factory()->create(['guru_id' => $this->guru->id]);
        $this->anak = Anak::factory()->create(['kelas_id' => $kelas->id, 'nama' => 'Dedek', 'aktif' => true]);
        $strategi = StrategiPembelajaran::factory()->create(['nama' => 'Media visual', 'deskripsi' => 'Memakai gambar dan kartu.']);
        $this->alternatif = AlternatifStrategi::factory()->create([
            'anak_id' => $this->anak->id,
            'strategi_id' => $strategi->id,
            'status' => 'dipilih',
        ]);
    }

    protected function tearDown(): void
    {
        Carbon::setTestNow();

        parent::tearDown();
    }

    /**
     * @param  array<string, mixed>  $ubah
     * @return array<string, mixed>
     */
    private function dataValid(array $ubah = []): array
    {
        return array_merge([
            'anak_id' => $this->anak->id,
            'alternatif_id' => $this->alternatif->id,
            'kategori_respons' => KategoriRespons::KurangTertarik->value,
            'tanggal' => '2026-11-22',
            'catatan' => 'Cepat beralih saat hanya mendengarkan cerita.',
        ], $ubah);
    }

    // TC-17-01 | Menyimpan tindak lanjut dengan tanggal hari ini -> status berjalan
    public function test_tc_17_01_simpan_dengan_tanggal_hari_ini_status_berjalan(): void
    {
        $this->actingAs($this->guru)
            ->post(route('guru.tindak-lanjut.store'), $this->dataValid(['tanggal' => '2026-11-22']))
            ->assertRedirect(route('guru.tindak-lanjut.riwayat'))
            ->assertSessionHas('status', 'Tindak lanjut untuk Dedek berhasil dicatat.');

        $tindakLanjut = $this->alternatif->tindakLanjut()->firstOrFail();
        $this->assertSame('2026-11-22', $tindakLanjut->tanggal_mulai->toDateString());
        $this->assertSame('berjalan', $tindakLanjut->status);
    }

    // TC-17-02 | Menyimpan tindak lanjut dengan tanggal masa depan -> status direncanakan
    public function test_tc_17_02_simpan_dengan_tanggal_masa_depan_status_direncanakan(): void
    {
        $this->actingAs($this->guru)
            ->post(route('guru.tindak-lanjut.store'), $this->dataValid(['tanggal' => '2026-11-25']))
            ->assertRedirect(route('guru.tindak-lanjut.riwayat'))
            ->assertSessionHasNoErrors();

        $tindakLanjut = $this->alternatif->tindakLanjut()->firstOrFail();
        $this->assertSame('2026-11-25', $tindakLanjut->tanggal_mulai->toDateString());
        $this->assertSame('direncanakan', $tindakLanjut->status);
    }

    // TC-17-03 | Menyimpan tindak lanjut tanpa catatan pendukung -> tetap tersimpan
    public function test_tc_17_03_simpan_tanpa_catatan_pendukung_tetap_tersimpan(): void
    {
        $this->actingAs($this->guru)
            ->post(route('guru.tindak-lanjut.store'), $this->dataValid(['catatan' => '']))
            ->assertRedirect(route('guru.tindak-lanjut.riwayat'))
            ->assertSessionHasNoErrors();

        $tindakLanjut = $this->alternatif->tindakLanjut()->firstOrFail();
        $this->assertNull($tindakLanjut->alasan_dugaan_penyebab);
        $this->assertSame(KategoriRespons::KurangTertarik, $tindakLanjut->kategori_respons_awal);
    }

    // TC-17-04 | Mengubah tindak lanjut -> riwayat menampilkan tulisan "Diubah" beserta waktunya
    public function test_tc_17_04_ubah_tindak_lanjut_menampilkan_tulisan_diubah_dan_waktunya(): void
    {
        $this->actingAs($this->guru)->post(route('guru.tindak-lanjut.store'), $this->dataValid())->assertSessionHasNoErrors();
        $tindakLanjut = $this->alternatif->tindakLanjut()->firstOrFail();

        Carbon::setTestNow('2026-11-22 10:30:00');

        $this->actingAs($this->guru)
            ->put(route('guru.tindak-lanjut.update', $tindakLanjut), [
                'tanggal' => '2026-11-22',
                'catatan' => 'Lebih fokus saat cerita dibantu kartu gambar.',
            ])
            ->assertRedirect(route('guru.tindak-lanjut.riwayat'))
            ->assertSessionHas('status', 'Tindak lanjut untuk Dedek berhasil diubah.');

        $this->assertSame('Lebih fokus saat cerita dibantu kartu gambar.', $tindakLanjut->fresh()->alasan_dugaan_penyebab);
        $this->assertDatabaseHas('riwayat_perubahan', [
            'tindak_lanjut_id' => $tindakLanjut->id,
            'dilakukan_oleh' => $this->guru->id,
            'aksi' => 'ubah',
        ]);

        $this->actingAs($this->guru)
            ->get(route('guru.tindak-lanjut.riwayat'))
            ->assertOk()
            ->assertSee('Diubah 22/11/2026 10:30')
            ->assertSee('Lebih fokus saat cerita dibantu kartu gambar.');
    }

    // TC-17-05 | Anak tidak dipilih -> tindak lanjut tidak tersimpan
    public function test_tc_17_05_anak_tidak_dipilih_tindak_lanjut_tidak_tersimpan(): void
    {
        $this->actingAs($this->guru)
            ->from(route('guru.tindak-lanjut.create'))
            ->post(route('guru.tindak-lanjut.store'), $this->dataValid(['anak_id' => '']))
            ->assertRedirect(route('guru.tindak-lanjut.create'))
            ->assertSessionHasErrors(['anak_id' => 'Anak wajib dipilih.']);

        $this->assertDatabaseCount('tindak_lanjut', 0);
        $this->assertSame(0, RiwayatPerubahan::query()->count());
        $this->assertNull(TindakLanjut::query()->where('alternatif_id', $this->alternatif->id)->first());
    }
}
