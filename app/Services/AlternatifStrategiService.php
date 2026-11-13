<?php

namespace App\Services;

use App\Models\AlternatifStrategi;
use App\Models\Anak;
use App\Models\AturanStrategi;
use App\Models\Kelas;
use App\Models\Observasi;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;

/**
 * PBI-15 Penyusunan alternatif strategi berdasarkan aturan.
 *
 * Observasi tiap anak dibandingkan dengan aturan aktif. Observasi anak lain tidak pernah digabung.
 */
class AlternatifStrategiService
{
    public const STATUS_DISARANKAN = 'disarankan';

    /**
     * Susun saran untuk semua anak aktif di kelas, lalu kembalikan daftar pola respons.
     *
     * Satu pola = satu anak + satu aturan aktif dengan minimal satu observasi yang cocok.
     * Saran baru hanya dibuat jika jumlah observasi cocok >= minimal_kejadian.
     *
     * @return Collection<int, array{anak: Anak, aturan: AturanStrategi, jumlah: int, tanggal: Collection, saran: ?AlternatifStrategi}>
     */
    public function susunUntukKelas(Kelas $kelas): Collection
    {
        $daftarAnak = $kelas->anak()->where('aktif', true)->orderBy('nama')->get();
        $daftarAturan = AturanStrategi::query()
            ->aktif()
            ->with(['aspek', 'strategiAsal', 'strategiAlternatif'])
            ->orderBy('id')
            ->get();

        if ($daftarAnak->isEmpty() || $daftarAturan->isEmpty()) {
            return collect();
        }

        $observasiPerAnak = Observasi::query()
            ->whereIn('anak_id', $daftarAnak->modelKeys())
            ->whereIn('strategi_id', $daftarAturan->pluck('strategi_asal_id')->unique())
            ->orderBy('tanggal')
            ->orderBy('id')
            ->get(['id', 'anak_id', 'strategi_id', 'aspek_id', 'kategori_respons', 'tanggal'])
            ->groupBy('anak_id');

        $saranTerbuka = $this->saranTerbuka($daftarAnak->modelKeys());

        $daftarPola = collect();

        foreach ($daftarAnak as $anak) {
            $observasiAnak = $observasiPerAnak->get($anak->id, collect());

            foreach ($daftarAturan as $aturan) {
                $cocok = $observasiAnak->filter(fn (Observasi $observasi) => $this->cocok($observasi, $aturan))->values();

                if ($cocok->isEmpty()) {
                    continue;
                }

                $saran = $saranTerbuka->get("{$anak->id}-{$aturan->id}");

                // Strategi alternatif yang sudah dinonaktifkan admin tidak disarankan.
                if ($saran === null && $cocok->count() >= $aturan->minimal_kejadian && $aturan->strategiAlternatif->aktif) {
                    $saran = $this->simpanSaran($anak, $aturan, $cocok);
                }

                $daftarPola->push([
                    'anak' => $anak,
                    'aturan' => $aturan,
                    'jumlah' => $cocok->count(),
                    'tanggal' => $cocok->pluck('tanggal'),
                    'saran' => $saran,
                ]);
            }
        }

        return $daftarPola;
    }

    /**
     * Alternatif yang ditambahkan guru sendiri (tanpa aturan) dan masih berstatus disarankan.
     *
     * @return Collection<int, AlternatifStrategi>
     */
    public function saranManual(Kelas $kelas): Collection
    {
        return AlternatifStrategi::query()
            ->whereNull('aturan_id')
            ->where('status', self::STATUS_DISARANKAN)
            ->whereHas('anak', fn ($query) => $query->where('kelas_id', $kelas->id)->where('aktif', true))
            ->with(['anak', 'strategi'])
            ->orderByDesc('dibuat_pada')
            ->orderByDesc('id')
            ->get();
    }

    public function tambahManual(Anak $anak, int $strategiId, ?string $catatan): AlternatifStrategi
    {
        return AlternatifStrategi::create([
            'anak_id' => $anak->id,
            'strategi_id' => $strategiId,
            'aturan_id' => null,
            'dasar_saran' => $catatan,
            'status' => self::STATUS_DISARANKAN,
        ]);
    }

    /**
     * Aturan tanpa aspek berlaku untuk semua aspek.
     */
    private function cocok(Observasi $observasi, AturanStrategi $aturan): bool
    {
        return (int) $observasi->strategi_id === (int) $aturan->strategi_asal_id
            && $observasi->kategori_respons === $aturan->respons_pemicu
            && ($aturan->aspek_id === null || (int) $observasi->aspek_id === (int) $aturan->aspek_id);
    }

    /**
     * @param  list<int>  $anakIds
     * @return Collection<string, AlternatifStrategi> kunci "anak_id-aturan_id"
     */
    private function saranTerbuka(array $anakIds): Collection
    {
        return AlternatifStrategi::query()
            ->whereIn('anak_id', $anakIds)
            ->whereNotNull('aturan_id')
            ->where('status', self::STATUS_DISARANKAN)
            ->with('strategi')
            ->orderBy('id')
            ->get()
            ->keyBy(fn (AlternatifStrategi $saran) => "{$saran->anak_id}-{$saran->aturan_id}");
    }

    /**
     * Simpan saran baru. Dicek ulang di dalam transaksi agar tidak ada saran ganda
     * untuk anak dan aturan yang sama jika halaman dibuka bersamaan.
     *
     * @param  Collection<int, Observasi>  $cocok
     */
    private function simpanSaran(Anak $anak, AturanStrategi $aturan, Collection $cocok): AlternatifStrategi
    {
        return DB::transaction(function () use ($anak, $aturan, $cocok) {
            Anak::query()->whereKey($anak->id)->lockForUpdate()->first();

            $sudahAda = AlternatifStrategi::query()
                ->where('anak_id', $anak->id)
                ->where('aturan_id', $aturan->id)
                ->where('status', self::STATUS_DISARANKAN)
                ->with('strategi')
                ->first();

            if ($sudahAda !== null) {
                return $sudahAda;
            }

            $saran = AlternatifStrategi::create([
                'anak_id' => $anak->id,
                'strategi_id' => $aturan->strategi_alternatif_id,
                'aturan_id' => $aturan->id,
                'dasar_saran' => $this->dasarSaran($aturan, $cocok),
                'status' => self::STATUS_DISARANKAN,
            ]);

            return $saran->setRelation('strategi', $aturan->strategiAlternatif);
        });
    }

    /**
     * Contoh: "Respons Kurang tertarik tercatat 3 kali pada strategi Bercerita Lisan (aspek: Semua aspek).
     * Tanggal observasi: 02/11/2026, 05/11/2026, 09/11/2026."
     *
     * @param  Collection<int, Observasi>  $cocok
     */
    private function dasarSaran(AturanStrategi $aturan, Collection $cocok): string
    {
        return sprintf(
            'Respons %s tercatat %d kali pada strategi %s (aspek: %s). Tanggal observasi: %s.',
            $aturan->respons_pemicu->label(),
            $cocok->count(),
            $aturan->strategiAsal->nama,
            $aturan->aspek?->nama ?? 'Semua aspek',
            $cocok->map(fn (Observasi $observasi) => $observasi->tanggal->format('d/m/Y'))->implode(', '),
        );
    }
}
