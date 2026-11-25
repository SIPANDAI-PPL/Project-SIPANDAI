<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use Closure;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\View\View;

class GantiKataSandiController extends Controller
{
    // Fitur: PBI-1 Kelola Akun Guru dan Reset Kata Sandi

    public function create(Request $request): View|RedirectResponse
    {
        $pengguna = $request->user();

        if (! $pengguna->wajib_ganti_sandi) {
            return redirect()->route($pengguna->ruteDashboard());
        }

        return view('auth.ganti-kata-sandi');
    }

    public function update(Request $request): RedirectResponse
    {
        $pengguna = $request->user();

        // Halaman ini hanya untuk mengganti kata sandi sementara.
        abort_unless($pengguna->wajib_ganti_sandi, 403);

        $request->validate([
            'kata_sandi_baru' => [
                'required',
                'string',
                'min:8',
                'confirmed',
                function (string $attribute, mixed $value, Closure $fail) use ($pengguna): void {
                    if (Hash::check((string) $value, $pengguna->getAuthPassword())) {
                        $fail('Kata sandi baru harus berbeda dari kata sandi sementara.');
                    }
                },
            ],
        ], [
            'kata_sandi_baru.required' => 'Kata sandi baru wajib diisi.',
            'kata_sandi_baru.string' => 'Kata sandi baru tidak valid.',
            'kata_sandi_baru.min' => 'Kata sandi baru minimal 8 karakter.',
            'kata_sandi_baru.confirmed' => 'Konfirmasi kata sandi baru tidak cocok.',
        ]);

        $pengguna->update([
            'kata_sandi' => $request->input('kata_sandi_baru'),
            'wajib_ganti_sandi' => false,
        ]);

        $request->session()->regenerate();

        return redirect()
            ->route($pengguna->ruteDashboard())
            ->with('status', 'Kata sandi berhasil diganti.');
    }
}
