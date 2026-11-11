<?php

use App\Http\Controllers\Auth\LoginController;
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
});
