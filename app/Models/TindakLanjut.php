<?php

namespace App\Models;

use Database\Factories\TindakLanjutFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class TindakLanjut extends Model
{
    /** @use HasFactory<TindakLanjutFactory> */
    use HasFactory;

    protected $table = 'tindak_lanjut';

    public $timestamps = false;

    protected $fillable = [
        'alternatif_id',
        'rencana',
        'alasan_dugaan_penyebab',
        'tanggal_mulai',
        'status',
    ];

    protected function casts(): array
    {
        return [
            'tanggal_mulai' => 'date',
        ];
    }

    public function alternatif(): BelongsTo
    {
        return $this->belongsTo(AlternatifStrategi::class, 'alternatif_id');
    }

    public function evaluasi(): HasMany
    {
        return $this->hasMany(EvaluasiTindakLanjut::class, 'tindak_lanjut_id');
    }

    public function observasi(): HasMany
    {
        return $this->hasMany(Observasi::class, 'tindak_lanjut_id');
    }

    public function riwayatPerubahan(): HasMany
    {
        return $this->hasMany(RiwayatPerubahan::class, 'tindak_lanjut_id');
    }
}
