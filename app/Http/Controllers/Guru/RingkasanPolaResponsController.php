<?php

namespace App\Http\Controllers\Guru;

use App\Enums\KategoriRespons;
use App\Http\Controllers\Controller;
use App\Http\Requests\Guru\FilterPolaResponsRequest;
use App\Models\Aktivitas;
use App\Models\Anak;
use App\Models\AspekPerkembangan;
use App\Models\Kelas;
use App\Models\StrategiPembelajaran;
use App\Services\RingkasanPolaResponsService;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\View\View;

class RingkasanPolaResponsController extends Controller
{
    // Fitur: PBI-12 Ringkasan Pola Respons Anak

    public function __construct(private readonly RingkasanPolaResponsService $ringkasan) {}

    /**
     * Daftar anak di kelas yang diampu guru, sebagai pintu masuk ringkasan per anak.
     */
    public function index(Request $request): View
    {
        // NFR-04: hanya kelas dengan guru_id = guru yang login.
        $daftarKelas = Kelas::query()
            ->where('guru_id', $request->user()->id)
            ->with(['anak' => fn ($query) => $query->withCount('observasi')->orderByDesc('aktif')->orderBy('nama')])
            ->orderBy('kelompok')
            ->orderBy('nama_kelas')
            ->get();

        return view('guru.pola-respons.index', ['daftarKelas' => $daftarKelas]);
    }

    public function show(FilterPolaResponsRequest $request, Anak $anak): View|Response
    {
        $kelas = $anak->kelas;

        // NFR-04: guru hanya boleh melihat ringkasan anak di kelas yang diampu.
        if ($kelas === null || $kelas->guru_id !== $request->user()->id) {
            return response()->view('guru.akses-ditolak', [
                'keterangan' => 'Ringkasan pola respons hanya dapat dilihat oleh guru pengampu kelas anak tersebut.',
            ], 403);
        }

        $data = $request->validated();
        $filter = [
            'periode' => $data['periode'] ?? 'semua',
            'aspek' => $data['aspek'] ?? null,
            'aktivitas' => $data['aktivitas'] ?? null,
            'strategi' => $data['strategi'] ?? null,
            'respons' => $data['respons'] ?? null,
        ];

        $filterAktif = $filter['periode'] !== 'semua'
            || collect($filter)->except('periode')->filter(fn ($nilai) => $nilai !== null)->isNotEmpty();

        return view('guru.pola-respons.show', [
            'anak' => $anak,
            'kelas' => $kelas,
            'ringkasan' => $this->ringkasan->hitung($anak, $filter),
            'filter' => $filter,
            'filterAktif' => $filterAktif,
            'opsiFilter' => [
                'periode' => RingkasanPolaResponsService::PERIODE,
                'aspek' => AspekPerkembangan::query()->orderBy('nama')->get(['id', 'nama']),
                'aktivitas' => Aktivitas::query()->orderBy('nama')->get(['id', 'nama']),
                'strategi' => StrategiPembelajaran::query()->orderBy('nama')->get(['id', 'nama']),
                'respons' => KategoriRespons::cases(),
            ],
        ]);
    }
}
