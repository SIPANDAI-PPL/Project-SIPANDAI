<?php

namespace App\Http\Controllers\DataMaster;

use App\Http\Controllers\Controller;
use App\Models\AspekPerkembangan;
use Closure;
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

    public function store(Request $request): RedirectResponse
    {
        $aspek = AspekPerkembangan::create($this->validasi($request));

        return redirect()
            ->route('data-master.aspek.index')
            ->with('status', "Aspek {$aspek->nama} berhasil ditambahkan.");
    }

    public function update(Request $request, AspekPerkembangan $aspek): RedirectResponse
    {
        $aspek->update($this->validasi($request, $aspek));

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

    /**
     * Validasi isian tambah dan ubah aspek.
     * Saat mengubah, aspek yang sedang diubah diabaikan pada cek nama unik.
     *
     * @return array<string, mixed>
     */
    private function validasi(Request $request, ?AspekPerkembangan $aspek = null): array
    {
        $rapikan = fn (string $kolom) => is_string($request->input($kolom)) ? trim($request->input($kolom)) : $request->input($kolom);

        $request->merge([
            'nama' => $rapikan('nama'),
            'deskripsi' => $rapikan('deskripsi'),
        ]);

        return $request->validate([
            'nama' => [
                'bail',
                'required',
                'string',
                'max:100',
                // Nama unik tanpa membedakan huruf besar/kecil ("Bahasa" sama dengan "bahasa").
                function (string $attribute, mixed $value, Closure $fail) use ($aspek) {
                    $sudahAda = AspekPerkembangan::query()
                        ->whereRaw('LOWER(nama) = ?', [mb_strtolower($value)])
                        ->when($aspek?->getKey(), fn ($query, $id) => $query->whereKeyNot($id))
                        ->exists();

                    if ($sudahAda) {
                        $fail('Nama aspek sudah terdaftar.');
                    }
                },
            ],
            'deskripsi' => ['nullable', 'string', 'max:1000'],
        ], [
            'nama.required' => 'Nama wajib diisi.',
            'nama.string' => 'Nama tidak valid.',
            'nama.max' => 'Nama maksimal 100 karakter.',
            'deskripsi.string' => 'Deskripsi tidak valid.',
            'deskripsi.max' => 'Deskripsi maksimal 1000 karakter.',
        ]);
    }
}
