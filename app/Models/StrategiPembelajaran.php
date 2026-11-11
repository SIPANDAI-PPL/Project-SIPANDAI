<?php

namespace App\Models;

use Database\Factories\StrategiPembelajaranFactory;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class StrategiPembelajaran extends Model
{
    /** @use HasFactory<StrategiPembelajaranFactory> */
    use HasFactory;

    protected $table = 'strategi_pembelajaran';

    public $timestamps = false;

    /** Relasi yang dihitung (withCount) untuk kolom "Dipakai di" dan pengecekan hapus. */
    public const RELASI_PEMAKAIAN = [
        'observasi',
        'aturanSebagaiAsal',
        'aturanSebagaiAlternatif',
        'alternatifStrategi',
    ];

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

    /** Hanya strategi berstatus aktif, untuk pilihan di formulir observasi dan aturan strategi. */
    public function scopeAktif(Builder $query): void
    {
        $query->where('aktif', true);
    }

    /** Jumlah aturan (sebagai asal dan alternatif). Butuh withCount/loadCount RELASI_PEMAKAIAN. */
    public function jumlahAturan(): int
    {
        return (int) $this->aturan_sebagai_asal_count + (int) $this->aturan_sebagai_alternatif_count;
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
