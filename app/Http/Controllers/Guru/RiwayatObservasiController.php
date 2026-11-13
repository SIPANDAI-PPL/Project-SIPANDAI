<?php

namespace App\Http\Controllers\Guru;

use App\Enums\KategoriRespons;
use App\Http\Controllers\Controller;
use App\Http\Requests\Guru\FilterRiwayatObservasiRequest;
use App\Models\Aktivitas;
use App\Models\Anak;
use App\Models\AspekPerkembangan;
use App\Models\Observasi;
use App\Models\StrategiPembelajaran;
use Illuminate\Support\Carbon;
use Illuminate\View\View;

class RiwayatObservasiController extends Controller
{
    // Fitur: PBI-11 Riwayat Observasi Anak dengan Filter

    public function index(FilterRiwayatObservasiRequest $request, Anak $anak): View
    {
        $kelas = $anak->kelas;

        // NFR-04: guru hanya boleh membuka riwayat anak di kelas yang diampu.
        abort_unless($kelas !== null && $kelas->guru_id === $request->user()->id, 403, 'Akses ditolak.');

        $dasar = Observasi::query()->where('anak_id', $anak->id);

        // Periode "30 hari terakhir" dihitung dari observasi terbaru anak agar data lama
        // tetap terlihat saat demo, bukan dari tanggal hari ini.
        $tanggalTerbaru = (clone $dasar)->max('tanggal');
        $totalSemua = (clone $dasar)->count();

        $data = $request->validated();
        $filter = [
            'periode' => $data['periode'] ?? '30',
            'aspek' => $data['aspek'] ?? null,
            'aktivitas' => $data['aktivitas'] ?? null,
            'strategi' => $data['strategi'] ?? null,
            'respons' => $data['respons'] ?? null,
        ];

        $filterAktif = $filter['periode'] !== '30'
            || $filter['aspek'] !== null
            || $filter['aktivitas'] !== null
            || $filter['strategi'] !== null
            || $filter['respons'] !== null;

        $query = (clone $dasar)
            ->with(['aktivitas', 'strategi', 'aspek'])
            ->withCount('riwayatPerubahan');

        $rentang = $this->rentangPeriode($filter['periode'], $tanggalTerbaru);

        if ($rentang !== null) {
            $query->whereBetween('tanggal', $rentang);
        }

        if ($filter['aspek'] !== null) {
            $query->where('aspek_id', $filter['aspek']);
        }

        if ($filter['aktivitas'] !== null) {
            $query->where('aktivitas_id', $filter['aktivitas']);
        }

        if ($filter['strategi'] !== null) {
            $query->where('strategi_id', $filter['strategi']);
        }

        if ($filter['respons'] !== null) {
            $query->where('kategori_respons', $filter['respons']);
        }

        // Kronologis: observasi terbaru di atas. id dipakai sebagai penentu urutan
        // ketika ada beberapa observasi pada tanggal yang sama.
        $observasis = $query
            ->orderByDesc('tanggal')
            ->orderByDesc('id')
            ->get()
            ->map(fn (Observasi $observasi) => [
                'id' => $observasi->id,
                'tanggal' => $observasi->tanggal->format('Y-m-d'),
                'aktivitas' => $observasi->aktivitas->nama,
                'aspek' => $observasi->aspek->nama,
                'strategi' => $observasi->strategi->nama,
                'kategori_respons' => $observasi->kategori_respons->value,
                'catatan_pendukung' => $observasi->catatan_pendukung,
                'jumlah_perubahan' => $observasi->riwayat_perubahan_count,
            ]);

        return view('guru.riwayat-observasi', [
            'anak' => [
                'id' => $anak->id,
                'nama' => $anak->nama,
                'nama_panggilan' => $anak->nama_panggilan,
                'kelas' => trim(implode(' - ', array_filter([$kelas->kelompok, $kelas->nama_kelas]))),
            ],
            'observasis' => $observasis,
            'totalSemua' => $totalSemua,
            'totalObservasi' => $observasis->count(),
            'filter' => $filter,
            'filterAktif' => $filterAktif,
            'opsiFilter' => [
                'periode' => $this->opsiPeriode($anak),
                'aspek' => AspekPerkembangan::query()->orderBy('nama')->get(),
                'aktivitas' => Aktivitas::query()->orderBy('nama')->get(),
                'strategi' => StrategiPembelajaran::query()->orderBy('nama')->get(),
                'respons' => collect(KategoriRespons::cases()),
            ],
        ]);
    }

    /**
     * Pilihan bulan yang benar-benar ada pada riwayat anak, terbaru lebih dulu.
     * Nilainya berformat YYYY-MM, misalnya 2026-10 untuk Oktober 2026.
     *
     * @return array<string, string>
     */
    private function opsiPeriode(Anak $anak): array
    {
        return Observasi::query()
            ->where('anak_id', $anak->id)
            ->select('tanggal')
            ->distinct()
            ->orderByDesc('tanggal')
            ->pluck('tanggal')
            ->map(fn ($tanggal) => Carbon::parse($tanggal)->format('Y-m'))
            ->unique()
            ->mapWithKeys(fn (string $bulan) => [
                $bulan => Carbon::createFromFormat('Y-m', $bulan)->locale('id')->translatedFormat('F Y'),
            ])
            ->all();
    }

    /**
     * Batas periode inklusif (tanggal mulai dan selesai ikut dihitung).
     * Null berarti seluruh periode.
     *
     * @return array{0: string, 1: string}|null
     */
    private function rentangPeriode(string $periode, ?string $tanggalTerbaru): ?array
    {
        if (preg_match('/^(\d{4})-(\d{2})$/', $periode, $cocok) === 1) {
            $awalBulan = Carbon::createFromDate((int) $cocok[1], (int) $cocok[2], 1)->startOfMonth();

            return [$awalBulan->toDateString(), $awalBulan->copy()->endOfMonth()->toDateString()];
        }

        return match ($periode) {
            '30' => $tanggalTerbaru === null ? null : [
                Carbon::parse($tanggalTerbaru)->subDays(29)->toDateString(),
                $tanggalTerbaru,
            ],
            '7' => $tanggalTerbaru === null ? null : [
                Carbon::parse($tanggalTerbaru)->subDays(6)->toDateString(),
                $tanggalTerbaru,
            ],
            default => null,
        };
    }
}
