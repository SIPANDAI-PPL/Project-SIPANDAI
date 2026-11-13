<?php

namespace App\Http\Controllers\Admin;

use App\Enums\KategoriRespons;
use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\SimpanAturanStrategiRequest;
use App\Http\Requests\Admin\UbahAturanStrategiRequest;
use App\Models\AspekPerkembangan;
use App\Models\AturanStrategi;
use App\Models\StrategiPembelajaran;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class AturanStrategiController extends Controller
{
    // Fitur: PBI-4 Kelola Aspek Perkembangan dan Aturan Alternatif Strategi

    private const STATUS = ['semua', 'aktif', 'nonaktif'];

    public function index(Request $request): View
    {
        $cari = trim((string) $request->query('q', ''));
        $status = in_array($request->query('status'), self::STATUS, true) ? $request->query('status') : 'semua';
        $aspek = ctype_digit((string) $request->query('aspek', '')) ? (int) $request->query('aspek') : null;

        $daftarAturan = AturanStrategi::query()
            ->with(['aspek', 'strategiAsal', 'strategiAlternatif'])
            ->withCount('alternatifStrategi')
            ->when($aspek !== null, fn (Builder $query) => $query->where('aspek_id', $aspek))
            ->when($status !== 'semua', fn (Builder $query) => $query->where('aktif', $status === 'aktif'))
            ->when($cari !== '', function (Builder $query) use ($cari) {
                $pola = '%'.addcslashes($cari, '%_\\').'%';
                $query->where(function (Builder $query) use ($pola) {
                    $query->whereHas('strategiAsal', fn (Builder $query) => $query->where('nama', 'like', $pola))
                        ->orWhereHas('strategiAlternatif', fn (Builder $query) => $query->where('nama', 'like', $pola));
                });
            })
            ->orderBy('id')
            ->paginate(10)
            ->withQueryString();

        return view('admin.aturan-strategi.index', [
            'daftarAturan' => $daftarAturan,
            // Hanya strategi aktif yang boleh dipilih saat menyusun aturan.
            'pilihanStrategi' => StrategiPembelajaran::query()->where('aktif', true)->orderBy('nama')->get(['id', 'nama']),
            'pilihanAspek' => AspekPerkembangan::query()->orderBy('id')->get(['id', 'nama']),
            'pilihanRespons' => KategoriRespons::cases(),
            'cari' => $cari,
            'status' => $status,
            'aspek' => $aspek,
        ]);
    }

    public function store(SimpanAturanStrategiRequest $request): RedirectResponse
    {
        AturanStrategi::create($request->validated());

        return redirect()
            ->route('admin.aturan-strategi.index')
            ->with('status', 'Aturan strategi berhasil ditambahkan.');
    }

    public function update(UbahAturanStrategiRequest $request, AturanStrategi $aturan): RedirectResponse
    {
        $aturan->update($request->validated());

        return redirect()
            ->route('admin.aturan-strategi.index')
            ->with('status', 'Aturan strategi berhasil diperbarui.');
    }

    public function nonaktifkan(AturanStrategi $aturan): RedirectResponse
    {
        $aturan->update(['aktif' => false]);

        return redirect()
            ->route('admin.aturan-strategi.index')
            ->with('status', 'Aturan strategi berhasil dinonaktifkan.');
    }

    public function aktifkan(AturanStrategi $aturan): RedirectResponse
    {
        $aturan->update(['aktif' => true]);

        return redirect()
            ->route('admin.aturan-strategi.index')
            ->with('status', 'Aturan strategi berhasil diaktifkan kembali.');
    }

    public function destroy(AturanStrategi $aturan): RedirectResponse
    {
        $jumlahSaran = $aturan->alternatifStrategi()->count();

        // Aturan yang sudah menghasilkan saran tetap disimpan sebagai dasar riwayat saran.
        if ($jumlahSaran > 0) {
            return redirect()
                ->route('admin.aturan-strategi.index')
                ->withErrors([
                    'hapus' => "Aturan sudah menghasilkan {$jumlahSaran} saran dan hanya dapat dinonaktifkan.",
                ]);
        }

        $aturan->delete();

        return redirect()
            ->route('admin.aturan-strategi.index')
            ->with('status', 'Aturan strategi berhasil dihapus.');
    }
}
