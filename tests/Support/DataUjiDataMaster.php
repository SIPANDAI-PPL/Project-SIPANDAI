<?php

namespace Tests\Support;

use App\Models\Aktivitas;
use App\Models\Anak;
use App\Models\AspekPerkembangan;
use App\Models\AturanStrategi;
use App\Models\Kelas;
use App\Models\Observasi;
use App\Models\StrategiPembelajaran;
use App\Models\User;

/**
 * Data uji PBI-2 (dummy dari factory).
 *
 * Pemakaian:
 * - AK1 Bercerita: 2 observasi; AK2 Menyusun balok: 1; AK3 Permainan gerak: 1;
 *   AK4 Menggambar & mewarnai (nonaktif): 1; AK5 Bermain peran: belum dipakai.
 * - ST1 Media visual: 3 observasi; ST2 Kelompok kecil: 1 observasi + 1 aturan (asal);
 *   ST5 Pendampingan individu (nonaktif): 1 observasi; ST6 Bernyanyi bersama: hanya 1 aturan (alternatif).
 */
trait DataUjiDataMaster
{
    /**
     * @return array<string, mixed>
     */
    protected function buatDataUjiDataMaster(): array
    {
        $admin = User::factory()->admin()->create([
            'nama' => 'Admin SIPANDAI',
            'nama_pengguna' => 'admin',
        ]);
        $guru = User::factory()->create([
            'nama' => 'Mia Stacia',
            'nama_pengguna' => 'miastacia',
        ]);

        $ak1 = Aktivitas::factory()->create([
            'nama' => 'Bercerita',
            'deskripsi' => 'Kegiatan mendengarkan cerita interaktif atau mendongeng menggunakan buku atau alat peraga.',
        ]);
        $ak2 = Aktivitas::factory()->create([
            'nama' => 'Menyusun balok',
            'deskripsi' => 'Aktivitas konstruktif menggunakan balok kayu atau lego untuk mengasah motorik dan spasial.',
        ]);
        $ak3 = Aktivitas::factory()->create([
            'nama' => 'Permainan gerak',
            'deskripsi' => 'Aktivitas fisik kinestetik seperti melompat, menari, dan permainan olah tubuh.',
        ]);
        $ak4 = Aktivitas::factory()->nonaktif()->create([
            'nama' => 'Menggambar & mewarnai',
            'deskripsi' => 'Kegiatan ekspresi seni visual menggunakan krayon, pensil warna, atau cat air.',
        ]);
        $ak5 = Aktivitas::factory()->create([
            'nama' => 'Bermain peran',
            'deskripsi' => 'Aktivitas sosiodrama untuk melatih empati, imajinasi, dan interaksi sosial.',
        ]);

        $st1 = StrategiPembelajaran::factory()->create([
            'nama' => 'Media visual',
            'deskripsi' => 'Penyampaian materi menggunakan kartu gambar, video ilustratif, poster, dan objek visual konkret.',
        ]);
        $st2 = StrategiPembelajaran::factory()->create([
            'nama' => 'Kelompok kecil',
            'deskripsi' => 'Pendekatan bimbingan interaktif dalam kelompok 3-5 anak untuk mendorong partisipasi aktif.',
        ]);
        $st5 = StrategiPembelajaran::factory()->nonaktif()->create([
            'nama' => 'Pendampingan individu',
            'deskripsi' => 'Bimbingan intensif satu-satu oleh guru untuk anak yang memerlukan perhatian khusus.',
        ]);
        $st6 = StrategiPembelajaran::factory()->create([
            'nama' => 'Bernyanyi bersama',
            'deskripsi' => 'Stimulasi bahasa dan musikal melalui nyanyian tematik berima.',
        ]);

        $kelas = Kelas::factory()->create(['guru_id' => $guru->id]);
        $anak = Anak::factory()->create(['kelas_id' => $kelas->id]);
        $aspek = AspekPerkembangan::factory()->create();

        $observasi = fn (Aktivitas $aktivitas, StrategiPembelajaran $strategi) => Observasi::factory()->create([
            'anak_id' => $anak->id,
            'aktivitas_id' => $aktivitas->id,
            'strategi_id' => $strategi->id,
            'aspek_id' => $aspek->id,
        ]);

        $observasi($ak1, $st1);
        $observasi($ak1, $st1);
        $observasi($ak2, $st2);
        $observasi($ak3, $st1);
        $observasiAk4 = $observasi($ak4, $st5);

        $aturan = AturanStrategi::factory()->create([
            'strategi_asal_id' => $st2->id,
            'strategi_alternatif_id' => $st6->id,
        ]);

        return compact(
            'admin', 'guru',
            'ak1', 'ak2', 'ak3', 'ak4', 'ak5',
            'st1', 'st2', 'st5', 'st6',
            'anak', 'aspek', 'aturan', 'observasiAk4',
        );
    }
}
