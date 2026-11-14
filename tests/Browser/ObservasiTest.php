<?php

namespace Tests\Browser;

use App\Models\User;
use Laravel\Dusk\Browser;
use Tests\DuskTestCase;

class ObservasiTest extends DuskTestCase
{
    public function test_guru_dapat_membuka_halaman_observasi(): void
    {
        $guru = User::where('peran', 'guru')->firstOrFail();

        $this->browse(function (Browser $browser) use ($guru) {
            $browser->loginAs($guru)
                ->visit('/guru/observasi')
                ->assertSee('Data Observasi Perkembangan')
                ->assertSee('Tambah observasi');
        });
    }

    public function test_guru_dapat_membuka_formulir_observasi(): void
    {
        $guru = User::where('peran', 'guru')->firstOrFail();

        $this->browse(function (Browser $browser) use ($guru) {
            $browser->loginAs($guru)
                ->visit('/guru/observasi/tambah')
                ->assertSee('Formulir Observasi Perkembangan')
                ->assertSee('Identitas Observasi')
                ->assertSee('Pilih Kelas')
                ->assertSee('Pilih Anak')
                ->assertSee('Kegiatan dan Strategi')
                ->assertSee('Respons Anak')
                ->assertSee('Catatan Pendukung')
                ->assertSee('Simpan Observasi');
        });
    }
}
