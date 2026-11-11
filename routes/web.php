<?php

use App\Http\Controllers\Admin\KelolaAkunGuruController;
use App\Http\Controllers\Auth\GantiKataSandiController;
use App\Http\Controllers\Auth\LoginController;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

// {guru} hanya cocok dengan akun berperan guru; akun admin menghasilkan 404.
Route::bind('guru', fn (string $nilai) => User::where('peran', 'guru')->findOrFail($nilai));

Route::get('/', function () {
    return view('welcome');
});

Route::middleware('guest')->group(function () {
    Route::get('/login', [LoginController::class, 'create'])->name('login');
    Route::post('/login', [LoginController::class, 'store']);
});

Route::middleware('auth')->group(function () {
    Route::post('/logout', [LoginController::class, 'destroy'])->name('logout');

    Route::get('/ganti-kata-sandi', [GantiKataSandiController::class, 'create'])->name('kata-sandi.edit');
    Route::put('/ganti-kata-sandi', [GantiKataSandiController::class, 'update'])->name('kata-sandi.update');

    Route::get('/dashboard', function (Request $request) {
        return redirect()->route($request->user()->ruteDashboard());
    })->name('dashboard');

    Route::view('/admin/dashboard', 'admin.dashboard')
        ->middleware('peran:admin')
        ->name('admin.dashboard');

    Route::middleware('peran:admin')
        ->prefix('admin/guru')
        ->name('admin.guru.')
        ->controller(KelolaAkunGuruController::class)
        ->group(function () {
            Route::get('/', 'index')->name('index');
            Route::post('/', 'store')->name('store');
            Route::put('/{guru}', 'update')->name('update');
            Route::patch('/{guru}/nonaktifkan', 'nonaktifkan')->name('nonaktifkan');
            Route::patch('/{guru}/aktifkan', 'aktifkan')->name('aktifkan');
            Route::post('/{guru}/reset-kata-sandi', 'resetKataSandi')->name('reset-kata-sandi');
        });

    Route::view('/guru/dashboard', 'guru.dashboard')
        ->middleware('peran:guru')
        ->name('guru.dashboard');

    // Pratinjau frontend PBI-11. Ganti dengan controller RiwayatObservasiController saat backend siap.
    Route::view('/guru/riwayat-observasi', 'guru.riwayat_observasi')
        ->middleware('peran:guru')
        ->name('guru.riwayat-observasi');
});
