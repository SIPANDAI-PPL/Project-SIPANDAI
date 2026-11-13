<?php

namespace App\Models;

use Database\Factories\AnakFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Anak extends Model
{
    /** @use HasFactory<AnakFactory> */
    use HasFactory;

    protected $table = 'anak';

    public $timestamps = false;

    protected $fillable = [
        'kelas_id',
        'nama',
        'nama_panggilan',
        'tanggal_lahir',
        'aktif',
    ];

    protected function casts(): array
    {
        return [
            'tanggal_lahir' => 'date',
            'aktif' => 'boolean',
        ];
    }

    public function kelas(): BelongsTo
    {
        return $this->belongsTo(Kelas::class, 'kelas_id');
    }

    public function alternatifStrategi(): HasMany
    {
        return $this->hasMany(AlternatifStrategi::class, 'anak_id');
    }

    public function observasi(): HasMany
    {
        return $this->hasMany(Observasi::class, 'anak_id');
    }

    public function laporan(): HasMany
    {
        return $this->hasMany(Laporan::class, 'anak_id');
    }
}
