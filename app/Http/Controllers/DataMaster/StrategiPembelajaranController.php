<?php

namespace App\Http\Controllers\DataMaster;

use App\Http\Controllers\Controller;
use App\Http\Controllers\DataMaster\Concerns\FilterDataMaster;
use App\Http\Requests\DataMaster\SimpanStrategiPembelajaranRequest;
use App\Models\StrategiPembelajaran;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class StrategiPembelajaranController extends Controller
{
    // Fitur: PBI-2 Kelola Aktivitas dan Strategi Pembelajaran

    use FilterDataMaster;

    public function index(Request $request): View
    {
        $query = StrategiPembelajaran::query();
        $filter = $this->terapkanFilter($query, $request);

        $daftar = $query->withCount(StrategiPembelajaran::RELASI_PEMAKAIAN)
            ->orderBy('id')
            ->paginate(10)
            ->withQueryString();

        return view('data-master.index', [
            ...$filter,
            'jenis' => 'strategi',
            'daftar' => $daftar,
        ]);
    }

    public function store(SimpanStrategiPembelajaranRequest $request): RedirectResponse
    {
        $strategi = StrategiPembelajaran::create([...$request->validated(), 'aktif' => true]);

        return $this->kembali("Strategi {$strategi->nama} berhasil ditambahkan.");
    }

    public function update(SimpanStrategiPembelajaranRequest $request, StrategiPembelajaran $strategi): RedirectResponse
    {
        $strategi->update($request->validated());

        return $this->kembali("Strategi {$strategi->nama} berhasil diperbarui.");
    }

    public function nonaktifkan(StrategiPembelajaran $strategi): RedirectResponse
    {
        $strategi->update(['aktif' => false]);

        return $this->kembali("Strategi {$strategi->nama} dinonaktifkan.");
    }

    public function aktifkan(StrategiPembelajaran $strategi): RedirectResponse
    {
        $strategi->update(['aktif' => true]);

        return $this->kembali("Strategi {$strategi->nama} diaktifkan kembali.");
    }

    public function destroy(StrategiPembelajaran $strategi): RedirectResponse
    {
        // Dicek di server: strategi yang sudah dipakai observasi, aturan, atau alternatif hanya boleh dinonaktifkan.
        $strategi->loadCount(StrategiPembelajaran::RELASI_PEMAKAIAN);

        $pemakaian = match (true) {
            $strategi->observasi_count > 0 => "{$strategi->observasi_count} observasi",
            $strategi->jumlahAturan() > 0 => "{$strategi->jumlahAturan()} aturan strategi",
            $strategi->alternatif_strategi_count > 0 => "{$strategi->alternatif_strategi_count} alternatif strategi",
            default => null,
        };

        if ($pemakaian !== null) {
            return redirect()->route('data-master.strategi.index')->withErrors([
                'hapus' => "Data sudah dipakai pada {$pemakaian} dan hanya dapat dinonaktifkan.",
            ]);
        }

        $strategi->delete();

        return $this->kembali("Strategi {$strategi->nama} berhasil dihapus.");
    }

    private function kembali(string $pesan): RedirectResponse
    {
        return redirect()->route('data-master.strategi.index')->with('status', $pesan);
    }
}
