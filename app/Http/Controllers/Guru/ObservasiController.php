<?php

namespace App\Http\Controllers\Guru;

use App\Enums\KategoriRespons;
use App\Http\Controllers\Controller;
use App\Http\Requests\ObservasiRequest;
use App\Models\Aktivitas;
use App\Models\Anak;
use App\Models\AspekPerkembangan;
use App\Models\Observasi;
use App\Models\StrategiPembelajaran;
use App\Support\TahunAjaran;
use Illuminate\Database\QueryException;
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
                $like = '%' . addcslashes($cari, '\\%\_') . '%';

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
        Observasi::create(
            $request->dataObservasi() + [
                'guru_id' => $request->user()->id,
            ]
        );

        return redirect()
            ->route('guru.observasi.index')
            ->with('notifikasi', [
                'judul' => 'Observasi berhasil disimpan',
                'pesan' => 'Hasil observasi dapat dilihat, diedit, atau dihapus melalui halaman ini.',
            ]);
    }

    public function edit(
        Request $request,
        Observasi $observasi
    ): View {
        $this->pastikanMilikGuru($request, $observasi);

        $observasi->load('anak');

        return view(
            'guru.observasi.form',
            $this->dataFormulir($observasi)
        );
    }

    public function update(
        ObservasiRequest $request,
        Observasi $observasi
    ): RedirectResponse {
        $this->pastikanMilikGuru($request, $observasi);

        $observasi->update(
            $request->dataObservasi()
        );

        return redirect()
            ->route('guru.observasi.index')
            ->with('notifikasi', [
                'judul' => 'Observasi berhasil diperbarui',
                'pesan' => 'Perubahan pada hasil observasi sudah tersimpan.',
            ]);
    }

    public function destroy(
        Request $request,
        Observasi $observasi
    ): RedirectResponse {
        $this->pastikanMilikGuru($request, $observasi);

        try {
            $observasi->delete();
        } catch (QueryException) {
            return back()->with(
                'gagal',
                'Observasi tidak dapat dihapus karena masih terhubung dengan data lain.'
            );
        }

        return back()->with('notifikasi', [
            'judul' => 'Observasi berhasil dihapus',
            'pesan' => 'Data observasi tersebut sudah dihapus dari daftar.',
        ]);
    }

    /**
     * Guru hanya boleh mengubah atau menghapus observasi miliknya sendiri.
     */
    private function pastikanMilikGuru(
        Request $request,
        Observasi $observasi
    ): void {
        abort_unless(
            (int) $observasi->guru_id === (int) $request->user()->id,
            404
        );
    }

    /**
     * Menyiapkan data untuk formulir observasi.
     */
    private function dataFormulir(
        ?Observasi $observasi = null
    ): array {
        $anakList = Anak::query()
            ->where(
                fn ($q) => $q->where('aktif', true)
                    ->when(
                        $observasi,
                        fn ($w) => $w->orWhere('id', $observasi->anak_id)
                    )
            )
            ->orderBy('nama')
            ->get(['id', 'nama', 'kelas_id'])
            ->map(fn ($a) => [
                'id' => $a->id,
                'nama' => $a->nama,
                'kelas_id' => $a->kelas_id,
            ])
            ->values();

        return [
            'observasi' => $observasi,

            // Mengambil nama_kelas, tetapi tetap menyediakan
            // atribut nama agar kompatibel dengan file Blade.
            'kelasList' => DB::table('kelas')
                ->select('id', 'nama_kelas as nama')
                ->orderBy('nama_kelas')
                ->get(),

            'anakList' => $anakList,

            'aktivitasList' => $this->pilihanAktif(
                Aktivitas::class,
                $observasi?->aktivitas_id
            ),

            'strategiList' => $this->pilihanAktif(
                StrategiPembelajaran::class,
                $observasi?->strategi_id
            ),

            'aspekList' => AspekPerkembangan::query()
                ->orderBy('nama')
                ->get(['id', 'nama']),

            'responsList' => KategoriRespons::cases(),

            'tahunAjaran' => TahunAjaran::dariTanggal(),
        ];
    }

    /**
     * Mengambil pilihan aktif dan mempertahankan pilihan lama
     * yang sudah dinonaktifkan ketika melakukan edit.
     */
    private function pilihanAktif(
        string $model,
        ?int $idTerpilih
    ) {
        return $model::query()
            ->where(
                fn ($q) => $q->where('aktif', true)
                    ->when(
                        $idTerpilih,
                        fn ($w) => $w->orWhere('id', $idTerpilih)
                    )
            )
            ->orderBy('nama')
            ->get(['id', 'nama']);
    }
}
