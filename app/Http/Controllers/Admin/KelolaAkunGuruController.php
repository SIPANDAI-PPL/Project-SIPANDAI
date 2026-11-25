<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Kelas;
use App\Models\User;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;
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

    public function store(Request $request): RedirectResponse
    {
        $data = $this->validasiAkun($request);
        $kataSandi = Str::password(12);

        $guru = User::create([
            ...$data,
            'kata_sandi' => $kataSandi,
            'peran' => 'guru',
            'aktif' => true,
            'wajib_ganti_sandi' => true,
        ]);

        return redirect()
            ->route('admin.guru.index')
            ->with('sandi_sementara', $this->dataSandiSementara($guru, $kataSandi, 'Akun guru berhasil dibuat'));
    }

    public function update(Request $request, User $guru): RedirectResponse
    {
        $guru->update($this->validasiAkun($request, $guru));

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
     * Validasi isian akun guru untuk tambah dan ubah.
     * Saat mengubah, akun yang sedang diubah diabaikan pada cek unik.
     *
     * @return array<string, string|null>
     */
    private function validasiAkun(Request $request, ?User $guru = null): array
    {
        $data = $request->validate([
            'nama' => ['required', 'string', 'max:100'],
            'nama_pengguna' => [
                'required',
                'string',
                'max:50',
                'regex:/^[A-Za-z0-9._-]+$/',
                Rule::unique('users', 'nama_pengguna')->ignore($guru?->id),
            ],
            'email' => [
                'required',
                'string',
                'email',
                'max:100',
                Rule::unique('users', 'email')->ignore($guru?->id),
            ],
            'nip' => ['nullable', 'string', 'max:18'],
        ], [
            'nama.required' => 'Nama lengkap wajib diisi.',
            'nama.max' => 'Nama lengkap maksimal 100 karakter.',
            'nama_pengguna.required' => 'Nama pengguna wajib diisi.',
            'nama_pengguna.max' => 'Nama pengguna maksimal 50 karakter.',
            'nama_pengguna.regex' => 'Nama pengguna hanya boleh berisi huruf, angka, titik, garis bawah, atau tanda hubung.',
            'nama_pengguna.unique' => 'Nama pengguna sudah terdaftar.',
            'email.required' => 'Email wajib diisi.',
            'email.email' => 'Format email tidak valid.',
            'email.max' => 'Email maksimal 100 karakter.',
            'email.unique' => 'Email sudah terdaftar.',
            'nip.max' => 'NIP maksimal 18 karakter.',
            '*.string' => 'Isian tidak valid.',
        ]);

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
