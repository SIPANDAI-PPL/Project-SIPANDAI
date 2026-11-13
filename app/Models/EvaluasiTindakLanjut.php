<?php

namespace App\Models;

use Database\Factories\EvaluasiTindakLanjutFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class EvaluasiTindakLanjut extends Model
{
    /** @use HasFactory<EvaluasiTindakLanjutFactory> */
    use HasFactory;

    protected $table = 'evaluasi_tindak_lanjut';

    public $timestamps = false;

    protected $fillable = [
        'tindak_lanjut_id',
        'tanggal',
        'kesimpulan',
        'catatan',
    ];

    protected function casts(): array
    {
        return [
            'tanggal' => 'date',
        ];
    }

    public function tindakLanjut(): BelongsTo
    {
        return $this->belongsTo(TindakLanjut::class, 'tindak_lanjut_id');
    }
}
