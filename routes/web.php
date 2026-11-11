<?php

use App\Http\Controllers\Auth\LoginController;
use App\Http\Controllers\DataMaster\AktivitasController;
use App\Http\Controllers\DataMaster\StrategiPembelajaranController;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

Route::get('/', function () {
    return view('welcome');
});

Route::middleware('guest')->group(function () {
    Route::get('/login', [LoginController::class, 'create'])->name('login');
    Route::post('/login', [LoginController::class, 'store']);
});

Route::middleware('auth')->group(function () {
    Route::post('/logout', [LoginController::class, 'destroy'])->name('logout');

    Route::get('/dashboard', function (Request $request) {
        return redirect()->route($request->user()->ruteDashboard());
    })->name('dashboard');

    Route::view('/admin/dashboard', 'admin.dashboard')
        ->middleware('peran:admin')
        ->name('admin.dashboard');

    Route::view('/guru/dashboard', 'guru.dashboard')
        ->middleware('peran:guru')
        ->name('guru.dashboard');

    // Pratinjau frontend PBI-11. Ganti dengan controller RiwayatObservasiController saat backend siap.
    Route::view('/guru/riwayat-observasi', 'guru.riwayat_observasi')
        ->middleware('peran:guru')
        ->name('guru.riwayat-observasi');

    // PBI-2 Kelola Aktivitas dan Strategi Pembelajaran (admin dan guru).
    Route::middleware('peran:admin,guru')
        ->prefix('data-master')
        ->name('data-master.')
        ->group(function () {
            Route::get('/aktivitas', [AktivitasController::class, 'index'])->name('aktivitas.index');
            Route::post('/aktivitas', [AktivitasController::class, 'store'])->name('aktivitas.store');
            Route::put('/aktivitas/{aktivitas}', [AktivitasController::class, 'update'])->name('aktivitas.update');
            Route::patch('/aktivitas/{aktivitas}/nonaktifkan', [AktivitasController::class, 'nonaktifkan'])->name('aktivitas.nonaktifkan');
            Route::patch('/aktivitas/{aktivitas}/aktifkan', [AktivitasController::class, 'aktifkan'])->name('aktivitas.aktifkan');
            Route::delete('/aktivitas/{aktivitas}', [AktivitasController::class, 'destroy'])->name('aktivitas.destroy');

            Route::get('/strategi', [StrategiPembelajaranController::class, 'index'])->name('strategi.index');
            Route::post('/strategi', [StrategiPembelajaranController::class, 'store'])->name('strategi.store');
            Route::put('/strategi/{strategi}', [StrategiPembelajaranController::class, 'update'])->name('strategi.update');
            Route::patch('/strategi/{strategi}/nonaktifkan', [StrategiPembelajaranController::class, 'nonaktifkan'])->name('strategi.nonaktifkan');
            Route::patch('/strategi/{strategi}/aktifkan', [StrategiPembelajaranController::class, 'aktifkan'])->name('strategi.aktifkan');
            Route::delete('/strategi/{strategi}', [StrategiPembelajaranController::class, 'destroy'])->name('strategi.destroy');
        });
});
