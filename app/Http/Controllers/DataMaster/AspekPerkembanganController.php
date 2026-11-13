<?php

namespace App\Http\Controllers\DataMaster;

use App\Http\Controllers\Controller;
use App\Http\Requests\DataMaster\SimpanAspekPerkembanganRequest;
use App\Http\Requests\DataMaster\UbahAspekPerkembanganRequest;
use App\Models\AspekPerkembangan;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class AspekPerkembanganController extends Controller
{
    // Fitur: PBI-4 Kelola Aspek Perkembangan dan Aturan Alternatif Strategi

    public function index(Request $request): View
    {
        $cari = trim((string) $request->query('q', ''));

        $daftarAspek = AspekPerkembangan::query()
            ->withCount(['observasi', 'aturanStrategi'])
            ->when($cari !== '', function (Builder $query) use ($cari) {
                $pola = '%'.addcslashes($cari, '%_\\').'%';
                $query->where(function (Builder $query) use ($pola) {
                    $query->where('nama', 'like', $pola)
                        ->orWhere('deskripsi', 'like', $pola);
                });
            })
            ->orderBy('id')
            ->paginate(10)
            ->withQueryString();

        return view('data-master.aspek.index', [
            'daftarAspek' => $daftarAspek,
            'cari' => $cari,
        ]);
    }

    public function store(SimpanAspekPerkembanganRequest $request): RedirectResponse
    {
        $aspek = AspekPerkembangan::create($request->validated());

        return redirect()
            ->route('data-master.aspek.index')
            ->with('status', "Aspek {$aspek->nama} berhasil ditambahkan.");
    }

    public function update(UbahAspekPerkembanganRequest $request, AspekPerkembangan $aspek): RedirectResponse
    {
        $aspek->update($request->validated());

        return redirect()
            ->route('data-master.aspek.index')
            ->with('status', "Aspek {$aspek->nama} berhasil diperbarui.");
    }

    public function destroy(AspekPerkembangan $aspek): RedirectResponse
    {
        $jumlahObservasi = $aspek->observasi()->count();
        $jumlahAturan = $aspek->aturanStrategi()->count();

        // Aspek yang sudah dipakai tidak boleh dihapus agar riwayat observasi dan aturan tetap utuh.
        if ($jumlahObservasi > 0 || $jumlahAturan > 0) {
            return redirect()
                ->route('data-master.aspek.index')
                ->withErrors([
                    'hapus' => "Aspek sudah dipakai pada {$jumlahObservasi} observasi dan {$jumlahAturan} aturan sehingga tidak dapat dihapus.",
                ]);
        }

        $aspek->delete();

        return redirect()
            ->route('data-master.aspek.index')
            ->with('status', "Aspek {$aspek->nama} berhasil dihapus.");
    }
}
