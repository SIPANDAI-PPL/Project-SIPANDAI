<?php

namespace App\Models;

use Database\Factories\AlternatifStrategiFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasOne;

class AlternatifStrategi extends Model
{
    /** @use HasFactory<AlternatifStrategiFactory> */
    use HasFactory;

    const CREATED_AT = 'dibuat_pada';

    const UPDATED_AT = null;

    protected $table = 'alternatif_strategi';

    protected $fillable = [
        'anak_id',
        'strategi_id',
        'aturan_id',
        'dasar_saran',
        'status',
    ];

    public function anak(): BelongsTo
    {
        return $this->belongsTo(Anak::class, 'anak_id');
    }

    public function strategi(): BelongsTo
    {
        return $this->belongsTo(StrategiPembelajaran::class, 'strategi_id');
    }

    public function aturan(): BelongsTo
    {
        return $this->belongsTo(AturanStrategi::class, 'aturan_id');
    }

    public function tindakLanjut(): HasOne
    {
        return $this->hasOne(TindakLanjut::class, 'alternatif_id');
    }
}
