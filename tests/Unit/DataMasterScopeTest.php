<?php

namespace Tests\Unit;

use App\Models\Aktivitas;
use App\Models\Observasi;
use App\Models\StrategiPembelajaran;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\Support\DataUjiDataMaster;
use Tests\TestCase;

class DataMasterScopeTest extends TestCase
{
    use DataUjiDataMaster;
    use RefreshDatabase;

    public function test_tc_02_12_scope_aktif_tidak_memuat_data_nonaktif(): void
    {
        $data = $this->buatDataUjiDataMaster();

        $aktivitasAktif = Aktivitas::aktif()->pluck('id');
        $this->assertCount(4, $aktivitasAktif);
        $this->assertNotContains($data['ak4']->id, $aktivitasAktif);
        $this->assertContains($data['ak1']->id, $aktivitasAktif);

        $strategiAktif = StrategiPembelajaran::aktif()->pluck('id');
        $this->assertCount(3, $strategiAktif);
        $this->assertNotContains($data['st5']->id, $strategiAktif);
        $this->assertContains($data['st6']->id, $strategiAktif);
    }

    public function test_tc_02_13_observasi_lama_tetap_membaca_nama_data_nonaktif(): void
    {
        $data = $this->buatDataUjiDataMaster();

        $observasi = Observasi::with(['aktivitas', 'strategi'])->findOrFail($data['observasiAk4']->id);

        $this->assertFalse($observasi->aktivitas->aktif);
        $this->assertSame('Menggambar & mewarnai', $observasi->aktivitas->nama);
        $this->assertSame('Pendampingan individu', $observasi->strategi->nama);
    }
}
