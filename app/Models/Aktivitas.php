<?php

namespace App\Models;

use Database\Factories\AktivitasFactory;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Aktivitas extends Model
{
    /** @use HasFactory<AktivitasFactory> */
    use HasFactory;

    protected $table = 'aktivitas';

    public $timestamps = false;

    protected $fillable = [
        'nama',
        'deskripsi',
        'aktif',
    ];

    protected function casts(): array
    {
        return [
            'aktif' => 'boolean',
        ];
    }

    /** Hanya aktivitas berstatus aktif, untuk pilihan di formulir observasi. */
    public function scopeAktif(Builder $query): void
    {
        $query->where('aktif', true);
    }

    public function observasi(): HasMany
    {
        return $this->hasMany(Observasi::class, 'aktivitas_id');
    }
}
