<?php

namespace Tests\Feature\Database;

use App\Enums\KategoriRespons;
use App\Models\Aktivitas;
use App\Models\AlternatifStrategi;
use App\Models\Anak;
use App\Models\AspekPerkembangan;
use App\Models\AturanStrategi;
use App\Models\EvaluasiTindakLanjut;
use App\Models\Kelas;
use App\Models\Laporan;
use App\Models\Observasi;
use App\Models\RiwayatPerubahan;
use App\Models\Rpp;
use App\Models\StrategiPembelajaran;
use App\Models\TindakLanjut;
use App\Models\User;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\QueryException;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use PHPUnit\Framework\Attributes\DataProvider;
use Tests\TestCase;

class SkemaDatabaseTest extends TestCase
{
    use RefreshDatabase;

    /**
     * @return array<string, array{string, list<string>}>
     */
    public static function daftarTabel(): array
    {
        return [
            'aspek_perkembangan' => ['aspek_perkembangan', ['id', 'nama', 'deskripsi']],
            'aktivitas' => ['aktivitas', ['id', 'nama', 'deskripsi', 'aktif']],
            'strategi_pembelajaran' => ['strategi_pembelajaran', ['id', 'nama', 'deskripsi', 'aktif']],
            'kelas' => ['kelas', ['id', 'guru_id', 'nama_kelas', 'kelompok', 'tahun_ajaran']],
            'anak' => ['anak', ['id', 'kelas_id', 'nama', 'nama_panggilan', 'tanggal_lahir', 'aktif']],
            'aturan_strategi' => ['aturan_strategi', [
                'id', 'aspek_id', 'strategi_asal_id', 'respons_pemicu', 'minimal_kejadian',
                'strategi_alternatif_id', 'keterangan', 'aktif',
            ]],
            'alternatif_strategi' => ['alternatif_strategi', [
                'id', 'anak_id', 'strategi_id', 'aturan_id', 'dasar_saran', 'status', 'dibuat_pada',
            ]],
            'tindak_lanjut' => ['tindak_lanjut', [
                'id', 'alternatif_id', 'rencana', 'alasan_dugaan_penyebab', 'tanggal_mulai', 'status',
            ]],
            'evaluasi_tindak_lanjut' => ['evaluasi_tindak_lanjut', [
                'id', 'tindak_lanjut_id', 'tanggal', 'kesimpulan', 'catatan',
            ]],
            'rpp' => ['rpp', [
                'id', 'guru_id', 'kelas_id', 'judul', 'jenis', 'tanggal_mulai', 'tanggal_selesai',
                'nama_file', 'path_file', 'tipe_file', 'ukuran_file', 'catatan', 'diunggah_pada', 'diubah_pada',
            ]],
            'observasi' => ['observasi', [
                'id', 'anak_id', 'guru_id', 'aktivitas_id', 'strategi_id', 'aspek_id', 'rpp_id',
                'tindak_lanjut_id', 'tanggal', 'kategori_respons', 'catatan_pendukung', 'dibuat_pada', 'diubah_pada',
            ]],
            'laporan' => ['laporan', [
                'id', 'guru_id', 'jenis', 'anak_id', 'kelas_id', 'periode_mulai', 'periode_selesai', 'dibuat_pada',
            ]],
            'riwayat_perubahan' => ['riwayat_perubahan', [
                'id', 'dilakukan_oleh', 'observasi_id', 'tindak_lanjut_id', 'aksi', 'keterangan', 'waktu',
            ]],
        ];
    }

    /**
     * @param  list<string>  $kolom
     */
    #[DataProvider('daftarTabel')]
    public function test_tabel_dan_semua_kolomnya_tersedia(string $tabel, array $kolom): void
    {
        $this->assertTrue(Schema::hasTable($tabel), "Tabel {$tabel} tidak ada.");
        $this->assertTrue(Schema::hasColumns($tabel, $kolom), "Kolom tabel {$tabel} tidak lengkap.");
        $this->assertEqualsCanonicalizing($kolom, Schema::getColumnListing($tabel), "Tabel {$tabel} punya kolom di luar ERD.");
    }

    /**
     * @return array<string, array{class-string<Model>}>
     */
    public static function daftarModel(): array
    {
        return [
            'aspek_perkembangan' => [AspekPerkembangan::class],
            'aktivitas' => [Aktivitas::class],
            'strategi_pembelajaran' => [StrategiPembelajaran::class],
            'kelas' => [Kelas::class],
            'anak' => [Anak::class],
            'aturan_strategi' => [AturanStrategi::class],
            'alternatif_strategi' => [AlternatifStrategi::class],
            'tindak_lanjut' => [TindakLanjut::class],
            'evaluasi_tindak_lanjut' => [EvaluasiTindakLanjut::class],
            'rpp' => [Rpp::class],
            'observasi' => [Observasi::class],
            'laporan' => [Laporan::class],
            'riwayat_perubahan' => [RiwayatPerubahan::class],
        ];
    }

    /**
     * @param  class-string<Model>  $model
     */
    #[DataProvider('daftarModel')]
    public function test_factory_dapat_membuat_data(string $model): void
    {
        $data = $model::factory()->create();

        $this->assertModelExists($data);
    }

    public function test_relasi_kelas_dan_anak(): void
    {
        $anak = Anak::factory()->create();
        $kelas = $anak->kelas;

        $this->assertInstanceOf(Kelas::class, $kelas);
        $this->assertInstanceOf(User::class, $kelas->guru);
        $this->assertTrue($kelas->guru->kelasDiampu->contains($kelas));
        $this->assertTrue($kelas->anak->contains($anak));
    }

    public function test_relasi_observasi(): void
    {
        $observasi = Observasi::factory()->create();

        $this->assertInstanceOf(Anak::class, $observasi->anak);
        $this->assertInstanceOf(User::class, $observasi->guru);
        $this->assertInstanceOf(Aktivitas::class, $observasi->aktivitas);
        $this->assertInstanceOf(StrategiPembelajaran::class, $observasi->strategi);
        $this->assertInstanceOf(AspekPerkembangan::class, $observasi->aspek);
        $this->assertInstanceOf(KategoriRespons::class, $observasi->kategori_respons);
        $this->assertTrue($observasi->anak->observasi->contains($observasi));
        $this->assertTrue($observasi->guru->observasi->contains($observasi));
    }

    public function test_laporan_per_anak_dan_per_kelas_dibuat_oleh_guru_pengampu(): void
    {
        $laporanKelas = Laporan::factory()->create();
        $laporanAnak = Laporan::factory()->untukAnak()->create();

        $this->assertSame('kelas', $laporanKelas->jenis);
        $this->assertNull($laporanKelas->anak_id);
        $this->assertSame($laporanKelas->kelas->guru_id, $laporanKelas->guru_id);

        $this->assertSame('anak', $laporanAnak->jenis);
        $this->assertNull($laporanAnak->kelas_id);
        $this->assertSame($laporanAnak->anak->kelas->guru_id, $laporanAnak->guru_id);
    }

    public function test_relasi_aturan_ke_strategi_asal_dan_alternatif(): void
    {
        $aturan = AturanStrategi::factory()->create();

        $this->assertInstanceOf(StrategiPembelajaran::class, $aturan->strategiAsal);
        $this->assertInstanceOf(StrategiPembelajaran::class, $aturan->strategiAlternatif);
        $this->assertNotEquals($aturan->strategi_asal_id, $aturan->strategi_alternatif_id);
        $this->assertInstanceOf(KategoriRespons::class, $aturan->respons_pemicu);
        $this->assertTrue($aturan->strategiAsal->aturanSebagaiAsal->contains($aturan));
        $this->assertTrue($aturan->strategiAlternatif->aturanSebagaiAlternatif->contains($aturan));
    }

    public function test_relasi_alternatif_dan_tindak_lanjut(): void
    {
        $tindakLanjut = TindakLanjut::factory()->create();
        $alternatif = $tindakLanjut->alternatif;

        $this->assertInstanceOf(AlternatifStrategi::class, $alternatif);
        $this->assertTrue($alternatif->tindakLanjut->is($tindakLanjut));
    }

    public function test_satu_alternatif_hanya_boleh_punya_satu_tindak_lanjut(): void
    {
        $tindakLanjut = TindakLanjut::factory()->create();

        $this->expectException(QueryException::class);

        TindakLanjut::factory()->create(['alternatif_id' => $tindakLanjut->alternatif_id]);
    }

    public function test_data_yang_masih_dipakai_tidak_dapat_dihapus(): void
    {
        $anak = Anak::factory()->create();

        $this->expectException(QueryException::class);

        $anak->kelas->delete();
    }

    public function test_nilai_bawaan_kolom_sesuai_erd(): void
    {
        $anak = Anak::factory()->create();
        $strategi = StrategiPembelajaran::factory()->create();

        $id = DB::table('alternatif_strategi')->insertGetId([
            'anak_id' => $anak->id,
            'strategi_id' => $strategi->id,
        ]);

        $this->assertSame('disarankan', AlternatifStrategi::find($id)->status);
        $this->assertTrue(Aktivitas::find(Aktivitas::factory()->create()->id)->aktif);

        $riwayatId = DB::table('riwayat_perubahan')->insertGetId([
            'dilakukan_oleh' => User::factory()->create()->id,
            'aksi' => 'tambah',
        ]);

        $this->assertNotNull(RiwayatPerubahan::find($riwayatId)->waktu);
    }

    public function test_kolom_waktu_bernama_khusus_terisi_otomatis(): void
    {
        $observasi = Observasi::factory()->create();
        $rpp = Rpp::factory()->create();
        $alternatif = AlternatifStrategi::factory()->create();
        $laporan = Laporan::factory()->create();

        $this->assertNotNull($observasi->dibuat_pada);
        $this->assertNotNull($observasi->diubah_pada);
        $this->assertNotNull($rpp->diunggah_pada);
        $this->assertNotNull($rpp->diubah_pada);
        $this->assertNotNull($alternatif->dibuat_pada);
        $this->assertNotNull($laporan->dibuat_pada);
    }

    public function test_seeder_membuat_dua_kelas_dan_dua_guru_tanpa_kelas(): void
    {
        $this->seed();

        $this->assertSame(2, Kelas::count());
        $this->assertSame(
            2,
            User::where('peran', 'guru')->whereDoesntHave('kelasDiampu')->count(),
        );

        $this->assertSame('miastacia', Kelas::where('kelompok', 'TK A')->first()->guru->nama_pengguna);
        $this->assertSame('ayurizki', Kelas::where('kelompok', 'TK B')->first()->guru->nama_pengguna);

        foreach (Kelas::all() as $kelas) {
            $this->assertSame(5, $kelas->anak()->count());
        }
    }

    public function test_seeder_membuat_data_master_dan_satu_aturan_aktif(): void
    {
        $this->seed();

        $this->assertSame(6, AspekPerkembangan::count());
        $this->assertSame(4, StrategiPembelajaran::count());
        $this->assertGreaterThan(0, Aktivitas::count());

        $aturan = AturanStrategi::sole();

        $this->assertTrue($aturan->aktif);
        $this->assertNull($aturan->aspek_id);
        $this->assertSame('Bercerita Lisan', $aturan->strategiAsal->nama);
        $this->assertSame('Media Visual', $aturan->strategiAlternatif->nama);
        $this->assertSame(KategoriRespons::KurangTertarik, $aturan->respons_pemicu);
        $this->assertSame(3, $aturan->minimal_kejadian);

        $this->assertSame(0, Observasi::count());
        $this->assertSame(0, AlternatifStrategi::count());
        $this->assertSame(0, TindakLanjut::count());
        $this->assertSame(0, Rpp::count());
        $this->assertSame(0, Laporan::count());
        $this->assertSame(0, RiwayatPerubahan::count());
    }
}
