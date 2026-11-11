<?php

use App\Http\Middleware\KeluarkanAkunNonaktif;
use App\Http\Middleware\PastikanPeran;
use App\Http\Middleware\WajibGantiKataSandi;
use Illuminate\Foundation\Application;
use Illuminate\Foundation\Configuration\Exceptions;
use Illuminate\Foundation\Configuration\Middleware;

return Application::configure(basePath: dirname(__DIR__))
    ->withRouting(
        web: __DIR__.'/../routes/web.php',
        commands: __DIR__.'/../routes/console.php',
        health: '/up',
    )
    ->withMiddleware(function (Middleware $middleware): void {
        $middleware->alias([
            'peran' => PastikanPeran::class,
        ]);

        // Urutan penting: akun nonaktif dikeluarkan dulu, baru cek wajib ganti sandi.
        $middleware->web(append: [
            KeluarkanAkunNonaktif::class,
            WajibGantiKataSandi::class,
        ]);
    })
    ->withExceptions(function (Exceptions $exceptions): void {
        //
    })->create();
