<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\SimpanKelasRequest;
use App\Http\Requests\Admin\TugaskanGuruPengampuRequest;
use App\Http\Requests\Admin\UbahKelasRequest;
use App\Models\Kelas;
use App\Models\User;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class KelasController extends Controller
{
    // Fitur: PBI-3 Kelola Kelas, Tahun Ajaran, dan Penugasan Guru Pengampu

    public function index(Request $request): View
    {
        $tahunTersimpan = Kelas::query()->distinct()->orderByDesc('tahun_ajaran')->pluck('tahun_ajaran');
        $tahunBerjalan = self::tahunAjaranBerjalan();

        // Tahun ajaran berjalan selalu tersedia walaupun belum ada kelas.
        $daftarTahunAjaran = $tahunTersimpan->push($tahunBerjalan)->unique()->sortDesc()->values();

        $tahunDipilih = $request->query('tahun_ajaran');
        $tahunAjaran = $daftarTahunAjaran->contains($tahunDipilih) ? $tahunDipilih : $tahunBerjalan;

        return view('admin.kelas.index', [
            'daftarKelas' => Kelas::query()
                ->with('guru:id,nama,email,aktif')
                ->where('tahun_ajaran', $tahunAjaran)
                ->orderBy('kelompok')
                ->orderBy('nama_kelas')
                ->get(),
            // Hanya guru aktif yang boleh dipilih sebagai pengampu.
            'daftarGuruAktif' => User::query()
                ->where('peran', 'guru')
                ->where('aktif', true)
                ->withCount('kelasDiampu')
                ->orderBy('nama')
                ->get(['id', 'nama', 'email']),
            'daftarTahunAjaran' => $daftarTahunAjaran,
            'daftarKelompok' => SimpanKelasRequest::KELOMPOK,
            'tahunAjaran' => $tahunAjaran,
        ]);
    }

    public function store(SimpanKelasRequest $request): RedirectResponse
    {
        $kelas = Kelas::create($request->validated());

        return $this->kembaliKeDaftar($kelas, "Kelas {$kelas->nama_kelas} berhasil disimpan.");
    }

    public function update(UbahKelasRequest $request, Kelas $kelas): RedirectResponse
    {
        $kelas->update($request->validated());

        return $this->kembaliKeDaftar($kelas, "Kelas {$kelas->nama_kelas} berhasil diperbarui.");
    }

    public function tugaskanGuru(TugaskanGuruPengampuRequest $request, Kelas $kelas): RedirectResponse
    {
        // Satu kelas satu guru pengampu: guru lama langsung diganti (tanpa tabel riwayat).
        $kelas->update(['guru_id' => $request->validated('guru_id')]);

        return $this->kembaliKeDaftar($kelas, "Guru pengampu {$kelas->nama_kelas} berhasil diperbarui.");
    }

    /**
     * Tahun ajaran baru dimulai bulan Juli, contoh November 2026 menjadi 2026/2027.
     */
    public static function tahunAjaranBerjalan(): string
    {
        $tahun = (int) now()->format('Y');
        $awal = (int) now()->format('n') >= 7 ? $tahun : $tahun - 1;

        return $awal.'/'.($awal + 1);
    }

    private function kembaliKeDaftar(Kelas $kelas, string $pesan): RedirectResponse
    {
        return redirect()
            ->route('classes.index', ['tahun_ajaran' => $kelas->tahun_ajaran])
            ->with('status', $pesan);
    }
}
