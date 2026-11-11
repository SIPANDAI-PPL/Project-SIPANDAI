<?php

namespace App\Models;

use Database\Factories\StrategiPembelajaranFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class StrategiPembelajaran extends Model
{
    /** @use HasFactory<StrategiPembelajaranFactory> */
    use HasFactory;

    protected $table = 'strategi_pembelajaran';

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

    /** Aturan yang memakai strategi ini sebagai strategi asal. */
    public function aturanSebagaiAsal(): HasMany
    {
        return $this->hasMany(AturanStrategi::class, 'strategi_asal_id');
    }

    /** Aturan yang menyarankan strategi ini sebagai alternatif. */
    public function aturanSebagaiAlternatif(): HasMany
    {
        return $this->hasMany(AturanStrategi::class, 'strategi_alternatif_id');
    }

    public function alternatifStrategi(): HasMany
    {
        return $this->hasMany(AlternatifStrategi::class, 'strategi_id');
    }

    public function observasi(): HasMany
    {
        return $this->hasMany(Observasi::class, 'strategi_id');
    }
}
