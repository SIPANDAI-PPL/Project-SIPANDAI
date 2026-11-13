<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\SimpanAkunGuruRequest;
use App\Http\Requests\Admin\UbahAkunGuruRequest;
use App\Models\Kelas;
use App\Models\User;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Illuminate\View\View;

class KelolaAkunGuruController extends Controller
{
    // Fitur: PBI-1 Kelola Akun Guru dan Reset Kata Sandi

    private const STATUS = ['semua', 'aktif', 'nonaktif'];

    public function index(Request $request): View
    {
        $cari = trim((string) $request->query('q', ''));
        $status = in_array($request->query('status'), self::STATUS, true) ? $request->query('status') : 'semua';

        $dasar = User::query()
            ->where('peran', 'guru')
            ->when($cari !== '', function (Builder $query) use ($cari) {
                $query->where(function (Builder $query) use ($cari) {
                    $pola = '%'.addcslashes($cari, '%_\\').'%';
                    $query->where('nama', 'like', $pola)
                        ->orWhere('email', 'like', $pola)
                        ->orWhere('nip', 'like', $pola);
                });
            });

        $jumlah = [
            'semua' => (clone $dasar)->count(),
            'aktif' => (clone $dasar)->where('aktif', true)->count(),
            'nonaktif' => (clone $dasar)->where('aktif', false)->count(),
        ];

        $daftarGuru = (clone $dasar)
            ->when($status !== 'semua', fn (Builder $query) => $query->where('aktif', $status === 'aktif'))
            ->with(['kelasDiampu' => fn ($query) => $query->orderBy('kelompok')->orderBy('nama_kelas')])
            ->orderBy('nama')
            ->paginate(10)
            ->withQueryString();

        return view('admin.akun-guru.index', [
            'daftarGuru' => $daftarGuru,
            'jumlah' => $jumlah,
            'cari' => $cari,
            'status' => $status,
        ]);
    }

    public function store(SimpanAkunGuruRequest $request): RedirectResponse
    {
        $kataSandi = Str::password(12);

        $guru = User::create([
            ...$this->dataAkun($request),
            'kata_sandi' => $kataSandi,
            'peran' => 'guru',
            'aktif' => true,
            'wajib_ganti_sandi' => true,
        ]);

        return redirect()
            ->route('admin.guru.index')
            ->with('sandi_sementara', $this->dataSandiSementara($guru, $kataSandi, 'Akun guru berhasil dibuat'));
    }

    public function update(UbahAkunGuruRequest $request, User $guru): RedirectResponse
    {
        $guru->update($this->dataAkun($request));

        return redirect()
            ->route('admin.guru.index')
            ->with('status', "Data akun {$guru->nama} berhasil diperbarui.");
    }

    public function nonaktifkan(User $guru): RedirectResponse
    {
        $kelas = $guru->kelasDiampu()->orderBy('kelompok')->orderBy('nama_kelas')->get();

        // Aturan bisnis: guru yang masih mengampu kelas tidak boleh dinonaktifkan.
        if ($kelas->isNotEmpty()) {
            return redirect()
                ->route('admin.guru.index')
                ->withErrors([
                    'nonaktifkan' => "Guru masih mengampu {$kelas->count()} kelas ({$this->labelKelas($kelas)}). Alihkan kelasnya ke guru lain dulu.",
                ]);
        }

        $guru->update(['aktif' => false]);

        return redirect()
            ->route('admin.guru.index')
            ->with('status', "Akun {$guru->nama} berhasil dinonaktifkan.");
    }

    public function aktifkan(User $guru): RedirectResponse
    {
        $guru->update(['aktif' => true]);

        return redirect()
            ->route('admin.guru.index')
            ->with('status', "Akun {$guru->nama} berhasil diaktifkan kembali.");
    }

    public function resetKataSandi(User $guru): RedirectResponse
    {
        $kataSandi = Str::password(12);

        $guru->update([
            'kata_sandi' => $kataSandi,
            'wajib_ganti_sandi' => true,
            'remember_token' => Str::random(60),
        ]);

        return redirect()
            ->route('admin.guru.index')
            ->with('sandi_sementara', $this->dataSandiSementara($guru, $kataSandi, 'Kata sandi berhasil direset'));
    }

    /**
     * @return array<string, string|null>
     */
    private function dataAkun(SimpanAkunGuruRequest $request): array
    {
        $data = $request->safe()->only(['nama', 'nama_pengguna', 'email', 'nip']);
        $data['nip'] = filled($data['nip'] ?? null) ? $data['nip'] : null;

        return $data;
    }

    /**
     * Teks kata sandi hanya dikirim lewat flash session agar tampil satu kali.
     *
     * @return array<string, string>
     */
    private function dataSandiSementara(User $guru, string $kataSandi, string $judul): array
    {
        return [
            'judul' => $judul,
            'nama' => $guru->nama,
            'email' => $guru->email,
            'kelas' => $this->labelKelas($guru->kelasDiampu()->orderBy('kelompok')->orderBy('nama_kelas')->get()),
            'kata_sandi' => $kataSandi,
        ];
    }

    /**
     * @param  iterable<Kelas>  $kelas
     */
    private function labelKelas(iterable $kelas): string
    {
        $label = collect($kelas)->map(fn (Kelas $item) => "{$item->kelompok} - {$item->nama_kelas}")->implode(', ');

        return $label !== '' ? $label : 'Tanpa Kelas';
    }
}
