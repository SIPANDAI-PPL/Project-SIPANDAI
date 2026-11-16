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
use Illuminate\Testing\TestResponse;
use Tests\TestCase;

// Fitur: PBI-12 Ringkasan Pola Respons Anak
class RingkasanPolaResponsTest extends TestCase
{
    use RefreshDatabase;

    private User $guru;

    private Kelas $kelas;

    private Anak $dedek;

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

        // Rabu, 18 Nov 2026. "Pekan ini" = Senin 16 s.d. Minggu 22 Nov 2026.
        $this->travelTo('2026-11-18 09:00:00');

        $this->guru = User::factory()->create(['nama' => 'Mia Stacia', 'nama_pengguna' => 'miastacia']);
        $guruLain = User::factory()->create(['nama_pengguna' => 'ayurizki']);

        $this->kelas = Kelas::factory()->create([
            'guru_id' => $this->guru->id,
            'nama_kelas' => 'Melati',
            'kelompok' => 'TK A',
        ]);

        $kelasLain = Kelas::factory()->create([
            'guru_id' => $guruLain->id,
            'nama_kelas' => 'Cendrawasih',
            'kelompok' => 'TK B',
        ]);

        foreach (['Kognitif', 'Bahasa', 'Seni'] as $nama) {
            $this->aspek[$nama] = AspekPerkembangan::factory()->create(['nama' => $nama]);
        }

        foreach (['Menyusun balok', 'Bercerita', 'Mewarnai gambar'] as $nama) {
            $this->aktivitas[$nama] = Aktivitas::factory()->create(['nama' => $nama]);
        }

        foreach (['Media visual', 'Kelompok kecil'] as $nama) {
            $this->strategi[$nama] = StrategiPembelajaran::factory()->create(['nama' => $nama]);
        }

        $this->dedek = Anak::factory()->create([
            'kelas_id' => $this->kelas->id,
            'nama' => 'Dedek Piplup',
            'nama_panggilan' => 'Dedek',
        ]);

        $this->anakKelasLain = Anak::factory()->create([
            'kelas_id' => $kelasLain->id,
            'nama_panggilan' => 'Cagak',
        ]);

        $this->buatObservasiDedek();
    }

    public function test_tc_12_01_ringkasan_pola_respons_menampilkan_grafik_dan_frekuensi(): void
    {
        $respons = $this->bukaRingkasan()
            ->assertOk()
            ->assertSee('Ringkasan Pola Respons')
            ->assertSee('Dedek')
            ->assertSee('TK A - Melati')
            ->assertSee('6 observasi')
            ->assertSee('id="grafik-kategori-respons"', false)
            ->assertSee('id="grafik-pola-kelompok"', false)
            ->assertSee('Menyusun balok')
            ->assertSee('Bercerita');

        $grafik = $this->dataGrafik($respons);

        // Urutan selalu mengikuti enum KategoriRespons.
        $this->assertSame(
            collect(KategoriRespons::cases())->map->label()->all(),
            $grafik['kategori']['labels'],
        );
        $this->assertSame([1, 2, 2, 1], $grafik['kategori']['data']);

        // Frekuensi per aktivitas: Menyusun balok (3) dan Bercerita (3).
        $aktivitas = $this->kelompok($grafik, 'aktivitas');
        $this->assertSame([0, 1, 2, 0], $aktivitas['Menyusun balok']);
        $this->assertSame([1, 1, 0, 1], $aktivitas['Bercerita']);
        $this->assertArrayNotHasKey('Mewarnai gambar', $aktivitas);

        $aspek = $this->kelompok($grafik, 'aspek');
        $this->assertSame([0, 1, 2, 0], $aspek['Kognitif']);
        $this->assertSame([1, 1, 0, 1], $aspek['Bahasa']);

        $strategi = $this->kelompok($grafik, 'strategi');
        $this->assertSame([0, 1, 2, 1], $strategi['Media visual']);
        $this->assertSame([1, 1, 0, 0], $strategi['Kelompok kecil']);

        // Rekam jejak: observasi terbaru tampil lebih dulu.
        $respons->assertSeeInOrder(['17 Nov 2026', '16 Nov 2026', '10 Nov 2026']);

        // Kategori hanya dari enum, bukan skala penilaian BSB/BSH/MB/BB.
        // Dicek pada teks halaman saja agar nama file aset Vite (hash acak) tidak ikut terbaca.
        $teksHalaman = strip_tags($respons->getContent());

        foreach (['BSB', 'BSH', 'Berkembang Sangat Baik', 'Mulai Berkembang'] as $istilahPenilaian) {
            $this->assertStringNotContainsString($istilahPenilaian, $teksHalaman);
        }
    }

    public function test_tc_12_01_tren_respons_per_aspek_tampil(): void
    {
        $this->bukaRingkasan()
            ->assertOk()
            ->assertSee('Tren respons per aspek')
            ->assertSeeInOrder(['Kognitif', 'Paling sering: Kurang tertarik'])
            ->assertSee('Bervariasi');
    }

    public function test_tc_12_01_guru_tidak_dapat_membuka_anak_di_luar_kelas_yang_diampu(): void
    {
        $this->actingAs($this->guru)
            ->get(route('guru.pola-respons.anak', $this->anakKelasLain))
            ->assertForbidden()
            ->assertSee('Anda tidak memiliki akses ke data anak ini')
            ->assertDontSee('Cagak');
    }

    public function test_tc_12_01_hanya_peran_guru_yang_dapat_membuka_ringkasan(): void
    {
        $this->get(route('guru.pola-respons.anak', $this->dedek))
            ->assertRedirect(route('login'));

        $admin = User::factory()->admin()->create();

        $this->actingAs($admin)
            ->get(route('guru.pola-respons.anak', $this->dedek))
            ->assertForbidden();
    }

    public function test_tc_12_01_observasi_anak_lain_tidak_ikut_dihitung(): void
    {
        $teman = Anak::factory()->create(['kelas_id' => $this->kelas->id]);

        Observasi::factory()->count(3)->create([
            'anak_id' => $teman->id,
            'aspek_id' => $this->aspek['Kognitif']->id,
            'aktivitas_id' => $this->aktivitas['Menyusun balok']->id,
            'strategi_id' => $this->strategi['Media visual']->id,
            'kategori_respons' => KategoriRespons::TerlibatAktif,
            'tanggal' => '2026-11-17',
        ]);

        $grafik = $this->dataGrafik($this->bukaRingkasan()->assertOk());

        $this->assertSame([1, 2, 2, 1], $grafik['kategori']['data']);
    }

    public function test_tc_12_02_filter_periode_pekan_ini(): void
    {
        $respons = $this->bukaRingkasan(['periode' => 'pekan-ini'])
            ->assertOk()
            ->assertSee('3 dari 6 observasi')
            ->assertSee('16 Nov 2026')
            ->assertDontSee('10 Nov 2026')
            ->assertDontSee('20 Okt 2026');

        $grafik = $this->dataGrafik($respons);

        $this->assertSame([1, 1, 1, 0], $grafik['kategori']['data']);
        $this->assertSame([0, 1, 1, 0], $this->kelompok($grafik, 'aspek')['Kognitif']);
        $this->assertSame([1, 0, 0, 0], $this->kelompok($grafik, 'aspek')['Bahasa']);
    }

    public function test_tc_12_02_filter_periode_bulan_ini_dan_30_hari(): void
    {
        // Bulan ini = 1-30 Nov 2026.
        $this->assertSame([1, 2, 2, 0], $this->dataGrafik($this->bukaRingkasan(['periode' => 'bulan-ini']))['kategori']['data']);

        // 30 hari terakhir = 20 Okt - 18 Nov 2026, batas awal ikut dihitung.
        $this->assertSame([1, 2, 2, 1], $this->dataGrafik($this->bukaRingkasan(['periode' => '30']))['kategori']['data']);
    }

    public function test_tc_12_02_filter_aspek_aktivitas_strategi_dan_respons(): void
    {
        $this->assertSame(
            [0, 1, 2, 0],
            $this->dataGrafik($this->bukaRingkasan(['aspek' => $this->aspek['Kognitif']->id]))['kategori']['data'],
        );

        $grafikAktivitas = $this->dataGrafik($this->bukaRingkasan(['aktivitas' => $this->aktivitas['Bercerita']->id]));
        $this->assertSame([1, 1, 0, 1], $grafikAktivitas['kategori']['data']);
        $this->assertSame(['Bercerita'], $grafikAktivitas['kelompok']['aktivitas']['labels']);

        $this->assertSame(
            [1, 1, 0, 0],
            $this->dataGrafik($this->bukaRingkasan(['strategi' => $this->strategi['Kelompok kecil']->id]))['kategori']['data'],
        );

        $this->assertSame(
            [0, 0, 2, 0],
            $this->dataGrafik($this->bukaRingkasan(['respons' => KategoriRespons::KurangTertarik->value]))['kategori']['data'],
        );
    }

    public function test_tc_12_02_kombinasi_filter(): void
    {
        $respons = $this->bukaRingkasan([
            'periode' => 'pekan-ini',
            'aktivitas' => $this->aktivitas['Menyusun balok']->id,
            'strategi' => $this->strategi['Kelompok kecil']->id,
        ])->assertOk()->assertSee('1 dari 6 observasi');

        $this->assertSame([0, 1, 0, 0], $this->dataGrafik($respons)['kategori']['data']);
    }

    public function test_tc_12_02_filter_tanpa_hasil_menampilkan_pesan(): void
    {
        $this->bukaRingkasan(['aspek' => $this->aspek['Seni']->id])
            ->assertOk()
            ->assertSee('0 dari 6 observasi')
            ->assertSee('Tidak ada observasi yang sesuai filter')
            ->assertDontSee('id="data-grafik-pola"', false);
    }

    public function test_tc_12_02_filter_tidak_valid_ditolak(): void
    {
        $this->bukaRingkasan(['periode' => 'tahun-lalu'])->assertSessionHasErrors('periode');
        $this->bukaRingkasan(['respons' => 'BSB'])->assertSessionHasErrors('respons');
        $this->bukaRingkasan(['aspek' => 9999])->assertSessionHasErrors('aspek');
    }

    public function test_tc_12_02_pilihan_filter_tersedia(): void
    {
        $respons = $this->bukaRingkasan()
            ->assertOk()
            ->assertSee('Pekan ini')
            ->assertSee('Bulan ini')
            ->assertSee('30 hari terakhir')
            ->assertSee('Semua periode')
            ->assertSee('Semua aspek')
            ->assertSee('Semua aktivitas')
            ->assertSee('Semua strategi')
            ->assertSee('Semua respons');

        foreach (KategoriRespons::cases() as $kategori) {
            $respons->assertSee('value="'.$kategori->value.'"', false);
        }
    }

    public function test_tc_12_03_penanda_bukan_penilaian_tetap_atau_diagnosis(): void
    {
        $this->bukaRingkasan()
            ->assertOk()
            ->assertSee('rangkuman data observasi')
            ->assertSee('bukan penilaian tetap')
            ->assertSee('bukan label permanen')
            ->assertSee('bukan diagnosis');
    }

    public function test_tc_12_03_penanda_tetap_tampil_saat_belum_ada_observasi(): void
    {
        $anakBaru = Anak::factory()->create(['kelas_id' => $this->kelas->id]);

        $this->bukaRingkasan([], $anakBaru)
            ->assertOk()
            ->assertSee('bukan penilaian tetap')
            ->assertSee('bukan diagnosis');
    }

    public function test_tc_12_04_anak_baru_tanpa_observasi_menampilkan_status_kosong(): void
    {
        $anakBaru = Anak::factory()->create([
            'kelas_id' => $this->kelas->id,
            'nama_panggilan' => 'Rara',
        ]);

        $this->bukaRingkasan([], $anakBaru)
            ->assertOk()
            ->assertSee('Rara')
            ->assertSee('0 observasi')
            ->assertSee('Belum ada data observasi')
            ->assertSee('Ringkasan pola respons Rara akan muncul setelah observasi pertama dicatat.')
            ->assertSee('Catat observasi')
            ->assertDontSee('id="data-grafik-pola"', false)
            ->assertDontSee('Tidak ada observasi yang sesuai filter');
    }

    public function test_daftar_ringkasan_hanya_menampilkan_anak_di_kelas_yang_diampu(): void
    {
        $this->actingAs($this->guru)
            ->get(route('guru.pola-respons.index'))
            ->assertOk()
            ->assertSee('Dedek')
            ->assertSee('6 observasi')
            ->assertSee(route('guru.pola-respons.anak', $this->dedek), false)
            ->assertDontSee('Cagak');
    }

    private function bukaRingkasan(array $filter = [], ?Anak $anak = null): TestResponse
    {
        return $this->actingAs($this->guru)
            ->get(route('guru.pola-respons.anak', ['anak' => ($anak ?? $this->dedek)->id, ...$filter]));
    }

    /**
     * Konfigurasi grafik Chart.js yang disisipkan di halaman sebagai JSON.
     *
     * @return array<string, mixed>
     */
    private function dataGrafik(TestResponse $respons): array
    {
        $cocok = preg_match(
            '#<script type="application/json" id="data-grafik-pola">(.*?)</script>#s',
            $respons->getContent(),
            $hasil,
        );

        $this->assertSame(1, $cocok, 'Data grafik Chart.js tidak ditemukan di halaman.');

        return json_decode($hasil[1], true, flags: JSON_THROW_ON_ERROR);
    }

    /**
     * Frekuensi per kategori untuk setiap baris kelompok (aspek/aktivitas/strategi).
     *
     * @return array<string, list<int>>
     */
    private function kelompok(array $grafik, string $jenis): array
    {
        $data = $grafik['kelompok'][$jenis];
        $hasil = [];

        foreach ($data['labels'] as $indeks => $label) {
            $hasil[$label] = array_map(fn (array $dataset) => $dataset['data'][$indeks], $data['datasets']);
        }

        return $hasil;
    }

    /**
     * Data uji PBI-12 untuk Dedek: aktivitas menyusun balok dan bercerita.
     * Total 6 = 1 terlibat aktif, 2 pendampingan, 2 kurang tertarik, 1 belum terlibat.
     */
    private function buatObservasiDedek(): void
    {
        $baris = [
            ['2026-11-16', 'Kognitif', 'Menyusun balok', 'Media visual', KategoriRespons::KurangTertarik],
            ['2026-11-17', 'Bahasa', 'Bercerita', 'Kelompok kecil', KategoriRespons::TerlibatAktif],
            ['2026-11-17', 'Kognitif', 'Menyusun balok', 'Kelompok kecil', KategoriRespons::TerlibatDenganPendampingan],
            ['2026-11-10', 'Bahasa', 'Bercerita', 'Media visual', KategoriRespons::TerlibatDenganPendampingan],
            ['2026-11-03', 'Kognitif', 'Menyusun balok', 'Media visual', KategoriRespons::KurangTertarik],
            ['2026-10-20', 'Bahasa', 'Bercerita', 'Media visual', KategoriRespons::BelumTerlibat],
        ];

        foreach ($baris as [$tanggal, $aspek, $aktivitas, $strategi, $respons]) {
            Observasi::factory()->create([
                'anak_id' => $this->dedek->id,
                'aspek_id' => $this->aspek[$aspek]->id,
                'aktivitas_id' => $this->aktivitas[$aktivitas]->id,
                'strategi_id' => $this->strategi[$strategi]->id,
                'tanggal' => $tanggal,
                'kategori_respons' => $respons,
            ]);
        }
    }
}
