<?php

namespace App\Services;

use App\Models\AlternatifStrategi;
use App\Models\Anak;
use App\Models\AturanStrategi;
use App\Models\Kelas;
use App\Models\Observasi;
use App\Models\User;
use Illuminate\Database\Eloquent\Collection as EloquentCollection;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

/**
 * PBI-15 Penyusunan alternatif strategi berdasarkan aturan.
 *
 * Observasi tiap anak dibandingkan dengan aturan aktif. Observasi anak lain tidak pernah digabung.
 */
class AlternatifStrategiService
{
    public const STATUS_DISARANKAN = 'disarankan';

    public const STATUS_DIPILIH = 'dipilih';

    public const STATUS_DIABAIKAN = 'diabaikan';

    /**
     * PBI-16: transisi status yang boleh dilakukan guru, [status lama => daftar status baru].
     * Alternatif yang sudah dipilih tidak bisa diubah lagi.
     */
    private const TRANSISI = [
        self::STATUS_DISARANKAN => [self::STATUS_DIPILIH, self::STATUS_DIABAIKAN],
        self::STATUS_DIABAIKAN => [self::STATUS_DIPILIH],
    ];

    public const PESAN_STRATEGI_GANDA = 'Strategi ini sudah ada di daftar saran anak.';

    /**
     * Susun saran untuk semua anak aktif di kelas, lalu kembalikan daftar pola respons.
     *
     * Satu pola = satu anak + satu aturan aktif dengan minimal satu observasi yang cocok.
     * Saran baru hanya dibuat jika jumlah observasi cocok >= minimal_kejadian (lihat susun()).
     *
     * @return Collection<int, array{anak: Anak, aturan: AturanStrategi, jumlah: int, tanggal: Collection, saran: ?AlternatifStrategi}>
     */
    public function susunUntukKelas(Kelas $kelas): Collection
    {
        return $this->susun($kelas->anak()->where('aktif', true)->orderBy('nama')->get());
    }

    /**
     * Susun saran untuk satu anak. Logika sama dengan susunUntukKelas.
     * Disiapkan untuk dipanggil setelah observasi anak disimpan (PBI-07/PBI-08).
     *
     * @return Collection<int, array{anak: Anak, aturan: AturanStrategi, jumlah: int, tanggal: Collection, saran: ?AlternatifStrategi}>
     */
    public function susunUntukAnak(Anak $anak): Collection
    {
        return $anak->aktif ? $this->susun(new EloquentCollection([$anak])) : collect();
    }

    /**
     * Inti penyusunan saran untuk sekumpulan anak aktif.
     *
     * Per anak + aturan, dilihat saran terakhir (status apa pun):
     * - belum ada saran: semua observasi cocok dihitung;
     * - saran terakhir masih disarankan: tidak dibuat saran baru;
     * - saran terakhir sudah dipilih/diabaikan: hanya observasi cocok yang dicatat setelah saran itu dibuat.
     * Jadi satu aturan dapat mendasari banyak saran untuk anak yang sama.
     *
     * @param  EloquentCollection<int, Anak>  $daftarAnak
     * @return Collection<int, array{anak: Anak, aturan: AturanStrategi, jumlah: int, tanggal: Collection, saran: ?AlternatifStrategi}>
     */
    private function susun(EloquentCollection $daftarAnak): Collection
    {
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
            ->get(['id', 'anak_id', 'strategi_id', 'aspek_id', 'kategori_respons', 'tanggal', 'dibuat_pada'])
            ->groupBy('anak_id');

        $saranTerakhir = $this->saranTerakhir($daftarAnak->modelKeys());

        $daftarPola = collect();

        foreach ($daftarAnak as $anak) {
            $observasiAnak = $observasiPerAnak->get($anak->id, collect());

            foreach ($daftarAturan as $aturan) {
                $cocok = $observasiAnak->filter(fn (Observasi $observasi) => $this->cocok($observasi, $aturan))->values();

                if ($cocok->isEmpty()) {
                    continue;
                }

                $saran = $saranTerakhir->get("{$anak->id}-{$aturan->id}");

                if ($saran?->status !== self::STATUS_DISARANKAN) {
                    $dihitung = $saran === null ? $cocok : $cocok->filter(fn (Observasi $observasi) => $this->dicatatSetelah($observasi, $saran))->values();

                    // Strategi alternatif yang sudah dinonaktifkan admin tidak disarankan.
                    if ($dihitung->count() >= $aturan->minimal_kejadian && $aturan->strategiAlternatif->aktif) {
                        $saran = $this->simpanSaran($anak, $aturan, $dihitung, $saran);
                    }
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
     * Alternatif yang ditambahkan guru sendiri (tanpa aturan), semua status (PBI-16).
     *
     * @return Collection<int, AlternatifStrategi>
     */
    public function saranManual(Kelas $kelas): Collection
    {
        return AlternatifStrategi::query()
            ->whereNull('aturan_id')
            ->whereHas('anak', fn ($query) => $query->where('kelas_id', $kelas->id)->where('aktif', true))
            ->with(['anak', 'strategi'])
            ->orderByDesc('dibuat_pada')
            ->orderByDesc('id')
            ->get();
    }

    /**
     * Kelompokkan pola (hasil susunUntukKelas) dan alternatif buatan guru menjadi satu item per anak, urut nama.
     *
     * @param  Collection<int, array>  $daftarPola
     * @param  Collection<int, AlternatifStrategi>  $daftarManual
     * @return Collection<int, array{anak: Anak, pola: Collection, manual: Collection, punya_saran: bool}>
     */
    public function kelompokkanPerAnak(Collection $daftarPola, Collection $daftarManual): Collection
    {
        $perAnak = [];

        foreach ($daftarPola as $pola) {
            $perAnak[$pola['anak']->id] ??= ['anak' => $pola['anak'], 'pola' => collect(), 'manual' => collect()];
            $perAnak[$pola['anak']->id]['pola']->push($pola);
        }

        foreach ($daftarManual as $saran) {
            $perAnak[$saran->anak_id] ??= ['anak' => $saran->anak, 'pola' => collect(), 'manual' => collect()];
            $perAnak[$saran->anak_id]['manual']->push($saran);
        }

        return collect($perAnak)
            ->map(fn (array $item) => $item + [
                'punya_saran' => $item['manual']->isNotEmpty() || $item['pola']->contains(fn (array $pola) => $pola['saran'] !== null),
            ])
            ->sortBy(fn (array $item) => mb_strtolower($item['anak']->nama))
            ->values();
    }

    /**
     * Data satu anak untuk Mode Fokus. Saran disusun lewat susunUntukAnak (logika sama dengan halaman daftar).
     * saran_aturan berisi SEMUA saran dari aturan untuk anak ini, termasuk saran lama yang sudah
     * dipilih/diabaikan, karena pola hanya menyimpan saran terakhir per aturan.
     *
     * @return array{anak: Anak, pola: Collection, manual: Collection, punya_saran: bool, saran_aturan: Collection}
     */
    public function dataAnak(Anak $anak): array
    {
        $item = $this->kelompokkanPerAnak(
            $this->susunUntukAnak($anak),
            $this->saranManual($anak->kelas)->where('anak_id', $anak->id),
        )->first() ?? ['anak' => $anak, 'pola' => collect(), 'manual' => collect(), 'punya_saran' => false];

        return $item + [
            'saran_aturan' => AlternatifStrategi::query()
                ->where('anak_id', $anak->id)
                ->whereNotNull('aturan_id')
                ->with('strategi')
                ->orderBy('id')
                ->get(),
        ];
    }

    /**
     * Anak sudah punya saran berstatus disarankan dengan strategi yang sama (dari aturan maupun buatan guru).
     * Strategi yang pernah dipilih/diabaikan boleh ditambahkan lagi.
     */
    public function strategiMasihDisarankan(Anak $anak, int $strategiId): bool
    {
        return AlternatifStrategi::query()
            ->where('anak_id', $anak->id)
            ->where('strategi_id', $strategiId)
            ->where('status', self::STATUS_DISARANKAN)
            ->exists();
    }

    /**
     * Dicek ulang di dalam transaksi dengan baris anak dikunci agar dua permintaan bersamaan
     * tidak menghasilkan saran ganda.
     *
     * @throws ValidationException jika strategi masih disarankan untuk anak ini.
     */
    public function tambahManual(Anak $anak, int $strategiId, ?string $catatan): AlternatifStrategi
    {
        return DB::transaction(function () use ($anak, $strategiId, $catatan) {
            Anak::query()->whereKey($anak->id)->lockForUpdate()->first();

            if ($this->strategiMasihDisarankan($anak, $strategiId)) {
                throw ValidationException::withMessages(['strategi_id' => self::PESAN_STRATEGI_GANDA]);
            }

            return AlternatifStrategi::create([
                'anak_id' => $anak->id,
                'strategi_id' => $strategiId,
                'aturan_id' => null,
                'dasar_saran' => $catatan,
                'status' => self::STATUS_DISARANKAN,
            ]);
        });
    }

    /**
     * PBI-16: alasan perubahan status ditolak, atau null jika boleh.
     * Dipakai UbahStatusAlternatifRequest dan dicek ulang di ubahStatus.
     */
    public function alasanTolak(AlternatifStrategi $alternatif, string $statusBaru): ?string
    {
        if ($alternatif->status === self::STATUS_DIPILIH) {
            return 'Alternatif yang sudah dipilih tidak bisa diubah.';
        }

        if ($alternatif->status === $statusBaru) {
            return "Alternatif ini sudah berstatus {$statusBaru}.";
        }

        if (! in_array($statusBaru, self::TRANSISI[$alternatif->status] ?? [], true)) {
            return 'Perubahan status ini tidak diizinkan.';
        }

        // Hanya satu alternatif dipilih per anak.
        $sudahAdaDipilih = $statusBaru === self::STATUS_DIPILIH && AlternatifStrategi::query()
            ->where('anak_id', $alternatif->anak_id)
            ->where('status', self::STATUS_DIPILIH)
            ->whereKeyNot($alternatif->id)
            ->exists();

        return $sudahAdaDipilih ? 'Sudah ada alternatif yang dipilih untuk anak ini.' : null;
    }

    /**
     * PBI-16: ubah status alternatif. Baris anak dikunci agar dua permintaan bersamaan
     * tidak menghasilkan dua alternatif dipilih.
     *
     * @throws ValidationException jika transisi tidak diizinkan.
     */
    public function ubahStatus(AlternatifStrategi $alternatif, string $statusBaru, User $guru): AlternatifStrategi
    {
        // $guru dipakai untuk riwayat_perubahan (lihat TODO di bawah).
        return DB::transaction(function () use ($alternatif, $statusBaru) {
            Anak::query()->whereKey($alternatif->anak_id)->lockForUpdate()->first();
            $alternatif->refresh();

            $alasan = $this->alasanTolak($alternatif, $statusBaru);
            if ($alasan !== null) {
                throw ValidationException::withMessages(['status' => $alasan]);
            }

            $statusLama = $alternatif->status;
            $alternatif->update(['status' => $statusBaru]);

            // TODO PBI-16 (NFR-08): tulis riwayat_perubahan setelah kolom alternatif_id
            // ditambahkan oleh pemilik migration. Kolom itu belum ada, jadi baris ini belum ditulis:
            // RiwayatPerubahan::create([
            //     'dilakukan_oleh' => $guru->id,
            //     'alternatif_id' => $alternatif->id,
            //     'aksi' => 'ubah',
            //     'keterangan' => "{$statusLama} -> {$statusBaru}",
            //     'waktu' => now(),
            // ]);

            return $alternatif;
        });
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
     * Observasi dicatat setelah saran dibuat. Pakai observasi.dibuat_pada (waktu pencatatan);
     * jika kosong, pakai tanggal observasi yang lebih besar dari tanggal saran dibuat.
     */
    private function dicatatSetelah(Observasi $observasi, AlternatifStrategi $saran): bool
    {
        if ($saran->dibuat_pada === null) {
            return true;
        }

        return $observasi->dibuat_pada !== null
            ? $observasi->dibuat_pada->gt($saran->dibuat_pada)
            : $observasi->tanggal->gt($saran->dibuat_pada->copy()->startOfDay());
    }

    /**
     * Saran terakhir dari aturan per anak + aturan, status apa pun.
     *
     * @param  list<int>  $anakIds
     * @return Collection<string, AlternatifStrategi> kunci "anak_id-aturan_id"
     */
    private function saranTerakhir(array $anakIds): Collection
    {
        return AlternatifStrategi::query()
            ->whereIn('anak_id', $anakIds)
            ->whereNotNull('aturan_id')
            ->with('strategi')
            ->orderBy('id')
            ->get()
            ->keyBy(fn (AlternatifStrategi $saran) => "{$saran->anak_id}-{$saran->aturan_id}");
    }

    /**
     * Simpan saran baru. Dicek ulang di dalam transaksi agar tidak ada saran ganda
     * untuk anak dan aturan yang sama jika halaman dibuka bersamaan.
     *
     * @param  Collection<int, Observasi>  $cocok  observasi yang dihitung untuk saran ini saja
     * @param  ?AlternatifStrategi  $saranSebelumnya  saran terakhir yang dipakai saat menghitung $cocok
     */
    private function simpanSaran(Anak $anak, AturanStrategi $aturan, Collection $cocok, ?AlternatifStrategi $saranSebelumnya): AlternatifStrategi
    {
        return DB::transaction(function () use ($anak, $aturan, $cocok, $saranSebelumnya) {
            Anak::query()->whereKey($anak->id)->lockForUpdate()->first();

            $terakhir = AlternatifStrategi::query()
                ->where('anak_id', $anak->id)
                ->where('aturan_id', $aturan->id)
                ->with('strategi')
                ->orderByDesc('id')
                ->first();

            // Sudah ada saran terbuka, atau ada saran yang lebih baru dari yang dipakai saat menghitung.
            if ($terakhir !== null && ($terakhir->status === self::STATUS_DISARANKAN || $terakhir->id !== $saranSebelumnya?->id)) {
                return $terakhir;
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
