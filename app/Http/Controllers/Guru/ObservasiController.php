<?php

namespace App\Http\Controllers\Guru;

use App\Enums\KategoriRespons;
use App\Http\Controllers\Controller;
use App\Http\Requests\Guru\ObservasiRequest;
use App\Models\Aktivitas;
use App\Models\Anak;
use App\Models\AspekPerkembangan;
use App\Models\Observasi;
use App\Models\RiwayatPerubahan;
use App\Models\StrategiPembelajaran;
use App\Support\TahunAjaran;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\View\View;

class ObservasiController extends Controller
{
    // Fitur: PBI-7 Formulir Pencatatan Observasi per Anak.
    // Fitur: PBI-8 Ubah dan Hapus Observasi beserta Riwayat Perubahan.

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
                $like = '%'.addcslashes($cari, '\\%_').'%';

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
     * Fitur: PBI-8 Ubah dan Hapus Observasi beserta Riwayat Perubahan.
     * Menampilkan detail satu observasi milik guru yang sedang login.
     */
    public function show(Request $request, Observasi $observasi): View
    {
        $this->pastikanMilikGuru($request, $observasi);

        return view('guru.observasi.detail', [
            'observasi' => $observasi->load(['anak.kelas', 'aktivitas', 'strategi', 'aspek']),
            'riwayat' => $this->riwayatObservasi($observasi),
        ]);
    }

    /**
     * Menampilkan formulir ubah observasi.
     */
    public function edit(Request $request, Observasi $observasi): View
    {
        $this->pastikanMilikGuru($request, $observasi);

        return view('guru.observasi.ubah', array_merge(
            $this->dataFormulir($observasi),
            [
                'observasi' => $observasi->load(['anak.kelas', 'aktivitas', 'strategi', 'aspek']),
                'riwayat' => $this->riwayatObservasi($observasi),
            ]
        ));
    }

    /**
     * Menyimpan perubahan observasi dan mencatatnya ke riwayat_perubahan.
     */
    public function update(ObservasiRequest $request, Observasi $observasi): RedirectResponse
    {
        $this->pastikanMilikGuru($request, $observasi);

        // Nilai sebelum diubah dipakai untuk menyusun keterangan riwayat.
        $sebelum = $this->nilaiPembanding($observasi);

        try {
            DB::transaction(function () use ($observasi, $request, $sebelum): void {
                $observasi->fill($request->dataObservasi());
                $observasi->save();

                $sesudah = $this->nilaiPembanding(
                    $observasi->fresh(['anak', 'aktivitas', 'strategi', 'aspek'])
                );

                $keterangan = $this->susunKeterangan($sebelum, $sesudah);

                // Riwayat hanya dicatat bila memang ada data yang berubah.
                if ($keterangan !== '') {
                    RiwayatPerubahan::create([
                        'dilakukan_oleh' => $request->user()->id,
                        'observasi_id' => $observasi->id,
                        // Mengikuti nilai aksi yang dipakai seeder: tambah/ubah/hapus.
                        'aksi' => 'ubah',
                        'keterangan' => $keterangan,
                    ]);
                }
            });
        } catch (\Throwable $e) {
            report($e);

            return back()
                ->withInput()
                ->with('gagal', 'Perubahan observasi gagal disimpan. Silakan coba kembali.');
        }

        return redirect()
            ->route('guru.observasi.show', $observasi)
            ->with('notifikasi', [
                'judul' => 'Observasi berhasil diperbarui',
                'pesan' => 'Perubahan tersimpan dan tercatat pada riwayat perubahan observasi.',
            ]);
    }

    /**
     * Menampilkan riwayat perubahan satu observasi.
     */
    public function riwayat(Request $request, Observasi $observasi): View
    {
        $this->pastikanMilikGuru($request, $observasi);

        return view('guru.observasi.riwayat', [
            'observasi' => $observasi->load(['anak.kelas', 'aktivitas', 'aspek']),
            'riwayat' => $this->riwayatObservasi($observasi),
        ]);
    }

    /**
     * Menghapus observasi beserta riwayat perubahannya.
     *
     * Kolom riwayat_perubahan.observasi_id memakai restrictOnDelete, sehingga
     * riwayat dihapus lebih dulu di dalam satu transaksi agar observasi dapat
     * dihapus tanpa meninggalkan baris yatim.
     */
    public function destroy(Request $request, Observasi $observasi): RedirectResponse
    {
        $this->pastikanMilikGuru($request, $observasi);

        try {
            DB::transaction(function () use ($observasi): void {
                $observasi->riwayatPerubahan()->delete();
                $observasi->delete();
            });
        } catch (\Throwable $e) {
            report($e);

            return redirect()
                ->route('guru.observasi.show', $observasi)
                ->with('gagal', 'Observasi gagal dihapus. Silakan coba kembali.');
        }

        return redirect()
            ->route('guru.observasi.index')
            ->with('notifikasi', [
                'judul' => 'Observasi berhasil dihapus',
                'pesan' => 'Catatan observasi beserta riwayat perubahannya telah dihapus.',
            ]);
    }

    /**
     * NFR-04: guru hanya boleh mengakses observasi miliknya sendiri.
     * Observasi milik guru lain dianggap tidak ada (404).
     */
    private function pastikanMilikGuru(Request $request, Observasi $observasi): void
    {
        abort_unless(
            (int) $observasi->guru_id === (int) $request->user()->id,
            404
        );
    }

    /**
     * Riwayat perubahan terbaru lebih dulu.
     */
    private function riwayatObservasi(Observasi $observasi): Collection
    {
        return $observasi->riwayatPerubahan()
            ->with('pengguna')
            ->orderByDesc('waktu')
            ->orderByDesc('id')
            ->get();
    }

    /**
     * Nilai observasi yang dipakai sebagai pembanding pada riwayat perubahan.
     *
     * @return array<string, string>
     */
    private function nilaiPembanding(Observasi $observasi): array
    {
        return [
            'Anak' => $observasi->anak?->nama ?? '-',
            'Tanggal observasi' => $observasi->tanggal?->locale('id')->isoFormat('D MMMM YYYY') ?? '-',
            'Aktivitas' => $observasi->aktivitas?->nama ?? '-',
            'Strategi pembelajaran' => $observasi->strategi?->nama ?? '-',
            'Aspek perkembangan' => $observasi->aspek?->nama ?? '-',
            'Respons anak' => $observasi->kategori_respons?->label() ?? '-',
            'Catatan pendukung' => $observasi->catatan_pendukung ?: '(kosong)',
        ];
    }

    /**
     * Menyusun keterangan riwayat dengan format "nama_field: sebelum -> sesudah".
     *
     * @param  array<string, string>  $sebelum
     * @param  array<string, string>  $sesudah
     */
    private function susunKeterangan(array $sebelum, array $sesudah): string
    {
        $baris = [];

        foreach ($sebelum as $field => $nilai) {
            if ($nilai === ($sesudah[$field] ?? null)) {
                continue;
            }

            $baris[] = $field.': '.$nilai.' -> '.($sesudah[$field] ?? '-');
        }

        return implode("\n", $baris);
    }

    /**
     * Menyiapkan data untuk formulir tambah/ubah observasi.
     */
    private function dataFormulir(?Observasi $observasi = null): array
    {
        // Mengambil kelas yang menjadi tanggung jawab guru login.
        $kelasList = DB::table('kelas')
            ->where('guru_id', auth()->id())
            ->select('id', 'nama_kelas as nama')
            ->orderBy('nama_kelas')
            ->get();

        $kelasIds = $kelasList->pluck('id');

        // Anak aktif dari kelas milik guru. Saat mengubah, anak yang sedang
        // diobservasi tetap ditampilkan meskipun statusnya sudah nonaktif.
        $anakList = Anak::query()
            ->whereIn('kelas_id', $kelasIds)
            ->where(fn ($q) => $q
                ->where('aktif', true)
                ->orWhere('id', $observasi?->anak_id))
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
