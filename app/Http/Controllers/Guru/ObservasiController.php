<?php

namespace App\Http\Controllers\Guru;

use App\Enums\KategoriRespons;
use App\Http\Controllers\Controller;
use App\Http\Requests\Guru\ObservasiRequest;
use App\Models\Aktivitas;
use App\Models\Anak;
use App\Models\AspekPerkembangan;
use App\Models\Observasi;
use App\Models\StrategiPembelajaran;
use App\Support\TahunAjaran;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\View\View;

class ObservasiController extends Controller
{
    private const PER_HALAMAN = 10;

    private const URUTAN = [
        'terbaru' => 'Terbaru',
        'terlama' => 'Terlama',
        'nama' => 'Nama anak (A-Z)',
    ];

    public function index(Request $request): View|RedirectResponse
    {
        $guruId = $request->user()->id;

        $tahunAjaran = TahunAjaran::normalisasi(
            $request->query('tahun')
        );

        [$mulai, $akhir] = TahunAjaran::rentang($tahunAjaran);

        // Menggunakan nama_kelas sesuai struktur tabel database.
        $kelasList = DB::table('kelas')
            ->orderBy('nama_kelas')
            ->pluck('nama_kelas', 'id');

        // Seluruh observasi milik guru pada tahun ajaran terpilih.
        $dasar = Observasi::query()
            ->where('guru_id', $guruId)
            ->whereBetween('tanggal', [
                $mulai->toDateString(),
                $akhir->toDateString(),
            ]);

        $total = (clone $dasar)->count();

        $anakUnik = (clone $dasar)
            ->distinct()
            ->count('anak_id');

        $idKelas = Anak::query()
            ->whereIn('id', (clone $dasar)->select('anak_id'))
            ->distinct()
            ->pluck('kelas_id');

        $kelasUnik = $idKelas->count();

        $kelasNamaStr = $idKelas
            ->map(fn ($id) => $kelasList->get($id))
            ->filter()
            ->sort()
            ->implode(' dan ');

        // Filter, pencarian, dan urutan.
        $cari = $request->string('q')->trim()->toString();

        $kelasId = $request->integer('kelas') ?: null;

        $urut = $request->string('urut')->toString();

        $urut = array_key_exists($urut, self::URUTAN)
            ? $urut
            : 'terbaru';

        $query = (clone $dasar)
            ->with(['anak', 'aktivitas', 'aspek'])
            ->when(
                $kelasId,
                fn ($q) => $q->whereHas(
                    'anak',
                    fn ($a) => $a->where('kelas_id', $kelasId)
                )
            )
            ->when($cari !== '', function ($q) use ($cari) {
                $like = '%' . addcslashes($cari, '\\%_') . '%';

                $q->where(function ($w) use ($like) {
                    $w->whereHas('anak', fn ($a) => $a
                        ->where('nama', 'like', $like)
                        ->orWhere('nama_panggilan', 'like', $like)
                    )->orWhereHas('aktivitas', fn ($a) => $a
                        ->where('nama', 'like', $like)
                    );
                });
            });

        match ($urut) {
            'terlama' => $query
                ->orderBy('tanggal')
                ->orderBy('id'),

            'nama' => $query
                ->orderBy(
                    Anak::query()
                        ->select('nama')
                        ->whereColumn('anak.id', 'observasi.anak_id')
                        ->limit(1)
                )
                ->orderByDesc('tanggal'),

            default => $query
                ->orderByDesc('tanggal')
                ->orderByDesc('id'),
        };

        $daftar = $query
            ->paginate(self::PER_HALAMAN)
            ->withQueryString();

        // Jika halaman terakhir kosong setelah data dihapus.
        if ($daftar->isEmpty() && $daftar->currentPage() > 1) {
            return redirect()->to(
                $daftar->url($daftar->lastPage())
            );
        }

        $tahunList = collect(
            TahunAjaran::daftar(
                Observasi::where('guru_id', $guruId)->min('tanggal'),
                Observasi::where('guru_id', $guruId)->max('tanggal'),
            )
        )
            ->push($tahunAjaran)
            ->unique()
            ->sortDesc()
            ->values();

        return view('guru.observasi.index', [
            'observasi' => $daftar,
            'kelasList' => $kelasList,
            'tahunList' => $tahunList,
            'urutList' => self::URUTAN,
            'tahunAjaran' => $tahunAjaran,
            'urut' => $urut,
            'cari' => $cari,
            'kelasId' => $kelasId,
            'total' => $total,
            'anakUnik' => $anakUnik,
            'kelasUnik' => $kelasUnik,
            'kelasNamaStr' => $kelasNamaStr,
            'adaFilter' => $cari !== '' || $kelasId !== null,
        ]);
    }

    public function create(): View
    {
        return view(
            'guru.observasi.form',
            $this->dataFormulir()
        );
    }

    
public function store(ObservasiRequest $request): RedirectResponse
{
    try {
        Observasi::create(
            $request->dataObservasi() + [
                'guru_id' => $request->user()->id,
            ]
        );

        return redirect()
            ->route('guru.observasi.index')
            ->with('notifikasi', [
                'judul' => 'Observasi berhasil disimpan',
                'pesan' => 'Hasil observasi berhasil dicatat dan dapat dilihat melalui halaman ini.',
            ]);

    } catch (\Throwable $e) {
        report($e);

        return redirect()
            ->route('guru.observasi.index')
            ->with(
                'gagal',
                'Data observasi gagal disimpan. Silakan coba kembali.'
            );
    }
}

    
/**
 * Menyiapkan data untuk formulir tambah observasi.
 */
private function dataFormulir(): array
{
    // Mengambil kelas yang menjadi tanggung jawab guru login.
    $kelasList = DB::table('kelas')
        ->where('guru_id', auth()->id())
        ->select('id', 'nama_kelas as nama')
        ->orderBy('nama_kelas')
        ->get();

    $kelasIds = $kelasList->pluck('id');

    // Hanya mengambil anak aktif dari kelas milik guru tersebut.
    $anakList = Anak::query()
        ->where('aktif', true)
        ->whereIn('kelas_id', $kelasIds)
        ->orderBy('nama')
        ->get(['id', 'nama', 'kelas_id'])
        ->map(fn ($a) => [
            'id' => $a->id,
            'nama' => $a->nama,
            'kelas_id' => $a->kelas_id,
        ])
        ->values();

    return [
        'observasi' => null,

        'kelasList' => $kelasList,

        'anakList' => $anakList,

        'aktivitasList' => $this->pilihanAktif(
            Aktivitas::class
        ),

        'strategiList' => $this->pilihanAktif(
            StrategiPembelajaran::class
        ),

        'aspekList' => AspekPerkembangan::query()
            ->orderBy('nama')
            ->get(['id', 'nama']),

        'responsList' => KategoriRespons::cases(),

        'tahunAjaran' => TahunAjaran::dariTanggal(),
    ];
}


    /**
     * Mengambil pilihan data master yang masih aktif.
     */
    private function pilihanAktif(string $model)
    {
        return $model::query()
            ->where('aktif', true)
            ->orderBy('nama')
            ->get(['id', 'nama']);
    }
}
