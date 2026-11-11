<?php

namespace App\Models;

use Database\Factories\AspekPerkembanganFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class AspekPerkembangan extends Model
{
    /** @use HasFactory<AspekPerkembanganFactory> */
    use HasFactory;

    protected $table = 'aspek_perkembangan';

    public $timestamps = false;

    protected $fillable = [
        'nama',
        'deskripsi',
    ];

    public function aturanStrategi(): HasMany
    {
        return $this->hasMany(AturanStrategi::class, 'aspek_id');
    }

    public function observasi(): HasMany
    {
        return $this->hasMany(Observasi::class, 'aspek_id');
    }
}
