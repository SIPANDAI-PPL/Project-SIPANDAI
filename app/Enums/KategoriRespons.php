<?php

namespace App\Enums;

enum KategoriRespons: string
{
    case TerlibatAktif = 'terlibat_aktif';
    case TerlibatDenganPendampingan = 'terlibat_dengan_pendampingan';
    case KurangTertarik = 'kurang_tertarik';
    case BelumTerlibat = 'belum_terlibat';

    public function label(): string
    {
        return match ($this) {
            self::TerlibatAktif => 'Terlibat aktif',
            self::TerlibatDenganPendampingan => 'Terlibat dengan pendampingan',
            self::KurangTertarik => 'Kurang tertarik',
            self::BelumTerlibat => 'Belum terlibat',
        };
    }
}