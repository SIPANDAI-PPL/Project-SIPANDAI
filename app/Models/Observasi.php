<?php

namespace App\Models;

use App\Enums\KategoriRespons;
use Database\Factories\ObservasiFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Observasi extends Model
{
    /** @use HasFactory<ObservasiFactory> */
    use HasFactory;

    const CREATED_AT = 'dibuat_pada';

    const UPDATED_AT = 'diubah_pada';

    protected $table = 'observasi';

    protected $fillable = [
        'anak_id',
        'guru_id',
        'aktivitas_id',
        'strategi_id',
        'aspek_id',
        'rpp_id',
        'tindak_lanjut_id',
        'tanggal',
        'kategori_respons',
        'catatan_pendukung',
    ];

    protected function casts(): array
    {
        return [
            'tanggal' => 'date',
            'kategori_respons' => KategoriRespons::class,
        ];
    }

    public function anak(): BelongsTo
    {
        return $this->belongsTo(Anak::class, 'anak_id');
    }

    public function guru(): BelongsTo
    {
        return $this->belongsTo(User::class, 'guru_id');
    }

    public function aktivitas(): BelongsTo
    {
        return $this->belongsTo(Aktivitas::class, 'aktivitas_id');
    }

    public function strategi(): BelongsTo
    {
        return $this->belongsTo(StrategiPembelajaran::class, 'strategi_id');
    }

    public function aspek(): BelongsTo
    {
        return $this->belongsTo(AspekPerkembangan::class, 'aspek_id');
    }

    public function rpp(): BelongsTo
    {
        return $this->belongsTo(Rpp::class, 'rpp_id');
    }

    public function tindakLanjut(): BelongsTo
    {
        return $this->belongsTo(TindakLanjut::class, 'tindak_lanjut_id');
    }

    public function riwayatPerubahan(): HasMany
    {
        return $this->hasMany(RiwayatPerubahan::class, 'observasi_id');
    }
}
