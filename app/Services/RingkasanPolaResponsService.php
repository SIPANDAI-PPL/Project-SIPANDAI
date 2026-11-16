<?php

namespace App\Services;

use App\Enums\KategoriRespons;
use App\Models\Anak;
use App\Models\Observasi;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Carbon;
use Illuminate\Support\Collection;

/**
 * PBI-12 Ringkasan Pola Respons Anak (FR-11, NFR-06).
 *
 * Merangkum frekuensi kategori respons dari data observasi historis.
 * Hasilnya hanya hitungan, bukan penilaian tetap atau label untuk anak.
 * Dipakai halaman ringkasan pola respons dan bisa dipakai ulang di profil anak
 * agar ringkasan tren per aspek tetap konsisten.
 */
class RingkasanPolaResponsService
{
    /** Pilihan periode: nilai => label. */
    public const PERIODE = [
        'semua' => 'Semua periode',
        'pekan-ini' => 'Pekan ini',
        'bulan-ini' => 'Bulan ini',
        '30' => '30 hari terakhir',
    ];

    /** Warna tiap kategori, dipakai kartu, badge, dan grafik. */
    public const WARNA = [
        'terlibat_aktif' => '#0F6B4F',
        'terlibat_dengan_pendampingan' => '#3B82C4',
        'kurang_tertarik' => '#E3964A',
        'belum_terlibat' => '#9CA3AF',
    ];

    /**
     * @param  array{periode?: string|null, aspek?: int|string|null, aktivitas?: int|string|null, strategi?: int|string|null, respons?: string|null}  $filter
     * @return array<string, mixed>
     */
    public function hitung(Anak $anak, array $filter = []): array
    {
        $totalSemua = Observasi::query()->where('anak_id', $anak->id)->count();

        $observasi = $this->query($anak, $filter)
            ->with(['aktivitas:id,nama', 'strategi:id,nama', 'aspek:id,nama'])
            ->orderByDesc('tanggal')
            ->orderByDesc('id')
            ->get();

        $perAspek = $this->perKelompok($observasi, 'aspek');

        return [
            'totalSemua' => $totalSemua,
            'total' => $observasi->count(),
            'frekuensi' => $this->frekuensi($observasi),
            'perAspek' => $perAspek,
            'perAktivitas' => $this->perKelompok($observasi, 'aktivitas'),
            'perStrategi' => $this->perKelompok($observasi, 'strategi'),
            'terbaru' => $observasi->take(10)->values(),
            'grafik' => $observasi->isEmpty() ? null : $this->grafik($observasi),
        ];
    }

    /**
     * Ringkasan tren per aspek saja, untuk kartu di profil anak.
     *
     * @return list<array<string, mixed>>
     */
    public function trenPerAspek(Anak $anak, array $filter = []): array
    {
        return $this->perKelompok($this->query($anak, $filter)->with('aspek:id,nama')->get(), 'aspek');
    }

    /**
     * Batas periode inklusif, atau null untuk seluruh periode.
     *
     * @return array{0: string, 1: string}|null
     */
    public function rentangPeriode(?string $periode): ?array
    {
        $hariIni = Carbon::now()->startOfDay();

        return match ($periode) {
            // Pekan dimulai Senin dan berakhir Minggu.
            'pekan-ini' => [
                $hariIni->copy()->startOfWeek(Carbon::MONDAY)->toDateString(),
                $hariIni->copy()->endOfWeek(Carbon::SUNDAY)->toDateString(),
            ],
            'bulan-ini' => [$hariIni->copy()->startOfMonth()->toDateString(), $hariIni->copy()->endOfMonth()->toDateString()],
            '30' => [$hariIni->copy()->subDays(29)->toDateString(), $hariIni->toDateString()],
            default => null,
        };
    }

    private function query(Anak $anak, array $filter): Builder
    {
        $query = Observasi::query()->where('anak_id', $anak->id);

        $rentang = $this->rentangPeriode($filter['periode'] ?? null);

        if ($rentang !== null) {
            $query->whereBetween('tanggal', $rentang);
        }

        foreach (['aspek' => 'aspek_id', 'aktivitas' => 'aktivitas_id', 'strategi' => 'strategi_id', 'respons' => 'kategori_respons'] as $kunci => $kolom) {
            if (($filter[$kunci] ?? null) !== null) {
                $query->where($kolom, $filter[$kunci]);
            }
        }

        return $query;
    }

    /**
     * Jumlah per kategori, selalu lengkap 4 kategori dengan urutan enum.
     *
     * @param  Collection<int, Observasi>  $observasi
     * @return array<string, int>
     */
    private function frekuensi(Collection $observasi): array
    {
        $hitungan = $observasi->countBy(fn (Observasi $item) => $item->kategori_respons->value);

        return collect(KategoriRespons::cases())
            ->mapWithKeys(fn (KategoriRespons $kategori) => [$kategori->value => (int) $hitungan->get($kategori->value, 0)])
            ->all();
    }

    /**
     * Frekuensi kategori per aspek/aktivitas/strategi, urut nama.
     *
     * @param  Collection<int, Observasi>  $observasi
     * @return list<array{nama: string, total: int, frekuensi: array<string, int>, ringkasan: string}>
     */
    private function perKelompok(Collection $observasi, string $relasi): array
    {
        return $observasi
            ->groupBy(fn (Observasi $item) => $item->{$relasi}->nama)
            ->map(function (Collection $grup, string $nama) {
                $frekuensi = $this->frekuensi($grup);

                return [
                    'nama' => $nama,
                    'total' => $grup->count(),
                    'frekuensi' => $frekuensi,
                    'ringkasan' => $this->kalimatRingkasan($frekuensi),
                ];
            })
            ->sortBy('nama', SORT_NATURAL | SORT_FLAG_CASE)
            ->values()
            ->all();
    }

    /**
     * Kalimat deskriptif tentang catatan, bukan label untuk anak (NFR-06).
     *
     * @param  array<string, int>  $frekuensi
     */
    private function kalimatRingkasan(array $frekuensi): string
    {
        $tertinggi = max($frekuensi);
        $kategoriTertinggi = array_keys($frekuensi, $tertinggi, true);

        if ($tertinggi === 0) {
            return 'Belum ada catatan';
        }

        if (count($kategoriTertinggi) > 1) {
            return 'Bervariasi';
        }

        return 'Paling sering: '.KategoriRespons::from($kategoriTertinggi[0])->label();
    }

    /**
     * Konfigurasi data Chart.js. Disisipkan ke halaman sebagai JSON.
     *
     * @param  Collection<int, Observasi>  $observasi
     * @return array<string, mixed>
     */
    private function grafik(Collection $observasi): array
    {
        $kategori = collect(KategoriRespons::cases());

        $kelompok = [];

        foreach (['aspek', 'aktivitas', 'strategi'] as $relasi) {
            $baris = $this->perKelompok($observasi, $relasi);

            $kelompok[$relasi] = [
                'labels' => array_column($baris, 'nama'),
                'datasets' => $kategori->map(fn (KategoriRespons $item) => [
                    'label' => $item->label(),
                    'kategori' => $item->value,
                    'backgroundColor' => self::WARNA[$item->value],
                    'data' => array_map(fn (array $b) => $b['frekuensi'][$item->value], $baris),
                ])->all(),
            ];
        }

        return [
            'kategori' => [
                'labels' => $kategori->map->label()->all(),
                'nilai' => $kategori->map->value->all(),
                'warna' => $kategori->map(fn (KategoriRespons $item) => self::WARNA[$item->value])->all(),
                'data' => array_values($this->frekuensi($observasi)),
            ],
            'kelompok' => $kelompok,
        ];
    }
}
