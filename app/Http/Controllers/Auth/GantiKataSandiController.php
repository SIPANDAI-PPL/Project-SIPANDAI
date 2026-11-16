<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Http\Requests\Auth\GantiKataSandiRequest;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
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

    public function update(GantiKataSandiRequest $request): RedirectResponse
    {
        $pengguna = $request->user();

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
