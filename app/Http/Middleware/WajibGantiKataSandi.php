<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class WajibGantiKataSandi
{
    // Fitur: PBI-1 Kelola Akun Guru dan Reset Kata Sandi

    private const RUTE_DIIZINKAN = ['kata-sandi.edit', 'kata-sandi.update', 'logout'];

    public function handle(Request $request, Closure $next): Response
    {
        $pengguna = $request->user();

        if ($pengguna?->wajib_ganti_sandi && ! $request->routeIs(...self::RUTE_DIIZINKAN)) {
            return redirect()->route('kata-sandi.edit');
        }

        return $next($request);
    }
}
