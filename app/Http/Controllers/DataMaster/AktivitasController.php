<?php

namespace App\Http\Controllers\DataMaster;

use App\Http\Controllers\Controller;
use App\Http\Controllers\DataMaster\Concerns\FilterDataMaster;
use App\Http\Requests\DataMaster\SimpanAktivitasRequest;
use App\Models\Aktivitas;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class AktivitasController extends Controller
{
    // Fitur: PBI-2 Kelola Aktivitas dan Strategi Pembelajaran

    use FilterDataMaster;

    public function index(Request $request): View
    {
        $query = Aktivitas::query();
        $filter = $this->terapkanFilter($query, $request);

        $daftar = $query->withCount('observasi')
            ->orderBy('id')
            ->paginate(10)
            ->withQueryString();

        return view('data-master.index', [
            ...$filter,
            'jenis' => 'aktivitas',
            'daftar' => $daftar,
        ]);
    }

    public function store(SimpanAktivitasRequest $request): RedirectResponse
    {
        $aktivitas = Aktivitas::create([...$request->validated(), 'aktif' => true]);

        return $this->kembali("Aktivitas {$aktivitas->nama} berhasil ditambahkan.");
    }

    public function update(SimpanAktivitasRequest $request, Aktivitas $aktivitas): RedirectResponse
    {
        $aktivitas->update($request->validated());

        return $this->kembali("Aktivitas {$aktivitas->nama} berhasil diperbarui.");
    }

    public function nonaktifkan(Aktivitas $aktivitas): RedirectResponse
    {
        $aktivitas->update(['aktif' => false]);

        return $this->kembali("Aktivitas {$aktivitas->nama} dinonaktifkan.");
    }

    public function aktifkan(Aktivitas $aktivitas): RedirectResponse
    {
        $aktivitas->update(['aktif' => true]);

        return $this->kembali("Aktivitas {$aktivitas->nama} diaktifkan kembali.");
    }

    public function destroy(Aktivitas $aktivitas): RedirectResponse
    {
        // Dicek di server: aktivitas yang sudah dipakai observasi hanya boleh dinonaktifkan.
        $jumlahObservasi = $aktivitas->observasi()->count();

        if ($jumlahObservasi > 0) {
            return redirect()->route('data-master.aktivitas.index')->withErrors([
                'hapus' => "Data sudah dipakai pada {$jumlahObservasi} observasi dan hanya dapat dinonaktifkan.",
            ]);
        }

        $aktivitas->delete();

        return $this->kembali("Aktivitas {$aktivitas->nama} berhasil dihapus.");
    }

    private function kembali(string $pesan): RedirectResponse
    {
        return redirect()->route('data-master.aktivitas.index')->with('status', $pesan);
    }
}
