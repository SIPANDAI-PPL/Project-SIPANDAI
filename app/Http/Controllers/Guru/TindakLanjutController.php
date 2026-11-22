<?php

namespace App\Http\Controllers\Guru;

use App\Http\Controllers\Controller;
use App\Http\Requests\Guru\SimpanTindakLanjutRequest;
use App\Http\Requests\Guru\UbahTindakLanjutRequest;
use App\Models\AlternatifStrategi;
use App\Models\Anak;
use App\Models\RiwayatPerubahan;
use App\Models\TindakLanjut;
use App\Services\AlternatifStrategiService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;
use Illuminate\View\View;

class TindakLanjutController extends Controller
{
    // Fitur: PBI-17 Pencatatan Tindak Lanjut

    public const STATUS_DIRENCANAKAN = 'direncanakan';

    public const STATUS_BERJALAN = 'berjalan';

    private const PER_HALAMAN = 10;

    /**
     * Formulir pencatatan. ?anak= mengisi anak otomatis, ?alternatif= mengisi strategi
     * (dipakai tombol "Buat Tindak Lanjut" di Mode Fokus alternatif strategi).
     */
    public function create(Request $request): View
    {
        $guru = $request->user();

        // Hanya anak aktif di kelas yang diampu guru (NFR-04).
        $daftarAnak = Anak::query()
            ->whereIn('kelas_id', $guru->kelasDiampu()->select('id'))
            ->where('aktif', true)
            ->with('kelas')
            ->orderBy('nama')
            ->get();

        $anakDariTautan = null;
        if ($request->filled('anak')) {
            $anakDariTautan = Anak::query()->findOrFail($request->query('anak'));
            abort_unless(
                $anakDariTautan->aktif && $guru->can('kelola', $anakDariTautan),
                403,
                'Akses ditolak.',
            );
        }

        // [anak_id => [[id, nama, rencana, respons_awal], ...]]: hanya alternatif dipilih tanpa tindak lanjut.
        // respons_awal = respons pemicu aturan yang menghasilkan saran; null untuk alternatif buatan guru.
        $pilihanAlternatif = AlternatifStrategi::query()
            ->whereIn('anak_id', $daftarAnak->modelKeys())
            ->where('status', AlternatifStrategiService::STATUS_DIPILIH)
            ->whereDoesntHave('tindakLanjut')
            ->with('strategi', 'aturan')
            ->orderBy('id')
            ->get()
            ->groupBy('anak_id')
            ->map(fn ($kelompok) => $kelompok->map(fn (AlternatifStrategi $alternatif) => [
                'id' => $alternatif->id,
                'nama' => $alternatif->strategi->nama,
                'rencana' => self::susunRencana($alternatif),
                'respons_awal' => $alternatif->aturan?->respons_pemicu?->value,
            ])->values()->all())
            ->all();

        return view('guru.tindak-lanjut.create', [
            'daftarAnak' => $daftarAnak,
            'pilihanAlternatif' => $pilihanAlternatif,
            'anakTerpilih' => old('anak_id', $anakDariTautan?->id),
            'alternatifTerpilih' => old('alternatif_id', $request->query('alternatif')),
            'urlKembali' => $anakDariTautan
                ? route('guru.alternatif-strategi.anak', $anakDariTautan)
                : route('guru.alternatif-strategi.index'),
        ]);
    }

    public function store(SimpanTindakLanjutRequest $request): RedirectResponse
    {
        $data = $request->validated();

        $tindakLanjut = DB::transaction(function () use ($data, $request) {
            // Kunci baris alternatif agar dua permintaan bersamaan tidak membuat dua tindak lanjut.
            $alternatif = AlternatifStrategi::query()->with('strategi', 'anak')->lockForUpdate()->findOrFail($data['alternatif_id']);

            $pesan = SimpanTindakLanjutRequest::alasanTolak($alternatif, (int) $data['anak_id']);
            if ($pesan !== null) {
                throw ValidationException::withMessages(['alternatif_id' => $pesan]);
            }

            $tindakLanjut = TindakLanjut::create([
                'alternatif_id' => $alternatif->id,
                'rencana' => self::susunRencana($alternatif),
                'alasan_dugaan_penyebab' => $data['catatan'] ?? null,
                'tanggal_mulai' => $data['tanggal'],
                'status' => self::statusDariTanggal($data['tanggal']),
                'kategori_respons_awal' => $data['kategori_respons'],
            ]);

            // NFR-08: setiap tindak lanjut baru tercatat beserta pengguna dan waktunya.
            RiwayatPerubahan::create([
                'dilakukan_oleh' => $request->user()->id,
                'tindak_lanjut_id' => $tindakLanjut->id,
                'aksi' => 'tambah',
                'keterangan' => "Tindak lanjut dicatat untuk {$alternatif->anak->nama} dengan strategi {$alternatif->strategi->nama}.",
                'waktu' => now(),
            ]);

            return $tindakLanjut->setRelation('alternatif', $alternatif);
        });

        return redirect()
            ->route('guru.tindak-lanjut.riwayat')
            ->with('status', "Tindak lanjut untuk {$tindakLanjut->alternatif->anak->nama} berhasil dicatat.");
    }

    /**
     * Formulir ubah: hanya tanggal mulai dan catatan pendukung. Hak akses sama dengan update (NFR-04).
     */
    public function edit(Request $request, TindakLanjut $tindakLanjut): View|RedirectResponse
    {
        $tindakLanjut->load('alternatif.anak.kelas', 'alternatif.strategi');
        abort_unless($request->user()->can('kelola', $tindakLanjut->alternatif->anak), 403, 'Akses ditolak.');

        if ($tindakLanjut->terkunci()) {
            return $this->tolakTerkunci();
        }

        return view('guru.tindak-lanjut.ubah', ['tindakLanjut' => $tindakLanjut]);
    }

    /**
     * Strategi dan kategori respons awal tetap. Status dihitung ulang dari tanggal mulai.
     * Perubahan dicatat di riwayat_perubahan dengan aksi "ubah" (NFR-08).
     */
    public function update(UbahTindakLanjutRequest $request, TindakLanjut $tindakLanjut): RedirectResponse
    {
        $data = $request->validated();
        $namaAnak = $tindakLanjut->alternatif->anak->nama;

        $hasil = DB::transaction(function () use ($data, $request, $tindakLanjut) {
            // Kunci baris dan periksa ulang agar tidak berubah setelah evaluasi dicatat bersamaan.
            $terkini = TindakLanjut::query()->lockForUpdate()->findOrFail($tindakLanjut->id);
            if ($terkini->terkunci()) {
                return 'terkunci';
            }

            $sebelum = [
                'tanggal' => $terkini->tanggal_mulai?->format('d/m/Y'),
                'status' => $terkini->status,
                'catatan' => $terkini->alasan_dugaan_penyebab,
            ];

            $terkini->fill([
                'tanggal_mulai' => $data['tanggal'],
                'status' => self::statusDariTanggal($data['tanggal']),
                'alasan_dugaan_penyebab' => $data['catatan'] ?? null,
            ]);

            $perubahan = array_filter([
                $sebelum['tanggal'] !== Carbon::parse($data['tanggal'])->format('d/m/Y')
                    ? "Tanggal mulai {$sebelum['tanggal']} menjadi ".Carbon::parse($data['tanggal'])->format('d/m/Y') : null,
                $sebelum['status'] !== $terkini->status ? "Status {$sebelum['status']} menjadi {$terkini->status}" : null,
                $sebelum['catatan'] !== $terkini->alasan_dugaan_penyebab ? 'Catatan pendukung diubah' : null,
            ]);

            if ($perubahan === []) {
                return 'tetap';
            }

            $terkini->save();

            RiwayatPerubahan::create([
                'dilakukan_oleh' => $request->user()->id,
                'tindak_lanjut_id' => $terkini->id,
                'aksi' => 'ubah',
                'keterangan' => implode('; ', $perubahan).'.',
                'waktu' => now(),
            ]);

            return 'diubah';
        });

        return match ($hasil) {
            'terkunci' => $this->tolakTerkunci(),
            'tetap' => redirect()->route('guru.tindak-lanjut.riwayat')->with('status', "Tidak ada perubahan pada tindak lanjut untuk {$namaAnak}."),
            default => redirect()->route('guru.tindak-lanjut.riwayat')->with('status', "Tindak lanjut untuk {$namaAnak} berhasil diubah."),
        };
    }

    /**
     * Riwayat tindak lanjut. Hanya anak di kelas yang diampu guru (NFR-04),
     * termasuk anak yang sudah nonaktif karena riwayat tetap disimpan.
     */
    public function riwayat(Request $request): View
    {
        $daftarTindakLanjut = TindakLanjut::query()
            ->whereHas('alternatif.anak', fn ($query) => $query->whereIn('kelas_id', $request->user()->kelasDiampu()->select('id')))
            // Dipakai TindakLanjut::terkunci() tanpa query tambahan per baris.
            ->withExists(['observasi', 'evaluasi'])
            ->with([
                'alternatif.anak.kelas',
                'alternatif.strategi',
                'riwayatPerubahan' => fn ($query) => $query->whereIn('aksi', ['tambah', 'ubah'])->orderBy('id')->with('pengguna'),
            ])
            ->orderByDesc('id')
            ->paginate(self::PER_HALAMAN);

        return view('guru.tindak-lanjut.riwayat', [
            'daftarTindakLanjut' => $daftarTindakLanjut,
        ]);
    }

    private function tolakTerkunci(): RedirectResponse
    {
        return redirect()
            ->route('guru.tindak-lanjut.riwayat')
            ->with('gagal', 'Tindak lanjut ini tidak dapat diubah karena sudah memiliki observasi lanjutan, evaluasi, atau sudah selesai.');
    }

    /**
     * Status otomatis: direncanakan bila tanggal mulai di masa depan, berjalan bila hari ini atau sebelumnya.
     */
    private static function statusDariTanggal(string $tanggal): string
    {
        return Carbon::createFromFormat('Y-m-d', $tanggal)->startOfDay()->isAfter(Carbon::today())
            ? self::STATUS_DIRENCANAKAN
            : self::STATUS_BERJALAN;
    }

    /**
     * Rencana diisi otomatis dari nama dan deskripsi strategi.
     */
    private static function susunRencana(AlternatifStrategi $alternatif): string
    {
        $strategi = $alternatif->strategi;

        return filled($strategi->deskripsi) ? "{$strategi->nama}: {$strategi->deskripsi}" : $strategi->nama;
    }
}
