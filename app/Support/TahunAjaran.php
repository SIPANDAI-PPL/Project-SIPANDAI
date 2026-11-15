<?php

namespace App\Support;

use Carbon\CarbonImmutable;
use Carbon\CarbonInterface;

final class TahunAjaran
{
    /** Tahun ajaran dimulai bulan Juli dan berakhir bulan Juni. */
    public const BULAN_MULAI = 7;

    /** Contoh keluaran: "2024/2025". */
    public static function dariTanggal(CarbonInterface|string|null $tanggal = null): string
    {
        $t = $tanggal ? CarbonImmutable::parse($tanggal) : CarbonImmutable::now();
        $awal = $t->month >= self::BULAN_MULAI ? $t->year : $t->year - 1;

        return $awal . '/' . ($awal + 1);
    }

    public static function valid(?string $nilai): bool
    {
        return $nilai !== null
            && preg_match('~^(\d{4})/(\d{4})$~', $nilai, $m) === 1
            && (int) $m[2] === (int) $m[1] + 1;
    }

    /** Pakai nilai dari pengguna bila valid, selain itu tahun ajaran berjalan. */
    public static function normalisasi(?string $nilai): string
    {
        return self::valid($nilai) ? $nilai : self::dariTanggal();
    }

    /** @return array{0: CarbonImmutable, 1: CarbonImmutable} */
    public static function rentang(string $tahunAjaran): array
    {
        $awal = (int) substr($tahunAjaran, 0, 4);

        return [
            CarbonImmutable::create($awal, self::BULAN_MULAI, 1)->startOfDay(),
            CarbonImmutable::create($awal + 1, self::BULAN_MULAI - 1, 30)->endOfDay(),
        ];
    }

    /**
     * Daftar tahun ajaran (terbaru dulu) dari tanggal data paling awal sampai paling akhir,
     * selalu menyertakan tahun ajaran berjalan.
     *
     * @return list<string>
     */
    public static function daftar(?string $tanggalAwal, ?string $tanggalAkhir): array
    {
        $sekarang = (int) substr(self::dariTanggal(), 0, 4);
        $awal = $tanggalAwal ? (int) substr(self::dariTanggal($tanggalAwal), 0, 4) : $sekarang;
        $akhir = $tanggalAkhir ? (int) substr(self::dariTanggal($tanggalAkhir), 0, 4) : $sekarang;

        $awal = min($awal, $sekarang);
        $akhir = max($akhir, $sekarang);

        $hasil = [];
        for ($tahun = $akhir; $tahun >= $awal; $tahun--) {
            $hasil[] = $tahun . '/' . ($tahun + 1);
        }

        return $hasil;
    }
}