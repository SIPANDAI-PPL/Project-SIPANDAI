<?php

namespace App\Models;

use Database\Factories\RppFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Rpp extends Model
{
    /** @use HasFactory<RppFactory> */
    use HasFactory;

    const CREATED_AT = 'diunggah_pada';

    const UPDATED_AT = 'diubah_pada';

    protected $table = 'rpp';

    protected $fillable = [
        'guru_id',
        'kelas_id',
        'judul',
        'jenis',
        'tanggal_mulai',
        'tanggal_selesai',
        'nama_file',
        'path_file',
        'tipe_file',
        'ukuran_file',
        'catatan',
    ];

    protected function casts(): array
    {
        return [
            'tanggal_mulai' => 'date',
            'tanggal_selesai' => 'date',
            // Ukuran file dalam byte.
            'ukuran_file' => 'integer',
        ];
    }

    public function guru(): BelongsTo
    {
        return $this->belongsTo(User::class, 'guru_id');
    }

    public function kelas(): BelongsTo
    {
        return $this->belongsTo(Kelas::class, 'kelas_id');
    }

    public function observasi(): HasMany
    {
        return $this->hasMany(Observasi::class, 'rpp_id');
    }
}
