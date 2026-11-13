<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Http\Requests\Auth\LoginRequest;
use App\Models\User;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\RateLimiter;
use Illuminate\Support\Str;
use Illuminate\Validation\ValidationException;
use Illuminate\View\View;

class LoginController extends Controller
{
    // Fitur: Login dan Logout (Sprint 0)

    private const MAKS_PERCOBAAN = 5;

    public function create(): View
    {
        return view('auth.login');
    }

    public function store(LoginRequest $request): RedirectResponse
    {
        $login = $request->input('login');
        $kolom = filter_var($login, FILTER_VALIDATE_EMAIL) ? 'email' : 'nama_pengguna';
        $pengguna = User::where($kolom, $login)->first();

        // Percobaan dihitung per akun agar email dan nama pengguna memakai batas yang sama.
        $kunci = ($pengguna ? 'pengguna:'.$pengguna->id : 'login:'.Str::lower($login)).'|'.$request->ip();

        if (RateLimiter::tooManyAttempts($kunci, self::MAKS_PERCOBAAN)) {
            $this->tolak('Terlalu banyak percobaan. Coba lagi dalam '.RateLimiter::availableIn($kunci).' detik.');
        }

        // Kunci "password" wajib untuk Auth; nilainya dicocokkan dengan kolom kata_sandi.
        $kredensial = [
            $kolom => $login,
            'password' => $request->input('kata_sandi'),
        ];

        if (! Auth::validate($kredensial)) {
            RateLimiter::hit($kunci);
            $this->tolak('Email/nama pengguna atau kata sandi salah.');
        }

        // Status akun baru diperiksa setelah kata sandi benar agar statusnya tidak bocor.
        if (! $pengguna->aktif) {
            $this->tolak('Akun tidak aktif, hubungi admin.');
        }

        RateLimiter::clear($kunci);
        Auth::login($pengguna);
        $request->session()->regenerate();

        if ($pengguna->wajib_ganti_sandi) {
            return redirect()->route('kata-sandi.edit');
        }

        return redirect()->route($pengguna->ruteDashboard());
    }

    public function destroy(Request $request): RedirectResponse
    {
        Auth::logout();

        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return redirect()->route('login');
    }

    private function tolak(string $pesan): never
    {
        throw ValidationException::withMessages(['login' => $pesan]);
    }
}
