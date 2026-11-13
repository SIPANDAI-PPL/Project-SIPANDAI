<?php

use App\Http\Controllers\Admin\AturanStrategiController;
use App\Http\Controllers\Admin\KelolaAkunGuruController;
use App\Http\Controllers\Auth\GantiKataSandiController;
use App\Http\Controllers\Auth\LoginController;
use App\Http\Controllers\DataMaster\AktivitasController;
use App\Http\Controllers\DataMaster\AspekPerkembanganController;
use App\Http\Controllers\DataMaster\StrategiPembelajaranController;
use App\Http\Controllers\Guru\AlternatifStrategiController;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

// {guru} hanya cocok dengan akun berperan guru; akun admin menghasilkan 404.
Route::bind('guru', fn (string $nilai) => User::where('peran', 'guru')->findOrFail($nilai));

// PBI-22 Landing page. Tidak mengalihkan otomatis; pengguna yang sudah masuk melihat tombol ke dashboard.
Route::view('/', 'landing')->name('beranda');

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

    // PBI-15 Penyusunan Alternatif Strategi Berdasarkan Aturan.
    Route::middleware('peran:guru')
        ->prefix('guru/alternatif-strategi')
        ->name('guru.alternatif-strategi.')
        ->controller(AlternatifStrategiController::class)
        ->group(function () {
            Route::get('/', 'index')->name('index');
            Route::post('/anak/{anak}', 'store')->name('store');
        });

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