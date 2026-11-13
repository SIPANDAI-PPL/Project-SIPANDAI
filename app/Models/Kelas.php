<?php

namespace App\Models;

use Database\Factories\KelasFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Kelas extends Model
{
    /** @use HasFactory<KelasFactory> */
    use HasFactory;

    protected $table = 'kelas';

    public $timestamps = false;

    protected $fillable = [
        'guru_id',
        'nama_kelas',
        'kelompok',
        'tahun_ajaran',
    ];

    public function guru(): BelongsTo
    {
        return $this->belongsTo(User::class, 'guru_id');
    }

    public function anak(): HasMany
    {
        return $this->hasMany(Anak::class, 'kelas_id');
    }

    public function rpp(): HasMany
    {
        return $this->hasMany(Rpp::class, 'kelas_id');
    }

    public function laporan(): HasMany
    {
        return $this->hasMany(Laporan::class, 'kelas_id');
    }
}
