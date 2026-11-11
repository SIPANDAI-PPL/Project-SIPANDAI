<?php

use App\Http\Controllers\Admin\AturanStrategiController;
use App\Http\Controllers\Auth\LoginController;
use App\Http\Controllers\DataMaster\AspekPerkembanganController;
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

    // PBI-4 Kelola Aspek Perkembangan (admin saja). Diletakkan di bawah grup data-master PBI-2.
    Route::middleware('peran:admin')->prefix('data-master')->name('data-master.')->group(function () {
        Route::controller(AspekPerkembanganController::class)->prefix('aspek')->name('aspek.')->group(function () {
            Route::get('/', 'index')->name('index');
            Route::post('/', 'store')->name('store');
            Route::put('/{aspek}', 'update')->name('update');
            Route::delete('/{aspek}', 'destroy')->name('destroy');
        });
    });

    // PBI-4 Kelola Aturan Alternatif Strategi (admin saja).
    Route::middleware('peran:admin')->prefix('admin/aturan-strategi')->name('admin.aturan-strategi.')
        ->controller(AturanStrategiController::class)
        ->group(function () {
            Route::get('/', 'index')->name('index');
            Route::post('/', 'store')->name('store');
            Route::put('/{aturan}', 'update')->name('update');
            Route::patch('/{aturan}/nonaktifkan', 'nonaktifkan')->name('nonaktifkan');
            Route::patch('/{aturan}/aktifkan', 'aktifkan')->name('aktifkan');
            Route::delete('/{aturan}', 'destroy')->name('destroy');
        });
});
