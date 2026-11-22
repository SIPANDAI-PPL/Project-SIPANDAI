<?php

namespace Tests\Browser\PBI05_KelolaDataAnak;

use App\Enums\KategoriRespons;
use App\Models\Aktivitas;
use App\Models\Anak;
use App\Models\AspekPerkembangan;
use App\Models\Kelas;
use App\Models\Observasi;
use App\Models\StrategiPembelajaran;
use App\Models\User;
use Laravel\Dusk\Browser;
use Tests\Browser\Concerns\DataUnik;
use Tests\DuskTestCase;

/**
 * PBI-05 Kelola Data Dasar Anak (FR-03, NFR-04).
 *
 * Sesuai kolom Otomasi pada sheet test case, Dusk hanya dipakai untuk TC-05-05, TC-05-08,
 * dan TC-05-19. Test case lain diuji di tests/Feature/Guru/KelolaDataAnakTest.php.
 *
 * Tanpa DatabaseMigrations/RefreshDatabase dan tanpa seeder: data uji (G1, G2, G3, C1–C6)
 * dibuat lewat factory dengan kode unik, jadi setiap run menambah data baru di database
 * sipandai_dusk dan data run sebelumnya tetap tersimpan.
 */
class KelolaDataAnakTest extends DuskTestCase
{
    use DataUnik;

    private const SANDI_GURU = 'GuruSipandai#2026';

    /** G1 Mia Stacia: mengampu Kelompok B1 dan Kelompok B2. */
    private User $g1;

    /** G2 Ayu Rizki: mengampu Kelompok A1. */
    private User $g2;

    /** G3 Andi Pratama: tanpa kelas. */
    private User $g3;

    private Kelas $kelasB1;

    private Kelas $kelasB2;

    private Kelas $kelasA1;

    /** @var array<string, Anak> C1–C6 */
    private array $c = [];

    protected function setUp(): void
    {
        parent::setUp();

        $kode = $this->kodeUnik();

        // Pre Condition: data akun.
        $this->g1 = $this->buatGuru("Mia Stacia {$kode}", "miastacia{$kode}");
        $this->g2 = $this->buatGuru("Ayu Rizki {$kode}", "ayurizki{$kode}");
        $this->g3 = $this->buatGuru("Andi Pratama {$kode}", "andipratama{$kode}");

        $this->kelasB1 = $this->buatKelas($this->g1, "Kelompok B1 {$kode}", 'TK B');
        $this->kelasB2 = $this->buatKelas($this->g1, "Kelompok B2 {$kode}", 'TK B');
        $this->kelasA1 = $this->buatKelas($this->g2, "Kelompok A1 {$kode}", 'TK A');

        // Pre Condition: data anak (dummy, bukan data anak asli).
        $this->c['C1'] = $this->buatAnak($this->kelasB1, "Alya Putri {$kode}", 'Alya', 40);
        $this->c['C2'] = $this->buatAnak($this->kelasB1, "Bima Pratama {$kode}", 'Bima', 45);
        $this->c['C3'] = $this->buatAnak($this->kelasB1, "Dedek Nandita {$kode}", 'Dedek', 50);
        $this->c['C4'] = $this->buatAnak($this->kelasB1, "Faris Ramadhan {$kode}", 'Faris', 52, aktif: false);
        $this->c['C5'] = $this->buatAnak($this->kelasB2, "Gita Anindya {$kode}", 'Gita', 48);
        $this->c['C6'] = $this->buatAnak($this->kelasA1, "Hanif Pratama {$kode}", 'Hanif', 36);

        // C1: 2 observasi, terakhir hari ini, Terlibat aktif.
        $this->buatObservasi($this->c['C1'], 1, now()->subDays(6)->format('Y-m-d'), KategoriRespons::KurangTertarik);
        $this->buatObservasi($this->c['C1'], 1, now()->format('Y-m-d'), KategoriRespons::TerlibatAktif);
        // C3: 14 observasi, terakhir hari ini, Kurang tertarik.
        $this->buatObservasi($this->c['C3'], 13, now()->subDays(15)->format('Y-m-d'), KategoriRespons::TerlibatAktif);
        $this->buatObservasi($this->c['C3'], 1, now()->format('Y-m-d'), KategoriRespons::KurangTertarik);
        // C4: 3 observasi (nonaktif).
        $this->buatObservasi($this->c['C4'], 3, now()->subDays(27)->format('Y-m-d'), KategoriRespons::TerlibatAktif);
    }

    // ---------------------------------------------------------------- Normal

    // TC-05-05 | Normal | Menambah data anak dengan data valid
    public function test_tc_05_05_menambah_data_anak_dengan_data_valid(): void
    {
        $kode = $this->kodeUnik();
        $lahir = now()->subMonths(42)->format('Y-m-d');

        $this->browse(function (Browser $browser) use ($kode, $lahir) {
            $this->bukaDataAnak($browser, $this->g1)
                ->assertSeeIn('@pilihan-kelas', "Kelompok B1 {$kode} · 2026/2027 (4 anak)")
                ->assertSeeIn('@tab-aktif', 'Aktif (3)')
                // Langkah: klik Tambah anak, isi formulir, lalu klik Simpan.
                ->click('@tombol-tambah-anak')
                ->waitFor('#modal-tambah:not(.hidden)')
                ->type('#tambah-nama', "Rani Kartika {$kode}")
                ->type('#tambah-panggilan', 'Rani')
                ->script("document.getElementById('tambah-lahir').value = '{$lahir}';");

            $browser->select('#tambah-kelas', (string) $this->kelasB1->id)
                ->click('#modal-tambah button[type=submit]')
                // Expected Result - Tampilan: pesan berhasil, jumlah pada pilihan Kelas dan tab bertambah.
                ->waitFor('@pesan-status')
                ->assertSeeIn('@pesan-status', 'Data anak berhasil ditambahkan.')
                ->assertSeeIn('@pilihan-kelas', "Kelompok B1 {$kode} · 2026/2027 (5 anak)")
                ->assertSeeIn('@tab-aktif', 'Aktif (4)');

            // Expected Result - Tampilan: Rani tampil di tab Aktif dengan "Belum diobservasi".
            $rani = Anak::query()->where('nama', "Rani Kartika {$kode}")->firstOrFail();
            $browser->assertAttribute('@tab-aktif', 'aria-current', 'page')
                ->within('@daftar-anak a[href$="/guru/anak/'.$rani->id.'"]', function (Browser $baris) {
                    $baris->assertSee('Rani')
                        ->assertSee('Belum diobservasi');
                });
        });

        // Expected Result - Database: tersimpan dengan aktif = 1 dan kelas_id Kelompok B1.
        $this->assertDatabaseHas('anak', [
            'nama' => "Rani Kartika {$kode}",
            'nama_panggilan' => 'Rani',
            'tanggal_lahir' => $lahir,
            'kelas_id' => $this->kelasB1->id,
            'aktif' => 1,
        ]);
    }

    // TC-05-08 | Normal | Menonaktifkan anak
    public function test_tc_05_08_menonaktifkan_anak(): void
    {
        $c2 = $this->c['C2'];

        $this->browse(function (Browser $browser) use ($c2) {
            // Langkah: buka Modal Ubah C2, klik Nonaktifkan, lalu klik Nonaktifkan pada dialog konfirmasi.
            $this->bukaDataAnak($browser, $this->g1)
                ->click('@daftar-anak a[href$="/guru/anak/'.$c2->id.'"]')
                ->waitForLocation('/guru/anak/'.$c2->id)
                ->click('[data-modal-buka="modal-ubah"]')
                ->waitFor('#modal-ubah:not(.hidden)')
                ->click('#modal-ubah [data-modal-buka="modal-nonaktifkan"]')
                ->waitFor('#modal-nonaktifkan:not(.hidden)')
                ->assertSeeIn('#modal-nonaktifkan', 'Nonaktifkan anak?')
                ->assertSeeIn('#modal-nonaktifkan', $c2->nama)
                ->click('#modal-nonaktifkan button[type=submit]')
                ->waitFor('@pesan-status')
                ->assertSeeIn('@pesan-status', 'Anak berhasil dinonaktifkan.')
                // Expected Result - Tampilan: C2 pindah ke tab Nonaktif.
                ->clickLink('← Kembali ke Data Anak')
                ->waitFor('@daftar-anak')
                ->assertSeeIn('@tab-aktif', 'Aktif (2)')
                ->assertDontSeeIn('@daftar-anak', 'Bima')
                ->click('@tab-nonaktif')
                ->waitFor('@tab-nonaktif[aria-current="page"]')
                ->assertSeeIn('@tab-nonaktif', 'Nonaktif (2)')
                ->assertSeeIn('@daftar-anak', 'Bima');
        });

        // Expected Result - Database: aktif C2 = 0 dan data C2 tetap ada.
        $this->assertDatabaseHas('anak', [
            'id' => $c2->id,
            'nama' => $c2->nama,
            'kelas_id' => $this->kelasB1->id,
            'aktif' => 0,
        ]);
    }

    // ---------------------------------------------------------------- Pengecualian

    // TC-05-19 | Pengecualian | Guru tanpa kelas membuka halaman Kelola Data Anak
    public function test_tc_05_19_guru_tanpa_kelas_membuka_halaman_kelola_data_anak(): void
    {
        $kode = $this->kodeUnik();

        $this->browse(function (Browser $browser) use ($kode) {
            // Langkah: login sebagai G3, lalu buka menu Data Anak.
            $this->bukaDataAnak($browser, $this->g3, tungguDaftar: false)
                // Expected Result - Tampilan: pesan tanpa kelas, pilihan Kelas kosong, tombol Tambah anak tidak dapat diklik.
                ->assertSeeIn('@pesan-tanpa-kelas', 'Anda belum mengampu kelas, hubungi admin.')
                ->assertMissing('@pilihan-kelas')
                ->assertMissing('@tab-aktif')
                ->assertDisabled('@tombol-tambah-anak');

            // Expected Result: request tambah langsung ke server ditolak.
            $browser->script(
                "window.hasilTambah = null;"
                ."var data = new FormData();"
                ."data.append('_token', document.querySelector('input[name=\"_token\"]').value);"
                ."data.append('nama', 'Rani Kartika {$kode}');"
                ."data.append('tanggal_lahir', '".now()->subMonths(42)->format('Y-m-d')."');"
                ."data.append('kelas_id', '{$this->kelasB1->id}');"
                ."fetch('/guru/anak', {method: 'POST', body: data, headers: {'Accept': 'application/json'}})"
                .".then(function (r) { return r.json().then(function (j) { window.hasilTambah = {status: r.status, body: j}; }); });"
            );

            $browser->waitUntil('window.hasilTambah !== null');
            $hasil = $browser->script('return window.hasilTambah;')[0];

            $this->assertSame(422, $hasil['status']);
            $this->assertSame('Kelas tidak valid.', $hasil['body']['errors']['kelas_id'][0]);
        });

        // Expected Result - Database: G3 tetap tanpa kelas dan anak tidak tersimpan.
        $this->assertDatabaseHas('users', ['id' => $this->g3->id, 'peran' => 'guru', 'aktif' => 1]);
        $this->assertDatabaseMissing('kelas', ['guru_id' => $this->g3->id]);
        $this->assertDatabaseMissing('anak', ['nama' => "Rani Kartika {$kode}"]);
    }

    // ---------------------------------------------------------------- Bantuan

    private function buatGuru(string $nama, string $namaPengguna): User
    {
        return User::factory()->create([
            'nama' => $nama,
            'nama_pengguna' => $namaPengguna,
            'email' => "{$namaPengguna}@tkceria.sch.id",
            'kata_sandi' => self::SANDI_GURU,
        ]);
    }

    private function buatKelas(User $guru, string $nama, string $kelompok): Kelas
    {
        return Kelas::factory()->create([
            'guru_id' => $guru->id,
            'nama_kelas' => $nama,
            'kelompok' => $kelompok,
            'tahun_ajaran' => '2026/2027',
        ]);
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

    private function buatObservasi(Anak $anak, int $jumlah, string $tanggal, KategoriRespons $respons): void
    {
        Observasi::factory()->count($jumlah)->create([
            'anak_id' => $anak->id,
            'guru_id' => $anak->kelas->guru_id,
            'aktivitas_id' => Aktivitas::factory(),
            'strategi_id' => StrategiPembelajaran::factory(),
            'aspek_id' => AspekPerkembangan::factory(),
            'tanggal' => $tanggal,
            'kategori_respons' => $respons,
        ]);
    }

    /**
     * Login seperti pengguna sungguhan (beranda → Masuk → formulir login), lalu buka menu Data Anak.
     * Sesi browser dipakai ulang antar-test, jadi keluar dulu sebelum login.
     */
    private function bukaDataAnak(Browser $browser, User $guru, bool $tungguDaftar = true): Browser
    {
        $browser->logout()
            ->visit('/')
            ->waitFor('@tombol-atas')
            ->click('@tombol-atas')
            ->waitForLocation('/login')
            ->type('login', $guru->nama_pengguna)
            ->type('kata_sandi', self::SANDI_GURU)
            ->press('Masuk')
            ->waitUntilMissing('input[name="kata_sandi"]')
            ->clickLink('Data Anak')
            ->waitForLocation('/guru/anak')
            ->assertSee('Kelola Data Anak');

        return $tungguDaftar ? $browser->waitFor('@daftar-anak') : $browser;
    }
}
