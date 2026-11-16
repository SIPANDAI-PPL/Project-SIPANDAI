<?php

namespace Tests\Unit;

use App\Enums\KategoriRespons;
use App\Models\AspekPerkembangan;
use App\Models\AturanStrategi;
use App\Models\StrategiPembelajaran;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class AturanStrategiScopeTest extends TestCase
{
    use RefreshDatabase;

    public function test_tc_04_13_scope_aktif_hanya_mengembalikan_aturan_aktif(): void
    {
        $st = [];
        foreach (['ST1' => true, 'ST2' => true, 'ST3' => true, 'ST4' => true, 'ST5' => false, 'ST6' => true] as $kode => $aktif) {
            $st[$kode] = StrategiPembelajaran::factory()->create(['aktif' => $aktif]);
        }
        $bahasa = AspekPerkembangan::factory()->create(['nama' => 'Bahasa']);
        $sosial = AspekPerkembangan::factory()->create(['nama' => 'Sosial-emosional']);
        $kognitif = AspekPerkembangan::factory()->create(['nama' => 'Kognitif']);

        $at1 = AturanStrategi::factory()->create([
            'respons_pemicu' => KategoriRespons::KurangTertarik, 'minimal_kejadian' => 3,
            'strategi_asal_id' => $st['ST1']->id, 'strategi_alternatif_id' => $st['ST2']->id,
            'aspek_id' => null, 'aktif' => true,
        ]);
        $at2 = AturanStrategi::factory()->create([
            'respons_pemicu' => KategoriRespons::BelumTerlibat, 'minimal_kejadian' => 2,
            'strategi_asal_id' => $st['ST4']->id, 'strategi_alternatif_id' => $st['ST3']->id,
            'aspek_id' => $bahasa->id, 'aktif' => true,
        ]);
        AturanStrategi::factory()->create([
            'respons_pemicu' => KategoriRespons::BelumTerlibat, 'minimal_kejadian' => 3,
            'strategi_asal_id' => $st['ST2']->id, 'strategi_alternatif_id' => $st['ST5']->id,
            'aspek_id' => $sosial->id, 'aktif' => false,
        ]);
        $at4 = AturanStrategi::factory()->create([
            'respons_pemicu' => KategoriRespons::TerlibatDenganPendampingan, 'minimal_kejadian' => 2,
            'strategi_asal_id' => $st['ST3']->id, 'strategi_alternatif_id' => $st['ST6']->id,
            'aspek_id' => $kognitif->id, 'aktif' => true,
        ]);

        $hasil = AturanStrategi::aktif()->orderBy('id')->pluck('id')->all();

        $this->assertSame([$at1->id, $at2->id, $at4->id], $hasil);
    }
}
