<?php

namespace App\Models;

use Carbon\CarbonInterface;
use Database\Factories\AnakFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Carbon;

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
        'usia',
        'aktif',
    ];

    protected function casts(): array
    {
        return [
            'tanggal_lahir' => 'date',
            'usia' => 'integer',
            'aktif' => 'boolean',
        ];
    }

    /**
     * Kolom usia disimpan dalam tahun penuh dan dihitung ulang setiap kali
     * tanggal lahir diisi atau diubah, jadi tampilan cukup membaca kolom ini.
     */
    protected static function booted(): void
    {
        static::saving(function (Anak $anak) {
            if ($anak->tanggal_lahir && ($anak->isDirty('tanggal_lahir') || $anak->usia === null)) {
                $anak->usia = self::hitungUsiaTahun($anak->tanggal_lahir);
            }
        });
    }

    /**
     * Usia dalam tahun penuh pada tanggal acuan (default hari ini).
     */
    public static function hitungUsiaTahun(CarbonInterface|string $tanggalLahir, ?CarbonInterface $acuan = null): int
    {
        return intdiv(max(0, self::hitungUsiaBulan($tanggalLahir, $acuan)), 12);
    }

    /**
     * Usia dalam bulan penuh pada tanggal acuan (default hari ini).
     * Dipakai validasi usia 24–59 bulan.
     */
    public static function hitungUsiaBulan(CarbonInterface|string $tanggalLahir, ?CarbonInterface $acuan = null): int
    {
        $lahir = Carbon::parse($tanggalLahir)->startOfDay();
        $acuan = ($acuan ?? Carbon::today())->copy()->startOfDay();

        return $lahir->greaterThan($acuan) ? -1 : (int) $lahir->diffInMonths($acuan);
    }

    /**
     * Hanya mengubah kolom aktif. Data anak tidak pernah dihapus permanen.
     */
    public function ubahStatusAktif(bool $aktif): void
    {
        $this->update(['aktif' => $aktif]);
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
