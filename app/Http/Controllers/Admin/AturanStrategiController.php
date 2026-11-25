<?php

namespace App\Http\Controllers\Admin;

use App\Enums\KategoriRespons;
use App\Http\Controllers\Controller;
use App\Models\AspekPerkembangan;
use App\Models\AturanStrategi;
use App\Models\StrategiPembelajaran;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator as ValidatorFacade;
use Illuminate\Validation\Rule;
use Illuminate\Validation\Rules\Enum;
use Illuminate\Validation\Validator;
use Illuminate\View\View;

class AturanStrategiController extends Controller
{
    // Fitur: PBI-4 Kelola Aspek Perkembangan dan Aturan Alternatif Strategi

    private const STATUS = ['semua', 'aktif', 'nonaktif'];

    public const MINIMAL_KEJADIAN_BAWAAN = 3;

    public function index(Request $request): View
    {
        $cari = trim((string) $request->query('q', ''));
        $status = in_array($request->query('status'), self::STATUS, true) ? $request->query('status') : 'semua';
        $aspek = ctype_digit((string) $request->query('aspek', '')) ? (int) $request->query('aspek') : null;

        $daftarAturan = AturanStrategi::query()
            ->with(['aspek', 'strategiAsal', 'strategiAlternatif'])
            ->withCount('alternatifStrategi')
            ->when($aspek !== null, fn (Builder $query) => $query->where('aspek_id', $aspek))
            ->when($status !== 'semua', fn (Builder $query) => $query->where('aktif', $status === 'aktif'))
            ->when($cari !== '', function (Builder $query) use ($cari) {
                $pola = '%'.addcslashes($cari, '%_\\').'%';
                $query->where(function (Builder $query) use ($pola) {
                    $query->whereHas('strategiAsal', fn (Builder $query) => $query->where('nama', 'like', $pola))
                        ->orWhereHas('strategiAlternatif', fn (Builder $query) => $query->where('nama', 'like', $pola));
                });
            })
            ->orderBy('id')
            ->paginate(10)
            ->withQueryString();

        return view('admin.aturan-strategi.index', [
            'daftarAturan' => $daftarAturan,
            // Hanya strategi aktif yang boleh dipilih saat menyusun aturan.
            'pilihanStrategi' => StrategiPembelajaran::query()->where('aktif', true)->orderBy('nama')->get(['id', 'nama']),
            'pilihanAspek' => AspekPerkembangan::query()->orderBy('id')->get(['id', 'nama']),
            'pilihanRespons' => KategoriRespons::cases(),
            'cari' => $cari,
            'status' => $status,
            'aspek' => $aspek,
        ]);
    }

    public function store(Request $request): RedirectResponse
    {
        AturanStrategi::create($this->validasi($request));

        return redirect()
            ->route('admin.aturan-strategi.index')
            ->with('status', 'Aturan strategi berhasil ditambahkan.');
    }

    public function update(Request $request, AturanStrategi $aturan): RedirectResponse
    {
        $aturan->update($this->validasi($request, $aturan));

        return redirect()
            ->route('admin.aturan-strategi.index')
            ->with('status', 'Aturan strategi berhasil diperbarui.');
    }

    public function nonaktifkan(AturanStrategi $aturan): RedirectResponse
    {
        $aturan->update(['aktif' => false]);

        return redirect()
            ->route('admin.aturan-strategi.index')
            ->with('status', 'Aturan strategi berhasil dinonaktifkan.');
    }

    public function aktifkan(AturanStrategi $aturan): RedirectResponse
    {
        $aturan->update(['aktif' => true]);

        return redirect()
            ->route('admin.aturan-strategi.index')
            ->with('status', 'Aturan strategi berhasil diaktifkan kembali.');
    }

    public function destroy(AturanStrategi $aturan): RedirectResponse
    {
        $jumlahSaran = $aturan->alternatifStrategi()->count();

        // Aturan yang sudah menghasilkan saran tetap disimpan sebagai dasar riwayat saran.
        if ($jumlahSaran > 0) {
            return redirect()
                ->route('admin.aturan-strategi.index')
                ->withErrors([
                    'hapus' => "Aturan sudah menghasilkan {$jumlahSaran} saran dan hanya dapat dinonaktifkan.",
                ]);
        }

        $aturan->delete();

        return redirect()
            ->route('admin.aturan-strategi.index')
            ->with('status', 'Aturan strategi berhasil dihapus.');
    }

    /**
     * Validasi isian tambah dan ubah aturan strategi.
     * Saat mengubah, aturan yang sedang diubah diabaikan pada cek duplikat.
     *
     * @return array<string, mixed>
     */
    private function validasi(Request $request, ?AturanStrategi $aturan = null): array
    {
        $minimal = $request->input('minimal_kejadian');

        $request->merge([
            'minimal_kejadian' => $minimal === null || $minimal === '' ? self::MINIMAL_KEJADIAN_BAWAAN : $minimal,
            // Checkbox yang tidak dicentang tidak terkirim; tanpa isian, aturan baru dianggap aktif.
            'aktif' => $request->has('aktif') ? $request->boolean('aktif') : true,
        ]);

        $strategiAktif = Rule::exists('strategi_pembelajaran', 'id')->where('aktif', true);
        $responsTidakValid = 'Respons pemicu tidak valid.';

        $validator = ValidatorFacade::make($request->all(), [
            'strategi_asal_id' => ['required', 'integer', $strategiAktif],
            'strategi_alternatif_id' => ['required', 'integer', 'different:strategi_asal_id', $strategiAktif],
            'respons_pemicu' => ['required', Rule::enum(KategoriRespons::class)],
            'minimal_kejadian' => ['required', 'integer', 'min:2', 'max:100'],
            'aspek_id' => ['nullable', 'integer', Rule::exists('aspek_perkembangan', 'id')],
            'keterangan' => ['nullable', 'string', 'max:1000'],
            'aktif' => ['boolean'],
        ], [
            'strategi_asal_id.required' => 'Strategi asal wajib dipilih.',
            'strategi_asal_id.integer' => 'Strategi asal tidak valid.',
            'strategi_asal_id.exists' => 'Strategi asal tidak ditemukan atau sudah nonaktif.',
            'strategi_alternatif_id.required' => 'Strategi alternatif wajib dipilih.',
            'strategi_alternatif_id.integer' => 'Strategi alternatif tidak valid.',
            'strategi_alternatif_id.exists' => 'Strategi alternatif tidak ditemukan atau sudah nonaktif.',
            'strategi_alternatif_id.different' => 'Strategi asal dan strategi alternatif tidak boleh sama.',
            'respons_pemicu.required' => 'Respons pemicu wajib dipilih.',
            'respons_pemicu.enum' => $responsTidakValid,
            // Rule::enum adalah objek rule; Laravel mencari pesan kustomnya dengan nama class.
            'respons_pemicu.'.Enum::class => $responsTidakValid,
            'minimal_kejadian.required' => 'Minimal kejadian wajib diisi.',
            'minimal_kejadian.integer' => 'Minimal kejadian harus berupa angka bulat.',
            'minimal_kejadian.min' => 'Minimal kejadian paling sedikit 2 kali.',
            'minimal_kejadian.max' => 'Minimal kejadian paling banyak 100 kali.',
            'aspek_id.integer' => 'Aspek tidak valid.',
            'aspek_id.exists' => 'Aspek tidak ditemukan.',
            'keterangan.string' => 'Keterangan tidak valid.',
            'keterangan.max' => 'Keterangan maksimal 1000 karakter.',
            'aktif.boolean' => 'Status aktif tidak valid.',
        ]);

        // Kombinasi strategi asal + respons pemicu + aspek (termasuk "Semua aspek") tidak boleh dobel.
        $validator->after(function (Validator $validator) use ($request, $aturan) {
            if ($validator->errors()->hasAny(['strategi_asal_id', 'respons_pemicu', 'aspek_id'])) {
                return;
            }

            $aspekId = $request->input('aspek_id');

            $duplikat = AturanStrategi::query()
                ->where('strategi_asal_id', $request->input('strategi_asal_id'))
                ->where('respons_pemicu', $request->input('respons_pemicu'))
                ->when(
                    $aspekId === null || $aspekId === '',
                    fn ($query) => $query->whereNull('aspek_id'),
                    fn ($query) => $query->where('aspek_id', $aspekId),
                )
                ->when($aturan?->getKey(), fn ($query, $id) => $query->whereKeyNot($id))
                ->exists();

            if ($duplikat) {
                $validator->errors()->add('aturan', 'Aturan dengan strategi asal, respons pemicu, dan aspek yang sama sudah ada.');
            }
        });

        return $validator->validate();
    }
}
