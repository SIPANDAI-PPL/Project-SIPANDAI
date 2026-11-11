<?php

namespace App\Models;

use App\Enums\KategoriRespons;
use Database\Factories\AturanStrategiFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class AturanStrategi extends Model
{
    /** @use HasFactory<AturanStrategiFactory> */
    use HasFactory;

    protected $table = 'aturan_strategi';

    public $timestamps = false;

    protected $fillable = [
        'aspek_id',
        'strategi_asal_id',
        'respons_pemicu',
        'minimal_kejadian',
        'strategi_alternatif_id',
        'keterangan',
        'aktif',
    ];

    protected function casts(): array
    {
        return [
            'respons_pemicu' => KategoriRespons::class,
            'minimal_kejadian' => 'integer',
            'aktif' => 'boolean',
        ];
    }

    public function aspek(): BelongsTo
    {
        return $this->belongsTo(AspekPerkembangan::class, 'aspek_id');
    }

    public function strategiAsal(): BelongsTo
    {
        return $this->belongsTo(StrategiPembelajaran::class, 'strategi_asal_id');
    }

    public function strategiAlternatif(): BelongsTo
    {
        return $this->belongsTo(StrategiPembelajaran::class, 'strategi_alternatif_id');
    }

    public function alternatifStrategi(): HasMany
    {
        return $this->hasMany(AlternatifStrategi::class, 'aturan_id');
    }
}
