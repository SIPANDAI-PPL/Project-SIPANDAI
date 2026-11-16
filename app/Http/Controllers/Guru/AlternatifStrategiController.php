<?php

namespace App\Http\Controllers\Guru;

use App\Http\Controllers\Controller;
use App\Http\Requests\Guru\TambahAlternatifStrategiRequest;
use App\Http\Requests\Guru\UbahStatusAlternatifRequest;
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
    // Fitur: PBI-16 Tinjau, Pilih, atau Abaikan Alternatif Strategi

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

        // Satu item per anak: semua pola dan semua alternatif buatan guru milik anak itu.
        $daftarAnak = $kelas
            ? $this->layanan->kelompokkanPerAnak($this->layanan->susunUntukKelas($kelas), $this->layanan->saranManual($kelas))
            : collect();

        $barisTersaring = $this->saring($daftarAnak, $cari, $aspek);
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
            // Ringkasan menghitung anak, bukan pola.
            'jumlahPola' => $daftarAnak->filter(fn (array $item) => $item['pola']->isNotEmpty())->count(),
            // PBI-16: hanya anak yang masih punya alternatif berstatus disarankan.
            'jumlahSaran' => $daftarAnak->filter(fn (array $item) => $this->punyaDisarankan($item))->count(),
            'jumlahBelumCukup' => $daftarAnak->filter(fn (array $item) => $item['pola']->isNotEmpty() && ! $item['punya_saran'])->count(),
            'pilihanAspek' => AspekPerkembangan::query()->orderBy('id')->get(['id', 'nama']),
            'cari' => $cari,
            'aspek' => $aspek,
        ]);
    }

    /**
     * Mode Fokus per anak. Hak akses sama dengan TambahAlternatifStrategiRequest (NFR-04).
     */
    public function fokus(Request $request, Anak $anak): View
    {
        abort_unless(
            $anak->aktif && (int) $anak->kelas?->guru_id === (int) $request->user()->id,
            403,
            'Akses ditolak.',
        );

        return view('alternatif-strategi.fokus', [
            'data' => $this->layanan->dataAnak($anak),
            'kelas' => $anak->kelas,
            // Deskripsi dipakai untuk pratinjau di dialog tambah alternatif.
            'pilihanStrategi' => StrategiPembelajaran::query()->aktif()->orderBy('nama')->get(['id', 'nama', 'deskripsi']),
        ]);
    }

    public function store(TambahAlternatifStrategiRequest $request, Anak $anak): RedirectResponse
    {
        $data = $request->validated();

        $this->layanan->tambahManual($anak, (int) $data['strategi_id'], $data['catatan']);

        return redirect()
            ->route('guru.alternatif-strategi.anak', $anak)
            ->with('status', "Alternatif strategi untuk {$anak->nama} berhasil ditambahkan.");
    }

    /**
     * PBI-16: ubah status alternatif (disarankan/diabaikan -> dipilih, disarankan -> diabaikan).
     */
    public function ubahStatus(UbahStatusAlternatifRequest $request, AlternatifStrategi $alternatif): RedirectResponse
    {
        $alternatif = $this->layanan->ubahStatus($alternatif, $request->validated('status'), $request->user());

        return redirect()
            ->route('guru.alternatif-strategi.anak', $alternatif->anak_id)
            ->with('status', "Strategi {$alternatif->strategi->nama} {$alternatif->status}.");
    }

    /**
     * @param  array<string, mixed>  $item  satu item dari kelompokkanPerAnak
     */
    private function punyaDisarankan(array $item): bool
    {
        return $item['pola']->pluck('saran')->filter()->concat($item['manual'])
            ->contains(fn (AlternatifStrategi $saran) => $saran->status === AlternatifStrategiService::STATUS_DISARANKAN);
    }

    /**
     * Saring per anak. Anak tampil jika nama, salah satu pola, atau salah satu strategi cocok.
     * Filter aspek hanya melihat pola, jadi anak yang hanya punya alternatif buatan guru ikut tersembunyi.
     *
     * @param  Collection<int, array<string, mixed>>  $daftarAnak
     * @return Collection<int, array<string, mixed>>
     */
    private function saring(Collection $daftarAnak, string $cari, ?int $aspek): Collection
    {
        return $daftarAnak
            ->when($aspek !== null, fn (Collection $koleksi) => $koleksi->filter(
                fn (array $item) => $item['pola']->contains(
                    fn (array $pola) => $pola['aturan']->aspek_id === null || (int) $pola['aturan']->aspek_id === $aspek
                )
            ))
            ->when($cari !== '', function (Collection $koleksi) use ($cari) {
                $kunci = mb_strtolower($cari);

                return $koleksi->filter(function (array $item) use ($kunci) {
                    $teks = collect([$item['anak']->nama, $item['anak']->nama_panggilan])
                        ->concat($item['pola']->flatMap(fn (array $pola) => [
                            $pola['aturan']->strategiAsal->nama,
                            $pola['aturan']->strategiAlternatif->nama,
                            $pola['saran']?->strategi?->nama,
                        ]))
                        ->concat($item['manual']->map(fn (AlternatifStrategi $saran) => $saran->strategi?->nama))
                        ->filter()
                        ->implode(' ');

                    return str_contains(mb_strtolower($teks), $kunci);
                });
            })
            ->values();
    }
}
