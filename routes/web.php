<?php

use App\Http\Controllers\Auth\LoginController;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

Route::get('/', function () {
    return view('welcome');
});

/*
|--------------------------------------------------------------------------
| Formulir Observasi (SEMENTARA tanpa login)
|--------------------------------------------------------------------------
| Nanti, setelah database & login siap, pindahkan grup ini ke dalam
| Route::middleware('auth') dan tambahkan ->middleware('peran:guru').
*/
Route::prefix('guru/observasi')
    ->name('guru.observasi.')
    ->group(function () {
        Route::view('/', 'formulir-pencatatan-observasi.index')->name('index');

        Route::post('/', function (Request $request) {
            $request->validate([
                'kelas_id'             => ['required'],
                'anak_id'              => ['required'],
                'tanggal_observasi'    => ['required', 'date'],
                'kegiatan'             => ['required', 'string', 'max:150'],
                'indikator'            => ['required', 'array'],
                'indikator.*'          => ['required', 'in:BSB,BSH,MB,BB'],
                'deskripsi_pengamatan' => ['nullable', 'string', 'max:2000'],
                'tindak_lanjut'        => ['nullable', 'string', 'max:2000'],
            ]);

            // TODO: pindahkan ke ObservasiController@store lalu simpan ke database
            return redirect()
                ->route('guru.observasi.index')
                ->with('sukses', 'Observasi berhasil disimpan.');
        })->name('store');
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
});