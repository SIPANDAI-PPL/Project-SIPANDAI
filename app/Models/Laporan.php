<?php

namespace App\Models;

use Database\Factories\LaporanFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Laporan extends Model
{
    /** @use HasFactory<LaporanFactory> */
    use HasFactory;

    const CREATED_AT = 'dibuat_pada';

    const UPDATED_AT = null;

    protected $table = 'laporan';

    protected $fillable = [
        'guru_id',
        'jenis',
        'anak_id',
        'kelas_id',
        'periode_mulai',
        'periode_selesai',
    ];

    protected function casts(): array
    {
        return [
            'periode_mulai' => 'date',
            'periode_selesai' => 'date',
        ];
    }

    public function guru(): BelongsTo
    {
        return $this->belongsTo(User::class, 'guru_id');
    }

    public function anak(): BelongsTo
    {
        return $this->belongsTo(Anak::class, 'anak_id');
    }

    public function kelas(): BelongsTo
    {
        return $this->belongsTo(Kelas::class, 'kelas_id');
    }
}
