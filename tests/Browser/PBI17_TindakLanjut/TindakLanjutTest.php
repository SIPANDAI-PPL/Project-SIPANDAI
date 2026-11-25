<?php

namespace Tests\Browser\PBI17_TindakLanjut;

use App\Enums\KategoriRespons;
use App\Models\AlternatifStrategi;
use App\Models\Anak;
use App\Models\Kelas;
use App\Models\StrategiPembelajaran;
use App\Models\TindakLanjut;
use App\Models\User;
use Laravel\Dusk\Browser;
use Tests\Browser\Concerns\DataUnik;
use Tests\DuskTestCase;

/**
 * PBI-17 Pencatatan Tindak Lanjut (FR-14, NFR-08).
 *
 * Guru login lewat antarmuka, lalu mencatat atau mengubah tindak lanjut dari strategi yang sudah dipilih.
 *
 * Tanpa DatabaseMigrations/DatabaseTruncation dan tanpa seeder: data awal (guru, kelas, anak, strategi,
 * alternatif berstatus dipilih) dibuat lewat factory dengan kode unik, jadi setiap run menambah data baru
 * di database sipandai_dusk dan data run sebelumnya tetap tersimpan.
 */
class TindakLanjutTest extends DuskTestCase
{
    use DataUnik;

    private const SANDI_MIA = 'GuruMia#2026';

    private User $mia;

    private Anak $anak;

    private AlternatifStrategi $alternatif;

    protected function setUp(): void
    {
        parent::setUp();

        $kode = $this->kodeUnik();

        // Pre Condition: guru dengan satu kelas, satu anak, dan satu strategi berstatus dipilih tanpa tindak lanjut.
        $this->mia = User::factory()->create([
            'nama' => "Mia Stacia {$kode}",
            'nama_pengguna' => "mia{$kode}",
            'email' => "mia.{$kode}@tkceria.sch.id",
            'kata_sandi' => self::SANDI_MIA,
        ]);
        $kelas = Kelas::factory()->create([
            'guru_id' => $this->mia->id,
            'kelompok' => 'TK A',
            'nama_kelas' => "Melati {$kode}",
        ]);

        // Data dummy, bukan data anak asli.
        $this->anak = Anak::factory()->create([
            'kelas_id' => $kelas->id,
            'nama' => "Raditya Kemal {$kode}",
            'nama_panggilan' => 'Radit',
            'aktif' => true,
        ]);
        $strategi = StrategiPembelajaran::factory()->create([
            'nama' => "Media Visual {$kode}",
            'deskripsi' => 'Memakai gambar dan kartu cerita.',
            'aktif' => true,
        ]);
        // Alternatif buatan guru (tanpa aturan), jadi kategori respons tidak terisi otomatis.
        $this->alternatif = AlternatifStrategi::factory()->create([
            'anak_id' => $this->anak->id,
            'strategi_id' => $strategi->id,
            'aturan_id' => null,
            'status' => 'dipilih',
        ]);
    }

    // TC-17-01 | Positif | Simpan dengan tanggal hari ini -> status Berjalan
    public function test_tc_17_01_simpan_dengan_tanggal_hari_ini_status_berjalan(): void
    {
        $this->browse(function (Browser $browser) {
            $this->login($browser);
            $this->bukaFormulir($browser);

            // Tanggal mulai terisi hari ini secara bawaan.
            $browser->assertInputValue('@isi-tanggal', now()->toDateString())
                ->assertSeeIn('[data-status-otomatis]', 'Berjalan');
            $this->isiFormulir($browser, KategoriRespons::KurangTertarik, 'Cepat beralih saat hanya mendengarkan cerita.');

            $browser->click('@tombol-simpan')
                ->waitForLocation('/guru/tindak-lanjut/riwayat')
                ->waitFor('@pesan-status')
                ->assertSeeIn('@pesan-status', "Tindak lanjut untuk {$this->anak->nama} berhasil dicatat.");

            $tindakLanjut = $this->tindakLanjut();
            $browser->within('@baris-tindak-lanjut-'.$tindakLanjut->id, function (Browser $baris) {
                $baris->assertSee($this->anak->nama)
                    ->assertSee('Berjalan')
                    ->assertSee(now()->format('d/m/Y'));
            });
        });

        $tindakLanjut = $this->tindakLanjut();
        $this->assertSame(now()->toDateString(), $tindakLanjut->tanggal_mulai->toDateString());
        $this->assertSame('berjalan', $tindakLanjut->status);
    }

    // TC-17-02 | Positif | Simpan dengan tanggal masa depan -> status Direncanakan
    public function test_tc_17_02_simpan_dengan_tanggal_masa_depan_status_direncanakan(): void
    {
        $tanggal = now()->addDays(3);

        $this->browse(function (Browser $browser) use ($tanggal) {
            $this->login($browser);
            $this->bukaFormulir($browser);
            $this->isiFormulir($browser, KategoriRespons::KurangTertarik, 'Dimulai setelah kartu gambar siap.');
            $this->isiTanggal($browser, $tanggal->toDateString());

            $browser->assertSeeIn('[data-status-otomatis]', 'Direncanakan')
                ->click('@tombol-simpan')
                ->waitForLocation('/guru/tindak-lanjut/riwayat')
                ->waitFor('@pesan-status')
                ->assertSeeIn('@pesan-status', "Tindak lanjut untuk {$this->anak->nama} berhasil dicatat.");

            $browser->within('@baris-tindak-lanjut-'.$this->tindakLanjut()->id, function (Browser $baris) use ($tanggal) {
                $baris->assertSee('Direncanakan')
                    ->assertSee($tanggal->format('d/m/Y'));
            });
        });

        $tindakLanjut = $this->tindakLanjut();
        $this->assertSame($tanggal->toDateString(), $tindakLanjut->tanggal_mulai->toDateString());
        $this->assertSame('direncanakan', $tindakLanjut->status);
    }

    // TC-17-03 | Positif | Simpan tanpa catatan pendukung -> tetap tersimpan
    public function test_tc_17_03_simpan_tanpa_catatan_pendukung_tetap_tersimpan(): void
    {
        $this->browse(function (Browser $browser) {
            $this->login($browser);
            $this->bukaFormulir($browser);
            $this->isiFormulir($browser, KategoriRespons::BelumTerlibat, null);

            $browser->assertInputValue('@isi-catatan', '')
                ->click('@tombol-simpan')
                ->waitForLocation('/guru/tindak-lanjut/riwayat')
                ->waitFor('@pesan-status')
                ->assertSeeIn('@pesan-status', "Tindak lanjut untuk {$this->anak->nama} berhasil dicatat.");

            $browser->within('@baris-tindak-lanjut-'.$this->tindakLanjut()->id, function (Browser $baris) {
                $baris->assertSee($this->anak->nama)
                    ->assertSee('Belum terlibat');
            });
        });

        $tindakLanjut = $this->tindakLanjut();
        $this->assertNull($tindakLanjut->alasan_dugaan_penyebab);
        $this->assertSame(KategoriRespons::BelumTerlibat, $tindakLanjut->kategori_respons_awal);
    }

    // TC-17-04 | Positif | Ubah tindak lanjut -> riwayat menampilkan tulisan "Diubah" beserta waktunya
    public function test_tc_17_04_ubah_tindak_lanjut_menampilkan_tulisan_diubah_dan_waktunya(): void
    {
        $catatanBaru = 'Lebih fokus saat cerita dibantu kartu gambar.';

        $this->browse(function (Browser $browser) use ($catatanBaru) {
            $this->login($browser);
            $this->bukaFormulir($browser);
            $this->isiFormulir($browser, KategoriRespons::KurangTertarik, 'Catatan awal.');
            $browser->click('@tombol-simpan')
                ->waitForLocation('/guru/tindak-lanjut/riwayat');

            $tindakLanjut = $this->tindakLanjut();

            // Sebelum diubah, belum ada tulisan "Diubah".
            $browser->within('@baris-tindak-lanjut-'.$tindakLanjut->id, function (Browser $baris) {
                $baris->assertDontSee('Diubah');
            })
                ->click('@ubah-'.$tindakLanjut->id)
                ->waitForLocation("/guru/tindak-lanjut/{$tindakLanjut->id}/ubah")
                ->waitForText('Ubah Tindak Lanjut')
                ->assertInputValue('@isi-catatan', 'Catatan awal.')
                ->clear('@isi-catatan')
                ->type('@isi-catatan', $catatanBaru)
                ->click('@tombol-simpan')
                ->waitForLocation('/guru/tindak-lanjut/riwayat')
                ->waitFor('@pesan-status')
                ->assertSeeIn('@pesan-status', "Tindak lanjut untuk {$this->anak->nama} berhasil diubah.")
                ->within('@baris-tindak-lanjut-'.$tindakLanjut->id, function (Browser $baris) use ($catatanBaru) {
                    $baris->assertSee($catatanBaru)
                        ->assertSee('Diubah '.now()->format('d/m/Y'));
                });
        });

        $tindakLanjut = $this->tindakLanjut();
        $this->assertSame($catatanBaru, $tindakLanjut->alasan_dugaan_penyebab);
        $this->assertDatabaseHas('riwayat_perubahan', [
            'tindak_lanjut_id' => $tindakLanjut->id,
            'dilakukan_oleh' => $this->mia->id,
            'aksi' => 'ubah',
        ]);
    }

    // TC-17-05 | Negatif | Anak tidak dipilih -> tindak lanjut tidak tersimpan
    public function test_tc_17_05_anak_tidak_dipilih_tindak_lanjut_tidak_tersimpan(): void
    {
        $this->browse(function (Browser $browser) {
            $this->login($browser);
            $this->bukaFormulir($browser);

            // Anak dibiarkan "Pilih anak", jadi pilihan strategi juga belum aktif.
            $browser->assertSelected('@pilih-anak', '')
                ->assertDisabled('@pilih-alternatif')
                ->click('label:has(input[name="kategori_respons"][value="'.KategoriRespons::KurangTertarik->value.'"])')
                ->type('@isi-catatan', 'Dicoba tanpa memilih anak.')
                ->click('@tombol-simpan')
                ->waitFor('@pesan-salah')
                ->assertPathIs('/guru/tindak-lanjut')
                ->assertSeeIn('@pesan-salah', 'Tindak lanjut belum tersimpan.')
                ->assertSeeIn('@pesan-salah', 'Anak wajib dipilih.')
                ->assertMissing('@pesan-status');
        });

        $this->assertNull($this->alternatif->tindakLanjut()->first());
    }

    // ---------------------------------------------------------------- Bantuan

    /**
     * Login seperti guru sungguhan: buka beranda, klik tombol "Masuk", lalu isi formulir login.
     * Sesi browser dipakai ulang antar-test, jadi keluar dulu sebelum login.
     */
    private function login(Browser $browser): Browser
    {
        return $browser->logout()
            ->visit('/')
            ->waitFor('@tombol-atas')
            ->click('@tombol-atas')
            ->waitForLocation('/login')
            ->type('login', $this->mia->nama_pengguna)
            ->type('kata_sandi', self::SANDI_MIA)
            ->press('Masuk')
            ->waitUntilMissing('input[name="kata_sandi"]');
    }

    /**
     * Buka formulir lewat menu Tindak Lanjut di sidebar.
     */
    private function bukaFormulir(Browser $browser): Browser
    {
        return $browser->clickLink('Tindak Lanjut')
            ->waitForLocation('/guru/tindak-lanjut')
            ->waitForText('Pencatatan Tindak Lanjut');
    }

    /**
     * Pilih anak (strategi satu-satunya terpilih otomatis), pilih kategori respons, dan isi catatan bila ada.
     */
    private function isiFormulir(Browser $browser, KategoriRespons $respons, ?string $catatan): Browser
    {
        $browser->select('@pilih-anak', (string) $this->anak->id)
            ->waitFor('@pilih-alternatif option[value="'.$this->alternatif->id.'"]')
            ->assertSelected('@pilih-alternatif', (string) $this->alternatif->id)
            ->click('label:has(input[name="kategori_respons"][value="'.$respons->value.'"])');

        if ($catatan !== null) {
            $browser->type('@isi-catatan', $catatan);
        }

        return $browser;
    }

    /**
     * Isi input tanggal lewat script: format ketikan input date bergantung pada bahasa Chrome.
     */
    private function isiTanggal(Browser $browser, string $tanggal): Browser
    {
        $browser->script(
            "const el = document.getElementById('tanggal');"
            ."el.value = '{$tanggal}';"
            ."el.dispatchEvent(new Event('input', { bubbles: true }));"
        );

        return $browser->assertInputValue('@isi-tanggal', $tanggal);
    }

    private function tindakLanjut(): TindakLanjut
    {
        return TindakLanjut::query()->where('alternatif_id', $this->alternatif->id)->firstOrFail();
    }
}
