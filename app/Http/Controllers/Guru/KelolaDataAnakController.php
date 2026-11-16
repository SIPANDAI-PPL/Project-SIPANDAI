<?php

namespace App\Http\Controllers\Guru;

use App\Enums\KategoriRespons;
use App\Http\Controllers\Controller;
use App\Http\Requests\Guru\SimpanAnakRequest;
use App\Http\Requests\Guru\UbahAnakRequest;
use App\Models\Anak;
use App\Models\Kelas;
use App\Models\Observasi;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Support\Carbon;
use Illuminate\Support\Collection;
use Illuminate\View\View;

class KelolaDataAnakController extends Controller
{
    // Fitur: PBI-5 Kelola Data Dasar Anak

    public function index(Request $request): View
    {
        $daftarKelas = $this->kelasDiampu($request);

        // NFR-04: kelas dari URL hanya dipakai jika diampu guru ini.
        $kelasTerpilih = $daftarKelas->firstWhere('id', (int) $request->query('kelas'))
            ?? $daftarKelas->first();

        $cari = trim((string) $request->query('cari', ''));
        $status = $request->query('status') === 'nonaktif' ? 'nonaktif' : 'aktif';

        $anakKelas = $kelasTerpilih === null
            ? collect()
            : $this->queryAnak()
                ->where('kelas_id', $kelasTerpilih->id)
                ->orderBy('nama')
                ->get()
                ->map(fn (Anak $anak) => $this->ringkasAnak($anak));

        // Jumlah pada tab adalah per status pada kelas terpilih (tidak ikut pencarian).
        $jumlahAktif = $anakKelas->where('aktif', true)->count();
        $jumlahNonaktif = $anakKelas->where('aktif', false)->count();

        $tampil = $cari === ''
            ? $anakKelas
            : $anakKelas->filter(fn (array $a) => mb_stripos($a['nama'], $cari) !== false
                || mb_stripos((string) $a['nama_panggilan'], $cari) !== false);

        return view('guru.anak.index', [
            'daftarKelas' => $daftarKelas->map(fn (Kelas $k) => $this->ringkasKelas($k))->all(),
            'kelasTerpilih' => $kelasTerpilih ? $this->ringkasKelas($kelasTerpilih) : null,
            'cari' => $cari,
            'jumlahAktif' => $jumlahAktif,
            'jumlahNonaktif' => $jumlahNonaktif,
            'status' => $status,
            'anakTampil' => $tampil->where('aktif', $status === 'aktif')->values()->all(),
        ]);
    }

    public function store(SimpanAnakRequest $request): RedirectResponse
    {
        $anak = Anak::create($request->dataAnak() + ['aktif' => true]);

        return redirect()
            ->route('guru.anak.index', ['kelas' => $anak->kelas_id])
            ->with('status', 'Data anak berhasil ditambahkan.');
    }

    public function show(Request $request, Anak $anak): View|Response
    {
        if (! $this->diampu($request, $anak)) {
            return response()->view('guru.akses-ditolak', [], 403);
        }

        $dataAnak = $this->queryAnak()->findOrFail($anak->id);

        return view('guru.anak.show', [
            'anak' => $this->ringkasAnak($dataAnak),
            'daftarKelas' => $this->kelasDiampu($request)->map(fn (Kelas $k) => $this->ringkasKelas($k))->all(),
        ]);
    }

    public function update(UbahAnakRequest $request, Anak $anak): RedirectResponse
    {
        $anak->update($request->dataAnak());

        return redirect()
            ->route('guru.anak.show', $anak)
            ->with('status', 'Data anak berhasil diubah.');
    }

    public function nonaktifkan(Request $request, Anak $anak): RedirectResponse
    {
        abort_unless($this->diampu($request, $anak), 403, 'Akses ditolak.');

        // Data tidak dihapus permanen; cukup ditandai nonaktif.
        $anak->ubahStatusAktif(false);

        return redirect()
            ->route('guru.anak.show', $anak)
            ->with('status', 'Anak berhasil dinonaktifkan.');
    }

    public function aktifkan(Request $request, Anak $anak): RedirectResponse
    {
        abort_unless($this->diampu($request, $anak), 403, 'Akses ditolak.');

        $anak->ubahStatusAktif(true);

        return redirect()
            ->route('guru.anak.show', $anak)
            ->with('status', 'Anak berhasil diaktifkan kembali.');
    }

    /**
     * @return Collection<int, Kelas>
     */
    private function kelasDiampu(Request $request): Collection
    {
        return Kelas::query()
            ->where('guru_id', $request->user()->id)
            ->withCount('anak')
            ->orderByDesc('tahun_ajaran')
            ->orderBy('nama_kelas')
            ->get();
    }

    /** NFR-04: aturan akses ada di App\Policies\AnakPolicy. */
    private function diampu(Request $request, Anak $anak): bool
    {
        return $request->user()->can('kelola', $anak);
    }

    /**
     * Anak beserta kelas, jumlah observasi, dan observasi terakhirnya.
     */
    private function queryAnak(): Builder
    {
        $terakhir = fn (string $kolom) => Observasi::query()
            ->select($kolom)
            ->whereColumn('observasi.anak_id', 'anak.id')
            ->orderByDesc('tanggal')
            ->orderByDesc('id')
            ->limit(1);

        return Anak::query()
            ->select('anak.*')
            ->with('kelas')
            ->withCount('observasi')
            ->addSelect([
                'tanggal_observasi_terakhir' => $terakhir('tanggal'),
                'kategori_observasi_terakhir' => $terakhir('kategori_respons'),
            ]);
    }

    /**
     * @return array{id: int, label: string, nama: string}
     */
    private function ringkasKelas(Kelas $kelas): array
    {
        $nama = "{$kelas->nama_kelas} · {$kelas->tahun_ajaran}";

        return [
            'id' => $kelas->id,
            'nama' => $nama,
            'label' => isset($kelas->anak_count) ? "{$nama} ({$kelas->anak_count} anak)" : $nama,
        ];
    }

    /**
     * Bentuk data yang dipakai view daftar dan profil anak.
     *
     * @return array<string, mixed>
     */
    private function ringkasAnak(Anak $anak): array
    {
        $hariIni = Carbon::today();
        $usiaBulan = max(0, Anak::hitungUsiaBulan($anak->tanggal_lahir, $hariIni));

        $tanggalTerakhir = $anak->tanggal_observasi_terakhir
            ? Carbon::parse($anak->tanggal_observasi_terakhir)->startOfDay()
            : null;

        $kategori = $anak->kategori_observasi_terakhir
            ? KategoriRespons::tryFrom($anak->kategori_observasi_terakhir)
            : null;

        return [
            'id' => $anak->id,
            'nama' => $anak->nama,
            'panggilan' => $anak->nama_panggilan ?: $anak->nama,
            'nama_panggilan' => $anak->nama_panggilan,
            'inisial' => $this->inisial($anak->nama),
            'usia' => intdiv($usiaBulan, 12).' th '.($usiaBulan % 12).' bln',
            'tanggal_lahir' => $anak->tanggal_lahir->format('Y-m-d'),
            'kelas_id' => $anak->kelas_id,
            'kelas' => $anak->kelas ? $this->ringkasKelas($anak->kelas)['nama'] : '-',
            'jumlah_observasi' => (int) $anak->observasi_count,
            'kategori' => $kategori?->label(),
            'waktu' => $tanggalTerakhir ? $this->waktuRelatif($tanggalTerakhir, $hariIni) : null,
            'aktif' => (bool) $anak->aktif,
        ];
    }

    private function inisial(string $nama): string
    {
        $kata = preg_split('/\s+/', trim($nama), -1, PREG_SPLIT_NO_EMPTY) ?: ['?'];

        return mb_strtoupper(
            mb_substr($kata[0], 0, 1).(isset($kata[1]) ? mb_substr($kata[1], 0, 1) : '')
        );
    }

    private function waktuRelatif(Carbon $tanggal, Carbon $hariIni): string
    {
        $selisih = (int) $tanggal->diffInDays($hariIni, false);

        return match (true) {
            $selisih <= 0 => 'Hari ini',
            $selisih === 1 => 'Kemarin',
            default => $selisih.' hari',
        };
    }
}
