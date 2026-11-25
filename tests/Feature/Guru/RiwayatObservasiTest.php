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
use Illuminate\Support\Facades\Route;
use Tests\TestCase;

// Fitur: PBI-11 Riwayat Observasi Anak dengan Filter
//
// Feature: logika data, batas periode, hak akses, dan validasi
// (TC-11-04, 13, 14, 15, 16, 17 + test pendukung).
// Tampilan dan interaksi filter di layar ada di tests/Browser/RiwayatObservasiTest.php
// (TC-11-01, 02, 03, 05, 06, 07, 08, 09, 10, 11, 12).
class RiwayatObservasiTest extends TestCase
{
    use RefreshDatabase;

    private User $guruA;

    private Kelas $kelasA;

    private Anak $anak1;

    private Anak $anak2;

    private Anak $anak3;

    private Anak $anak4;

    private Anak $anakKelasLain;

    /** @var array<string, AspekPerkembangan> */
    private array $aspek = [];

    /** @var array<string, Aktivitas> */
    private array $aktivitas = [];

    /** @var array<string, StrategiPembelajaran> */
    private array $strategi = [];

    protected function setUp(): void
    {
        parent::setUp();

        $this->guruA = User::factory()->create([
            'nama' => 'Mia Stacia',
            'nama_pengguna' => 'miastacia',
        ]);

        $guruB = User::factory()->create(['nama_pengguna' => 'ayurizki']);

        $this->kelasA = Kelas::factory()->create([
            'guru_id' => $this->guruA->id,
            'nama_kelas' => 'Melati',
            'kelompok' => 'TK A',
        ]);

        $kelasB = Kelas::factory()->create([
            'guru_id' => $guruB->id,
            'nama_kelas' => 'Cendrawasih',
            'kelompok' => 'TK B',
        ]);

        foreach (['Kognitif', 'Bahasa', 'Fisik Motorik', 'Sosial Emosional', 'Seni'] as $nama) {
            $this->aspek[$nama] = AspekPerkembangan::factory()->create(['nama' => $nama]);
        }

        foreach (['Menyusun balok', 'Bernyanyi bersama', 'Menempel kertas', 'Mewarnai gambar', 'Bermain peran'] as $nama) {
            $this->aktivitas[$nama] = Aktivitas::factory()->create(['nama' => $nama]);
        }

        foreach (['Media visual', 'Kelompok kecil', 'Permainan gerak'] as $nama) {
            $this->strategi[$nama] = StrategiPembelajaran::factory()->create(['nama' => $nama]);
        }

        $this->anak1 = Anak::factory()->create([
            'kelas_id' => $this->kelasA->id,
            'nama' => 'Dedek Tirta',
            'nama_panggilan' => 'Tirta',
        ]);

        $this->anak2 = Anak::factory()->create([
            'kelas_id' => $this->kelasA->id,
            'nama_panggilan' => 'Waluyo',
        ]);

        $this->anak3 = Anak::factory()->create([
            'kelas_id' => $this->kelasA->id,
            'nama_panggilan' => 'Puspa',
        ]);

        $this->anak4 = Anak::factory()->nonaktif()->create([
            'kelas_id' => $this->kelasA->id,
            'nama_panggilan' => 'Sadina',
        ]);

        $this->anakKelasLain = Anak::factory()->create([
            'kelas_id' => $kelasB->id,
            'nama_panggilan' => 'Cagak',
        ]);

        $this->buatRiwayatAnak1();
    }

    protected function tearDown(): void
    {
        // Kembalikan waktu asli setelah test yang memakai travelTo().
        Carbon::setTestNow();

        parent::tearDown();
    }

    public function test_tc_11_04_batas_periode_masuk_perhitungan(): void
    {
        $anakBatas = Anak::factory()->create([
            'kelas_id' => $this->kelasA->id,
            'nama_panggilan' => 'Batas',
        ]);

        foreach (['2026-07-31' => 'Catatan sebelum periode', '2026-08-01' => 'Catatan hari pertama', '2026-08-31' => 'Catatan hari terakhir'] as $tanggal => $catatan) {
            $this->buatObservasi($anakBatas, $tanggal, 'Kognitif', 'Menyusun balok', 'Media visual', KategoriRespons::KurangTertarik, $catatan);
        }

        $this->actingAs($this->guruA)
            ->get(route('guru.riwayat-observasi', ['anak' => $anakBatas->id, 'periode' => '2026-08']))
            ->assertOk()
            ->assertSee('2 dari 3 catatan')
            ->assertSee('Catatan hari pertama')
            ->assertSee('Catatan hari terakhir')
            ->assertDontSee('Catatan sebelum periode');
    }

    public function test_periode_30_dan_7_hari_dihitung_dari_hari_ini(): void
    {
        $this->travelTo('2026-10-20 09:00:00');

        // 7 hari terakhir = 14-20 Okt 2026, hanya observasi 15 Okt yang masuk.
        $this->actingAs($this->guruA)
            ->get(route('guru.riwayat-observasi', ['anak' => $this->anak1->id, 'periode' => '7']))
            ->assertOk()
            ->assertSee('1 dari 14 catatan')
            ->assertSee('Catatan ke-14')
            ->assertDontSee('Catatan ke-13');

        // 30 hari terakhir = 21 Sep - 20 Okt 2026, seluruh 14 catatan masuk.
        $this->actingAs($this->guruA)
            ->get(route('guru.riwayat-observasi', ['anak' => $this->anak1->id, 'periode' => '30']))
            ->assertOk()
            ->assertSee('14 dari 14 catatan');

        // Setelah lewat 30 hari dari observasi terakhir, filter 30 hari tidak menampilkan apa pun.
        $this->travelTo('2026-11-14 09:00:00');

        $this->actingAs($this->guruA)
            ->get(route('guru.riwayat-observasi', ['anak' => $this->anak1->id, 'periode' => '30']))
            ->assertOk()
            ->assertSee('0 dari 14 catatan')
            ->assertSee('Belum ada observasi yang sesuai');
    }

    public function test_default_periode_semua_tetap_menampilkan_data_lama(): void
    {
        // Default "semua periode" tidak bergantung pada tanggal hari ini.
        $this->travelTo('2030-01-01 08:00:00');

        $this->actingAs($this->guruA)
            ->get(route('guru.riwayat-observasi', $this->anak1->id))
            ->assertOk()
            ->assertSee('14 catatan')
            ->assertDontSee('Belum ada observasi yang sesuai');

        $this->assertSame(14, substr_count($this->get(route('guru.riwayat-observasi', $this->anak1->id))->getContent(), 'Catatan ke-'));
    }

    public function test_tc_11_13_hanya_menampilkan_observasi_anak_yang_dipilih(): void
    {
        $this->buatObservasi($this->anak3, '2026-10-12', 'Seni', 'Mewarnai gambar', 'Media visual', KategoriRespons::TerlibatAktif, 'Catatan anak lain');

        $this->actingAs($this->guruA)
            ->get(route('guru.riwayat-observasi', $this->anak1->id))
            ->assertOk()
            ->assertSee('Catatan ke-14')
            ->assertDontSee('Catatan anak lain');
    }

    public function test_tc_11_14_anak_nonaktif_tetap_punya_riwayat(): void
    {
        $this->buatObservasi($this->anak4, '2026-09-15', 'Sosial Emosional', 'Bermain peran', 'Kelompok kecil', KategoriRespons::TerlibatDenganPendampingan, 'Catatan anak nonaktif');

        $this->actingAs($this->guruA)
            ->get(route('guru.riwayat-observasi', $this->anak4->id))
            ->assertOk()
            ->assertSee('Catatan anak nonaktif')
            ->assertSee('1 catatan');
    }

    public function test_tc_11_15_akses_anak_di_kelas_guru_lain_ditolak(): void
    {
        $this->buatObservasi($this->anakKelasLain, '2026-10-10', 'Kognitif', 'Menyusun balok', 'Kelompok kecil', KategoriRespons::TerlibatAktif, 'Catatan anak kelas lain');

        // Tombol kembali mengikuti route Data Anak bila sudah ada (PBI-5),
        // dan sementara jatuh ke dashboard selama route itu belum terdaftar.
        $tautanDataAnak = Route::has('guru.anak.index')
            ? route('guru.anak.index')
            : route('guru.dashboard');

        $this->actingAs($this->guruA)
            ->get(route('guru.riwayat-observasi', $this->anakKelasLain->id))
            ->assertForbidden()
            ->assertSee('Akses ditolak')
            ->assertSee('Anda tidak memiliki akses ke data anak ini')
            ->assertSee('Kembali ke Data Anak')
            ->assertSee('href="'.$tautanDataAnak.'"', false)
            ->assertDontSee('Catatan anak kelas lain');
    }

    public function test_halaman_403_tetap_memakai_layout_bersama(): void
    {
        $this->actingAs($this->guruA)
            ->get(route('guru.riwayat-observasi', $this->anakKelasLain->id))
            ->assertForbidden()
            ->assertSee('SIPANDAI')
            ->assertSee($this->guruA->nama)
            ->assertSee('Keluar');
    }

    public function test_tc_11_16_akses_tanpa_login_dialihkan_ke_halaman_login(): void
    {
        $this->get(route('guru.riwayat-observasi', $this->anak1->id))
            ->assertRedirect(route('login'));
    }

    public function test_tc_11_17_kategori_respons_hanya_empat_tanpa_label_diagnosis(): void
    {
        $respons = $this->actingAs($this->guruA)
            ->get(route('guru.riwayat-observasi', $this->anak1->id))
            ->assertOk()
            ->assertSee('Terlibat aktif')
            ->assertSee('Terlibat dengan pendampingan')
            ->assertSee('Kurang tertarik')
            ->assertSee('Belum terlibat')
            ->assertDontSee('Diagnosis')
            ->assertDontSee('hiperaktif')
            ->assertDontSee('autis');

        foreach (KategoriRespons::cases() as $kategori) {
            $this->assertSame(1, substr_count($respons->getContent(), 'value="'.$kategori->value.'"'));
        }
    }

    public function test_filter_periode_tidak_dikenal_ditolak(): void
    {
        $this->actingAs($this->guruA)
            ->get(route('guru.riwayat-observasi', ['anak' => $this->anak1->id, 'periode' => 'bulan-lalu']))
            ->assertSessionHasErrors('periode');
    }

    public function test_opsi_periode_hanya_bulan_yang_ada_pada_riwayat(): void
    {
        $this->actingAs($this->guruA)
            ->get(route('guru.riwayat-observasi', $this->anak1->id))
            ->assertOk()
            ->assertSee('Oktober 2026')
            ->assertSee('September 2026')
            ->assertDontSee('Agustus 2026');
    }

    public function test_judul_riwayat_memakai_nama_lengkap_seperti_halaman_observasi(): void
    {
        $this->actingAs($this->guruA)
            ->get(route('guru.riwayat-observasi', $this->anak1->id))
            ->assertOk()
            ->assertSee('Dedek Tirta')
            ->assertSee('Tirta');
    }

    public function test_tautan_lihat_perubahan_membuka_riwayat_perubahan_observasi(): void
    {
        $observasi = Observasi::where('anak_id', $this->anak1->id)->latest('tanggal')->firstOrFail();

        RiwayatPerubahan::create([
            'dilakukan_oleh' => $this->guruA->id,
            'observasi_id' => $observasi->id,
            'aksi' => 'ubah',
            'keterangan' => 'Kategori respons diperbarui.',
        ]);

        $this->actingAs($this->guruA)
            ->get(route('guru.riwayat-observasi', $this->anak1->id))
            ->assertOk()
            ->assertSee('Diubah 1x')
            ->assertSee('href="'.route('guru.observasi.riwayat', $observasi).'"', false);
    }

    public function test_lihat_perubahan_tidak_ditautkan_untuk_observasi_guru_lain(): void
    {
        // Observasi dicatat guru lain (misalnya pengampu sebelumnya); halaman riwayat
        // perubahannya hanya boleh dibuka pencatatnya, jadi tidak diberi tautan.
        $observasi = $this->buatObservasi($this->anak1, '2026-10-20', 'Seni', 'Mewarnai gambar', 'Media visual', KategoriRespons::TerlibatAktif, 'Catatan guru lain');
        $observasi->update(['guru_id' => User::factory()->create()->id]);

        RiwayatPerubahan::create([
            'dilakukan_oleh' => $observasi->guru_id,
            'observasi_id' => $observasi->id,
            'aksi' => 'ubah',
            'keterangan' => 'Catatan diperbarui.',
        ]);

        $this->actingAs($this->guruA)
            ->get(route('guru.riwayat-observasi', $this->anak1->id))
            ->assertOk()
            ->assertSee('Catatan guru lain')
            ->assertSee('Diubah 1x')
            ->assertDontSee('href="'.route('guru.observasi.riwayat', $observasi).'"', false);
    }

    /**
     * 14 observasi data uji PBI-11: 12 Oktober 2026 + 2 September 2026,
     * dengan 4 terlibat aktif, 3 pendampingan, 6 kurang tertarik, 1 belum terlibat.
     */
    private function buatRiwayatAnak1(): void
    {
        $baris = [
            ['2026-09-22', 'Fisik Motorik', 'Bernyanyi bersama', 'Kelompok kecil', KategoriRespons::TerlibatAktif],
            ['2026-09-28', 'Kognitif', 'Menyusun balok', 'Kelompok kecil', KategoriRespons::KurangTertarik],
            ['2026-10-01', 'Fisik Motorik', 'Menempel kertas', 'Media visual', KategoriRespons::BelumTerlibat],
            ['2026-10-01', 'Kognitif', 'Menyusun balok', 'Kelompok kecil', KategoriRespons::KurangTertarik],
            ['2026-10-01', 'Bahasa', 'Menempel kertas', 'Media visual', KategoriRespons::TerlibatDenganPendampingan],
            ['2026-10-03', 'Kognitif', 'Menyusun balok', 'Media visual', KategoriRespons::KurangTertarik],
            ['2026-10-03', 'Sosial Emosional', 'Bermain peran', 'Kelompok kecil', KategoriRespons::TerlibatAktif],
            ['2026-10-05', 'Fisik Motorik', 'Mewarnai gambar', 'Media visual', KategoriRespons::TerlibatDenganPendampingan],
            ['2026-10-07', 'Bahasa', 'Bernyanyi bersama', 'Kelompok kecil', KategoriRespons::TerlibatAktif],
            ['2026-10-09', 'Kognitif', 'Menyusun balok', 'Media visual', KategoriRespons::KurangTertarik],
            ['2026-10-09', 'Sosial Emosional', 'Bermain peran', 'Kelompok kecil', KategoriRespons::KurangTertarik],
            ['2026-10-13', 'Fisik Motorik', 'Bernyanyi bersama', 'Permainan gerak', KategoriRespons::TerlibatAktif],
            ['2026-10-13', 'Bahasa', 'Menempel kertas', 'Media visual', KategoriRespons::TerlibatDenganPendampingan],
            ['2026-10-15', 'Kognitif', 'Menyusun balok', 'Kelompok kecil', KategoriRespons::KurangTertarik],
        ];

        foreach ($baris as $indeks => [$tanggal, $aspek, $aktivitas, $strategi, $respons]) {
            $this->buatObservasi(
                $this->anak1,
                $tanggal,
                $aspek,
                $aktivitas,
                $strategi,
                $respons,
                'Catatan ke-'.str_pad((string) ($indeks + 1), 2, '0', STR_PAD_LEFT),
            );
        }
    }

    private function buatObservasi(
        Anak $anak,
        string $tanggal,
        string $aspek,
        string $aktivitas,
        string $strategi,
        KategoriRespons $respons,
        string $catatan,
    ): Observasi {
        return Observasi::factory()->create([
            'anak_id' => $anak->id,
            'aspek_id' => $this->aspek[$aspek]->id,
            'aktivitas_id' => $this->aktivitas[$aktivitas]->id,
            'strategi_id' => $this->strategi[$strategi]->id,
            'tanggal' => $tanggal,
            'kategori_respons' => $respons,
            'catatan_pendukung' => $catatan,
        ]);
    }
}
