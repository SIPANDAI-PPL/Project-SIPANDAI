<?php

namespace Tests\Browser;

use App\Models\Observasi;
use Illuminate\Foundation\Testing\DatabaseMigrations;
use Laravel\Dusk\Browser;
use Tests\DuskTestCase;
use Tests\Support\DataUjiDataMaster;

class DataMasterTest extends DuskTestCase
{
    use DatabaseMigrations;
    use DataUjiDataMaster;

    public function test_tc_02_05_menambah_aktivitas_bernyanyi_lewat_modal(): void
    {
        $data = $this->buatDataUjiDataMaster();

        $this->browse(function (Browser $browser) use ($data) {
            $browser->loginAs($data['admin'])
                ->visitRoute('data-master.aktivitas.index')
                ->click('@tombol-tambah')
                ->whenAvailable('@modal-tambah', function (Browser $modal) {
                    $modal->type('nama', 'Bernyanyi')
                        ->type('deskripsi', 'Bernyanyi lagu anak bersama-sama.')
                        ->click('@simpan-tambah');
                })
                ->waitForText('Aktivitas Bernyanyi berhasil ditambahkan.')
                ->assertSeeIn('@badge-jumlah', '6 Aktivitas');
        });

        $this->assertDatabaseHas('aktivitas', ['nama' => 'Bernyanyi', 'aktif' => true]);
    }

    public function test_tc_02_08_menonaktifkan_bercerita_lewat_dialog(): void
    {
        $data = $this->buatDataUjiDataMaster();
        $ak1 = $data['ak1'];

        $this->browse(function (Browser $browser) use ($data, $ak1) {
            $browser->loginAs($data['admin'])
                ->visitRoute('data-master.aktivitas.index')
                ->click('@menu-aksi-'.$ak1->id)
                ->waitFor('@aksi-nonaktifkan-'.$ak1->id)
                ->click('@aksi-nonaktifkan-'.$ak1->id)
                ->whenAvailable('@modal-nonaktifkan', function (Browser $modal) {
                    $modal->assertSee('Nonaktifkan aktivitas Bercerita?')
                        ->assertSee('2 observasi')
                        ->click('@konfirmasi-nonaktifkan');
                })
                ->waitForText('Aktivitas Bercerita dinonaktifkan.')
                ->assertSeeIn('@baris-'.$ak1->id, 'Nonaktif');
        });

        $this->assertDatabaseHas('aktivitas', ['id' => $ak1->id, 'aktif' => false]);
        $this->assertSame(2, Observasi::where('aktivitas_id', $ak1->id)->count());
    }

    public function test_tc_02_10_menghapus_bermain_peran_lewat_dialog(): void
    {
        $data = $this->buatDataUjiDataMaster();
        $ak5 = $data['ak5'];

        $this->browse(function (Browser $browser) use ($data, $ak5) {
            $browser->loginAs($data['admin'])
                ->visitRoute('data-master.aktivitas.index')
                ->click('@menu-aksi-'.$ak5->id)
                ->waitFor('@aksi-hapus-'.$ak5->id)
                ->click('@aksi-hapus-'.$ak5->id)
                ->whenAvailable('@modal-hapus', function (Browser $modal) {
                    $modal->assertSee('Hapus aktivitas Bermain peran?')
                        ->click('@konfirmasi-hapus');
                })
                ->waitForText('Aktivitas Bermain peran berhasil dihapus.')
                ->assertMissing('@baris-'.$ak5->id);
        });

        $this->assertDatabaseMissing('aktivitas', ['id' => $ak5->id]);
    }
}
