<?php

namespace Tests\Browser\PBI02_AktivitasStrategi;

use App\Models\Aktivitas;
use App\Models\Observasi;
use Laravel\Dusk\Browser;
use Tests\Browser\Concerns\DataUnik;
use Tests\DuskTestCase;
use Tests\Support\DataUjiDataMaster;

// PBI-02 Kelola Aktivitas dan Strategi Pembelajaran
// Menambah, menonaktifkan, menghapus aktivitas, dan hak akses halaman aktivitas
class DataMasterTest extends DuskTestCase
{
    // Tanpa DatabaseMigrations: data hasil test tetap tersimpan di database sipandai_dusk.
    use DataUjiDataMaster;
    use DataUnik;

    /** @var array<string, mixed> */
    private array $data;

    protected function setUp(): void
    {
        parent::setUp();

        // Pre Condition: admin, 5 aktivitas, strategi, dan observasi dibuat lewat factory
        // dengan kode unik, misalnya "Bercerita k7m2q".
        $this->data = $this->buatDataUjiDataMaster($this->kodeUnik());
    }

    /**
     * Daftar aktivitas terus bertambah setiap run, jadi tabel disaring dengan kode unik test ini.
     */
    private function bukaDaftarAktivitasTestIni(Browser $browser): Browser
    {
        return $browser->loginAs($this->data['admin'])
            ->visitRoute('data-master.aktivitas.index', ['q' => $this->kodeUnik()])
            ->waitFor('@baris-'.$this->data['ak1']->id);
    }

    // TC-02-05 | Positif | Admin menambah aktivitas baru lewat modal
    public function test_tc_02_05_menambah_aktivitas_bernyanyi_lewat_modal(): void
    {
        $kode = $this->kodeUnik();
        $jumlahAwal = Aktivitas::count();

        $this->browse(function (Browser $browser) use ($kode) {
            $this->bukaDaftarAktivitasTestIni($browser)
                ->click('@tombol-tambah')
                ->whenAvailable('@modal-tambah', function (Browser $modal) use ($kode) {
                    $modal->type('nama', "Bernyanyi {$kode}")
                        ->type('deskripsi', 'Bernyanyi lagu anak bersama-sama.')
                        ->click('@simpan-tambah');
                })
                ->waitForTextIn('@pesan-status', "Aktivitas Bernyanyi {$kode} berhasil ditambahkan.")
                ->visitRoute('data-master.aktivitas.index', ['q' => "Bernyanyi {$kode}"])
                ->assertSee("Bernyanyi {$kode}");
        });

        $this->assertSame($jumlahAwal + 1, Aktivitas::count());
        $this->assertDatabaseHas('aktivitas', [
            'nama' => "Bernyanyi {$kode}",
            'deskripsi' => 'Bernyanyi lagu anak bersama-sama.',
            'aktif' => true,
        ]);
    }

    // TC-02-15 | Negatif | Nama aktivitas yang sudah terdaftar ditolak
    public function test_tc_02_15_nama_aktivitas_duplikat_ditolak(): void
    {
        $kode = $this->kodeUnik();
        $jumlahAwal = Aktivitas::count();

        $this->browse(function (Browser $browser) use ($kode) {
            $this->bukaDaftarAktivitasTestIni($browser)
                ->click('@tombol-tambah')
                ->whenAvailable('@modal-tambah', function (Browser $modal) use ($kode) {
                    $modal->type('nama', "Bercerita {$kode}")
                        ->click('@simpan-tambah');
                })
                // Server menolak; modal tambah terbuka lagi dengan pesan galat.
                ->waitFor('@error-tambah-nama')
                ->assertSeeIn('@error-tambah-nama', 'Nama aktivitas sudah terdaftar.')
                ->assertMissing('@pesan-status');
        });

        $this->assertSame($jumlahAwal, Aktivitas::count());
        $this->assertSame(1, Aktivitas::where('nama', "Bercerita {$kode}")->count());
    }

    // TC-02-08 | Positif | Menonaktifkan aktivitas yang sudah dipakai, observasi tetap ada
    public function test_tc_02_08_menonaktifkan_bercerita_lewat_dialog(): void
    {
        $kode = $this->kodeUnik();
        $ak1 = $this->data['ak1'];

        $this->browse(function (Browser $browser) use ($ak1, $kode) {
            $this->bukaDaftarAktivitasTestIni($browser)
                ->click('@menu-aksi-'.$ak1->id)
                ->waitFor('@aksi-nonaktifkan-'.$ak1->id)
                ->click('@aksi-nonaktifkan-'.$ak1->id)
                ->whenAvailable('@modal-nonaktifkan', function (Browser $modal) use ($kode) {
                    $modal->assertSee("Nonaktifkan aktivitas Bercerita {$kode}?")
                        ->assertSee('2 observasi')
                        ->click('@konfirmasi-nonaktifkan');
                })
                ->waitForTextIn('@pesan-status', "Aktivitas Bercerita {$kode} dinonaktifkan.")
                ->visitRoute('data-master.aktivitas.index', ['q' => $kode])
                ->assertSeeIn('@baris-'.$ak1->id, 'Nonaktif');
        });

        $this->assertDatabaseHas('aktivitas', ['id' => $ak1->id, 'aktif' => false]);
        $this->assertSame(2, Observasi::where('aktivitas_id', $ak1->id)->count());
    }

    // TC-02-10 | Positif | Menghapus aktivitas yang belum dipakai observasi
    public function test_tc_02_10_menghapus_bermain_peran_lewat_dialog(): void
    {
        $kode = $this->kodeUnik();
        $ak5 = $this->data['ak5'];

        $this->browse(function (Browser $browser) use ($ak5, $kode) {
            $this->bukaDaftarAktivitasTestIni($browser)
                ->click('@menu-aksi-'.$ak5->id)
                ->waitFor('@aksi-hapus-'.$ak5->id)
                ->click('@aksi-hapus-'.$ak5->id)
                ->whenAvailable('@modal-hapus', function (Browser $modal) use ($kode) {
                    $modal->assertSee("Hapus aktivitas Bermain peran {$kode}?")
                        ->click('@konfirmasi-hapus');
                })
                ->waitForTextIn('@pesan-status', "Aktivitas Bermain peran {$kode} berhasil dihapus.")
                ->visitRoute('data-master.aktivitas.index', ['q' => $kode])
                ->assertMissing('@baris-'.$ak5->id);
        });

        $this->assertDatabaseMissing('aktivitas', ['id' => $ak5->id]);
    }

    // TC-02-19 | Error | Tamu yang belum login diarahkan ke halaman login
    public function test_tc_02_19_tamu_diarahkan_ke_login(): void
    {
        $jumlahAwal = Aktivitas::count();

        $this->browse(function (Browser $browser) {
            $browser->logout()
                ->visitRoute('data-master.aktivitas.index')
                ->waitForLocation('/login')
                ->assertPathIs('/login')
                ->assertSee('Masuk ke SIPANDAI');
        });

        $this->assertSame($jumlahAwal, Aktivitas::count());
        $this->assertDatabaseHas('aktivitas', ['id' => $this->data['ak5']->id, 'aktif' => true]);
    }
}
