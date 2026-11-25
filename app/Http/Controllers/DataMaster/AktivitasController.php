<?php

namespace App\Http\Controllers\DataMaster;

use App\Http\Controllers\Controller;
use App\Http\Controllers\DataMaster\Concerns\FilterDataMaster;
use App\Models\Aktivitas;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
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

    public function store(Request $request): RedirectResponse
    {
        $aktivitas = Aktivitas::create([...$this->validasi($request), 'aktif' => true]);

        return $this->kembali("Aktivitas {$aktivitas->nama} berhasil ditambahkan.");
    }

    public function update(Request $request, Aktivitas $aktivitas): RedirectResponse
    {
        $aktivitas->update($this->validasi($request, $aktivitas));

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

    /**
     * Validasi isian tambah dan ubah aktivitas.
     * Keunikan nama tanpa beda huruf besar/kecil mengandalkan collation MySQL (utf8mb4_unicode_ci).
     *
     * @return array<string, mixed>
     */
    private function validasi(Request $request, ?Aktivitas $aktivitas = null): array
    {
        if (is_string($request->input('nama'))) {
            $request->merge(['nama' => trim($request->input('nama'))]);
        }

        return $request->validate([
            'nama' => [
                'required',
                'string',
                'max:100',
                Rule::unique('aktivitas', 'nama')->ignore($aktivitas),
            ],
            'deskripsi' => ['nullable', 'string', 'max:1000'],
        ], [
            'nama.required' => 'Nama wajib diisi.',
            'nama.string' => 'Nama wajib diisi.',
            'nama.max' => 'Nama maksimal 100 karakter.',
            'nama.unique' => 'Nama aktivitas sudah terdaftar.',
            'deskripsi.string' => 'Deskripsi harus berupa teks.',
            'deskripsi.max' => 'Deskripsi maksimal 1000 karakter.',
        ]);
    }

    private function kembali(string $pesan): RedirectResponse
    {
        return redirect()->route('data-master.aktivitas.index')->with('status', $pesan);
    }
}
