<?php

namespace App\Http\Controllers\Guru;

use App\Http\Controllers\Controller;
use App\Http\Requests\Guru\TambahAlternatifStrategiRequest;
use App\Models\AlternatifStrategi;
use App\Models\Anak;
use App\Models\AspekPerkembangan;
use App\Models\Kelas;
use App\Models\StrategiPembelajaran;
use App\Services\AlternatifStrategiService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Support\Collection;
use Illuminate\View\View;

class AlternatifStrategiController extends Controller
{
    // Fitur: PBI-15 Penyusunan Alternatif Strategi Berdasarkan Aturan

    private const PER_HALAMAN = 10;

    public function __construct(private AlternatifStrategiService $layanan) {}

    public function index(Request $request): View
    {
        $guru = $request->user();
        $daftarKelas = $guru->kelasDiampu()->orderBy('id')->get();

        $kelas = $daftarKelas->first();
        if ($request->filled('kelas')) {
            $kelas = Kelas::query()->findOrFail($request->query('kelas'));
            // Guru hanya boleh melihat kelas yang diampunya (NFR-04).
            abort_unless((int) $kelas->guru_id === (int) $guru->id, 403, 'Akses ditolak.');
        }

        $cari = trim((string) $request->query('q', ''));
        $aspek = ctype_digit((string) $request->query('aspek', '')) ? (int) $request->query('aspek') : null;

        $daftarPola = $kelas ? $this->layanan->susunUntukKelas($kelas) : collect();
        $daftarManual = $kelas ? $this->layanan->saranManual($kelas) : collect();

        $baris = $daftarPola
            ->map(fn (array $pola) => ['jenis' => 'pola'] + $pola)
            ->concat($daftarManual->map(fn (AlternatifStrategi $saran) => [
                'jenis' => 'manual',
                'anak' => $saran->anak,
                'saran' => $saran,
            ]))
            ->sortBy(fn (array $item) => $item['anak']->nama)
            ->values();

        $barisTersaring = $this->saring($baris, $cari, $aspek);
        $halaman = LengthAwarePaginator::resolveCurrentPage();

        return view('alternatif-strategi.index', [
            'daftarKelas' => $daftarKelas,
            'kelas' => $kelas,
            'daftarBaris' => new LengthAwarePaginator(
                $barisTersaring->forPage($halaman, self::PER_HALAMAN)->values(),
                $barisTersaring->count(),
                self::PER_HALAMAN,
                $halaman,
                ['path' => $request->url(), 'query' => $request->query()],
            ),
            'jumlahPola' => $daftarPola->count(),
            'jumlahSaran' => $daftarPola->whereNotNull('saran')->count() + $daftarManual->count(),
            'jumlahBelumCukup' => $daftarPola->whereNull('saran')->count(),
            'daftarAnak' => $kelas ? $kelas->anak()->where('aktif', true)->orderBy('nama')->get(['id', 'nama']) : collect(),
            'pilihanAspek' => AspekPerkembangan::query()->orderBy('id')->get(['id', 'nama']),
            'pilihanStrategi' => StrategiPembelajaran::query()->aktif()->orderBy('nama')->get(['id', 'nama']),
            'cari' => $cari,
            'aspek' => $aspek,
        ]);
    }

    public function store(TambahAlternatifStrategiRequest $request, Anak $anak): RedirectResponse
    {
        $data = $request->validated();

        $this->layanan->tambahManual($anak, (int) $data['strategi_id'], $data['catatan']);

        return redirect()
            ->route('guru.alternatif-strategi.index', ['kelas' => $anak->kelas_id])
            ->with('status', "Alternatif strategi untuk {$anak->nama} berhasil ditambahkan.");
    }

    /**
     * Cari berdasarkan nama anak atau nama strategi; filter aspek hanya berlaku untuk pola dari aturan.
     *
     * @param  Collection<int, array<string, mixed>>  $baris
     * @return Collection<int, array<string, mixed>>
     */
    private function saring(Collection $baris, string $cari, ?int $aspek): Collection
    {
        return $baris
            ->when($aspek !== null, fn (Collection $koleksi) => $koleksi->filter(
                fn (array $item) => $item['jenis'] === 'pola'
                    && ($item['aturan']->aspek_id === null || (int) $item['aturan']->aspek_id === $aspek)
            ))
            ->when($cari !== '', function (Collection $koleksi) use ($cari) {
                $kunci = mb_strtolower($cari);

                return $koleksi->filter(function (array $item) use ($kunci) {
                    $teks = implode(' ', array_filter([
                        $item['anak']->nama,
                        $item['anak']->nama_panggilan,
                        $item['saran']?->strategi?->nama,
                        $item['jenis'] === 'pola' ? $item['aturan']->strategiAsal->nama : null,
                        $item['jenis'] === 'pola' ? $item['aturan']->strategiAlternatif->nama : null,
                    ]));

                    return str_contains(mb_strtolower($teks), $kunci);
                });
            })
            ->values();
    }
}
