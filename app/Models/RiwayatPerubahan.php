<?php

namespace App\Models;

use Database\Factories\RiwayatPerubahanFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class RiwayatPerubahan extends Model
{
    /** @use HasFactory<RiwayatPerubahanFactory> */
    use HasFactory;

    protected $table = 'riwayat_perubahan';

    // Kolom waktu diisi database lewat useCurrent().
    public $timestamps = false;

    protected $fillable = [
        'dilakukan_oleh',
        'observasi_id',
        'tindak_lanjut_id',
        'aksi',
        'keterangan',
        'waktu',
    ];

    protected function casts(): array
    {
        return [
            'waktu' => 'datetime',
        ];
    }

    public function pengguna(): BelongsTo
    {
        return $this->belongsTo(User::class, 'dilakukan_oleh');
    }

    public function observasi(): BelongsTo
    {
        return $this->belongsTo(Observasi::class, 'observasi_id');
    }

    public function tindakLanjut(): BelongsTo
    {
        return $this->belongsTo(TindakLanjut::class, 'tindak_lanjut_id');
    }
}
