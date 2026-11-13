<?php

namespace App\Http\Controllers\DataMaster\Concerns;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\Request;

/** Pencarian (?q=) dan filter status (?status=) bersama untuk daftar aktivitas dan strategi (PBI-2). */
trait FilterDataMaster
{
    /**
     * Menerapkan pencarian dan filter status ke $query (diubah langsung).
     *
     * @return array{cari: string, status: string, jumlah: array{semua: int, aktif: int, nonaktif: int}}
     */
    protected function terapkanFilter(Builder $query, Request $request): array
    {
        $cari = is_string($request->query('q')) ? trim($request->query('q')) : '';
        $status = in_array($request->query('status'), ['aktif', 'nonaktif'], true)
            ? $request->query('status')
            : 'semua';

        if ($cari !== '') {
            $pola = '%'.addcslashes($cari, '%_\\').'%';
            $query->where(fn (Builder $q) => $q->where('nama', 'like', $pola)->orWhere('deskripsi', 'like', $pola));
        }

        // Jumlah per status mengikuti kata kunci pencarian, dipakai untuk badge di pilihan status.
        $jumlah = [
            'semua' => (clone $query)->count(),
            'aktif' => (clone $query)->where('aktif', true)->count(),
            'nonaktif' => (clone $query)->where('aktif', false)->count(),
        ];

        if ($status !== 'semua') {
            $query->where('aktif', $status === 'aktif');
        }

        return compact('cari', 'status', 'jumlah');
    }
}
