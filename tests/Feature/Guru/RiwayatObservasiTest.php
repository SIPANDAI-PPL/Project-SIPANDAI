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
use Tests\TestCase;

// Fitur: PBI-11 Riwayat Observasi Anak dengan Filter
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

    public function test_tc_11_01_riwayat_tampil_kronologis_dan_penghitung_14(): void
    {
        $respons = $this->actingAs($this->guruA)
            ->get(route('guru.riwayat-observasi', $this->anak1->id))
            ->assertOk()
            ->assertSee('14 catatan');

        // 14 catatan tampil semua (tanpa harus klik "Muat lebih banyak").
        $this->assertSame(14, substr_count($respons->getContent(), 'Catatan ke-'));

        // Terbaru (catatan 14) di atas, terlama (catatan 1) di bawah.
        $respons->assertSeeInOrder([
            'Catatan ke-14',
            'Catatan ke-13',
            'Catatan ke-12',
            'Catatan ke-11',
            'Catatan ke-10',
            'Catatan ke-09',
            'Catatan ke-08',
            'Catatan ke-07',
            'Catatan ke-06',
            'Catatan ke-05',
            'Catatan ke-04',
            'Catatan ke-03',
            'Catatan ke-02',
            'Catatan ke-01',
        ]);
    }

    public function test_tc_11_02_kartu_observasi_memuat_data_lengkap(): void
    {
        $this->actingAs($this->guruA)
            ->get(route('guru.riwayat-observasi', $this->anak1->id))
            ->assertOk()
            ->assertSee('15 Okt 2026')
            ->assertSee('Aspek: Kognitif')
            ->assertSee('Strategi: Media visual')
            ->assertSee('Menyusun balok')
            ->assertSee('Kurang tertarik')
            ->assertSee('Catatan ke-14');
    }

    public function test_tc_11_03_filter_periode_bulan(): void
    {
        $this->actingAs($this->guruA)
            ->get(route('guru.riwayat-observasi', ['anak' => $this->anak1->id, 'periode' => '2026-10']))
            ->assertOk()
            ->assertSee('12 dari 14 catatan')
            ->assertSee('Catatan ke-03')
            ->assertDontSee('Catatan ke-02')
            ->assertDontSee('Catatan ke-01');
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

    public function test_tc_11_05_filter_aspek(): void
    {
        $this->actingAs($this->guruA)
            ->get(route('guru.riwayat-observasi', [
                'anak' => $this->anak1->id,
                'aspek' => $this->aspek['Sosial Emosional']->id,
            ]))
            ->assertOk()
            ->assertSee('2 dari 14 catatan')
            ->assertSee('Catatan ke-11')
            ->assertSee('Catatan ke-07')
            ->assertDontSee('Catatan ke-14');
    }

    public function test_tc_11_06_filter_aktivitas(): void
    {
        $this->actingAs($this->guruA)
            ->get(route('guru.riwayat-observasi', [
                'anak' => $this->anak1->id,
                'aktivitas' => $this->aktivitas['Menyusun balok']->id,
            ]))
            ->assertOk()
            ->assertSee('5 dari 14 catatan');
    }

    public function test_tc_11_07_filter_strategi(): void
    {
        $this->actingAs($this->guruA)
            ->get(route('guru.riwayat-observasi', [
                'anak' => $this->anak1->id,
                'strategi' => $this->strategi['Media visual']->id,
            ]))
            ->assertOk()
            ->assertSee('6 dari 14 catatan');
    }

    public function test_tc_11_08_filter_kategori_respons(): void
    {
        $this->actingAs($this->guruA)
            ->get(route('guru.riwayat-observasi', [
                'anak' => $this->anak1->id,
                'respons' => KategoriRespons::KurangTertarik->value,
            ]))
            ->assertOk()
            ->assertSee('6 dari 14 catatan');
    }

    public function test_tc_11_09_kombinasi_filter_aspek_dan_respons(): void
    {
        $this->actingAs($this->guruA)
            ->get(route('guru.riwayat-observasi', [
                'anak' => $this->anak1->id,
                'aspek' => $this->aspek['Kognitif']->id,
                'respons' => KategoriRespons::KurangTertarik->value,
            ]))
            ->assertOk()
            ->assertSee('5 dari 14 catatan')
            ->assertSee('Catatan ke-14')
            ->assertDontSee('Catatan ke-12');
    }

    public function test_tc_11_10_hapus_filter_mengembalikan_seluruh_catatan(): void
    {
        $this->actingAs($this->guruA)
            ->get(route('guru.riwayat-observasi', [
                'anak' => $this->anak1->id,
                'aspek' => $this->aspek['Kognitif']->id,
                'respons' => KategoriRespons::KurangTertarik->value,
            ]))
            ->assertSee('Hapus filter')
            ->assertSee('href="'.route('guru.riwayat-observasi', $this->anak1->id).'"', false);

        $this->actingAs($this->guruA)
            ->get(route('guru.riwayat-observasi', $this->anak1->id))
            ->assertOk()
            ->assertSee('14 catatan');
    }

    public function test_tc_11_11_filter_tanpa_hasil_tampil_pesan_kosong(): void
    {
        $this->actingAs($this->guruA)
            ->get(route('guru.riwayat-observasi', [
                'anak' => $this->anak1->id,
                'aspek' => $this->aspek['Seni']->id,
                'respons' => KategoriRespons::KurangTertarik->value,
            ]))
            ->assertOk()
            ->assertSee('Belum ada observasi yang sesuai')
            ->assertSee('0 dari 14 catatan')
            ->assertSee('Hapus filter');
    }

    public function test_tc_11_12_anak_belum_punya_observasi(): void
    {
        $this->actingAs($this->guruA)
            ->get(route('guru.riwayat-observasi', $this->anak2->id))
            ->assertOk()
            ->assertSee('Belum ada observasi')
            ->assertSee($this->anak2->nama_panggilan);
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

        $this->actingAs($this->guruA)
            ->get(route('guru.riwayat-observasi', $this->anakKelasLain->id))
            ->assertForbidden()
            ->assertDontSee('Catatan anak kelas lain');
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
