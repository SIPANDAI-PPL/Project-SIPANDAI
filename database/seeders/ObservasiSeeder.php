<?php

namespace Database\Seeders;

use App\Enums\KategoriRespons;
use App\Models\Aktivitas;
use App\Models\Anak;
use App\Models\AspekPerkembangan;
use App\Models\Kelas;
use App\Models\Observasi;
use App\Models\RiwayatPerubahan;
use App\Models\StrategiPembelajaran;
use Illuminate\Database\Seeder;

/**
 * Data dummy riwayat observasi untuk demo PBI-11.
 *
 * Tidak dipanggil DatabaseSeeder karena seeder bawaan hanya mengisi data master.
 * Jalankan manual saat demo:
 * php artisan db:seed --class=ObservasiSeeder
 *
 * Anak pertama kelas Melati mendapat 14 observasi persis seperti data uji PBI:
 * 12 pada Oktober 2026 dan 2 pada September 2026, dengan komposisi
 * 4 terlibat aktif, 3 terlibat dengan pendampingan, 6 kurang tertarik,
 * dan 1 belum terlibat.
 */
class ObservasiSeeder extends Seeder
{
    public function run(): void
    {
        if (Observasi::query()->exists()) {
            return;
        }

        $kelasMelati = Kelas::query()->where('nama_kelas', 'Melati')->first();

        if ($kelasMelati === null) {
            return;
        }

        $anakMelati = Anak::query()
            ->where('kelas_id', $kelasMelati->id)
            ->orderBy('id')
            ->get();

        if ($anakMelati->isEmpty()) {
            return;
        }

        $aspek = AspekPerkembangan::query()->pluck('id', 'nama');
        $aktivitas = Aktivitas::query()->pluck('id', 'nama');
        $strategi = StrategiPembelajaran::query()->pluck('id', 'nama');
        $guruId = $kelasMelati->guru_id;

        $buat = function (Anak $anak, array $baris) use ($aspek, $aktivitas, $strategi, $guruId): Observasi {
            return Observasi::create([
                'anak_id' => $anak->id,
                'guru_id' => $guruId,
                'aktivitas_id' => $aktivitas[$baris['aktivitas']],
                'strategi_id' => $strategi[$baris['strategi']],
                'aspek_id' => $aspek[$baris['aspek']],
                'tanggal' => $baris['tanggal'],
                'kategori_respons' => $baris['respons'],
                'catatan_pendukung' => $baris['catatan'],
            ]);
        };

        $riwayatAnakPertama = [
            ['tanggal' => '2026-10-15', 'aktivitas' => 'Menyusun balok', 'aspek' => 'Kognitif', 'strategi' => 'Kerja Kelompok', 'respons' => KategoriRespons::KurangTertarik, 'catatan' => 'Bertahan ±5 menit. Kembali antusias saat diberi pilihan menggambar menara baloknya.'],
            ['tanggal' => '2026-10-13', 'aktivitas' => 'Bernyanyi bersama', 'aspek' => 'Fisik Motorik', 'strategi' => 'Permainan Gerak', 'respons' => KategoriRespons::TerlibatAktif, 'catatan' => 'Antusias, mengikuti semua gerakan sampai selesai.'],
            ['tanggal' => '2026-10-13', 'aktivitas' => 'Menempel kertas', 'aspek' => 'Bahasa', 'strategi' => 'Media Visual', 'respons' => KategoriRespons::TerlibatDenganPendampingan, 'catatan' => 'Mau menjawab pertanyaan setelah ditunjukkan gambar tokoh.'],
            ['tanggal' => '2026-10-09', 'aktivitas' => 'Menyusun balok', 'aspek' => 'Kognitif', 'strategi' => 'Media Visual', 'respons' => KategoriRespons::KurangTertarik, 'catatan' => 'Berhenti setelah menara jatuh, tidak mau mencoba lagi.'],
            ['tanggal' => '2026-10-09', 'aktivitas' => 'Bermain peran', 'aspek' => 'Sosial Emosional', 'strategi' => 'Kerja Kelompok', 'respons' => KategoriRespons::KurangTertarik, 'catatan' => 'Enggan bergabung saat pembagian peran, memilih mengamati dari dekat.'],
            ['tanggal' => '2026-10-07', 'aktivitas' => 'Bernyanyi bersama', 'aspek' => 'Bahasa', 'strategi' => 'Kerja Kelompok', 'respons' => KategoriRespons::TerlibatAktif, 'catatan' => 'Mengikuti lagu dan gerakan tangan dengan semangat.'],
            ['tanggal' => '2026-10-05', 'aktivitas' => 'Mewarnai gambar', 'aspek' => 'Fisik Motorik', 'strategi' => 'Media Visual', 'respons' => KategoriRespons::TerlibatDenganPendampingan, 'catatan' => 'Perlu diingatkan untuk memegang krayon dengan benar.'],
            ['tanggal' => '2026-10-03', 'aktivitas' => 'Menyusun balok', 'aspek' => 'Kognitif', 'strategi' => 'Media Visual', 'respons' => KategoriRespons::KurangTertarik, 'catatan' => 'Beralih ke kegiatan lain setelah memasang dua potongan.'],
            ['tanggal' => '2026-10-03', 'aktivitas' => 'Bermain peran', 'aspek' => 'Sosial Emosional', 'strategi' => 'Kerja Kelompok', 'respons' => KategoriRespons::TerlibatAktif, 'catatan' => 'Berbagi peran dengan teman tanpa diarahkan.'],
            ['tanggal' => '2026-10-01', 'aktivitas' => 'Menempel kertas', 'aspek' => 'Fisik Motorik', 'strategi' => 'Media Visual', 'respons' => KategoriRespons::BelumTerlibat, 'catatan' => 'Belum mau mencoba, hanya mengamati teman.'],
            ['tanggal' => '2026-10-01', 'aktivitas' => 'Menyusun balok', 'aspek' => 'Kognitif', 'strategi' => 'Kerja Kelompok', 'respons' => KategoriRespons::KurangTertarik, 'catatan' => 'Menyusun tiga balok lalu meninggalkan area kegiatan.'],
            ['tanggal' => '2026-10-01', 'aktivitas' => 'Menempel kertas', 'aspek' => 'Bahasa', 'strategi' => 'Media Visual', 'respons' => KategoriRespons::TerlibatDenganPendampingan, 'catatan' => 'Mendengarkan cerita sampai selesai dengan bantuan gambar.'],
            ['tanggal' => '2026-09-28', 'aktivitas' => 'Menyusun balok', 'aspek' => 'Kognitif', 'strategi' => 'Kerja Kelompok', 'respons' => KategoriRespons::KurangTertarik, 'catatan' => 'Hanya bertahan sebentar, lalu pindah ke area lain.'],
            ['tanggal' => '2026-09-22', 'aktivitas' => 'Bernyanyi bersama', 'aspek' => 'Fisik Motorik', 'strategi' => 'Kerja Kelompok', 'respons' => KategoriRespons::TerlibatAktif, 'catatan' => 'Mengikuti seluruh rangkaian gerak dengan gembira.'],
        ];

        $observasiAnakPertama = collect($riwayatAnakPertama)->map(fn (array $baris) => $buat($anakMelati[0], $baris));

        // Satu catatan pernah diubah supaya penanda "Diubah 1x" ikut terlihat.
        RiwayatPerubahan::create([
            'dilakukan_oleh' => $guruId,
            'observasi_id' => $observasiAnakPertama[1]->id,
            'aksi' => 'ubah',
            'keterangan' => 'Kategori respons diperbarui dari kurang tertarik menjadi terlibat aktif.',
        ]);

        // Anak ketiga punya observasi sendiri (TC-11-13: hanya data anak yang dipilih).
        if ($anakMelati->has(2)) {
            $buat($anakMelati[2], [
                'tanggal' => '2026-10-12',
                'aktivitas' => 'Mewarnai gambar',
                'aspek' => 'Seni',
                'strategi' => 'Media Visual',
                'respons' => KategoriRespons::TerlibatAktif,
                'catatan' => 'Mewarnai seluruh bidang gambar tanpa bantuan.',
            ]);
        }

        // Anak keempat nonaktif tetapi tetap menyimpan riwayat lama (TC-11-14).
        if ($anakMelati->has(3)) {
            $anakMelati[3]->update(['aktif' => false]);

            $buat($anakMelati[3], [
                'tanggal' => '2026-09-15',
                'aktivitas' => 'Bermain peran',
                'aspek' => 'Sosial Emosional',
                'strategi' => 'Kerja Kelompok',
                'respons' => KategoriRespons::TerlibatDenganPendampingan,
                'catatan' => 'Mau bergabung setelah diajak teman sekelompok.',
            ]);
        }
    }
}
