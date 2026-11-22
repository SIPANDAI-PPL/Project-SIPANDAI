<?php

namespace App\Http\Requests\Guru;

use App\Enums\KategoriRespons;
use App\Models\AlternatifStrategi;
use App\Models\Anak;
use App\Services\AlternatifStrategiService;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;
use Illuminate\Validation\Validator;

// Fitur: PBI-17 Pencatatan Tindak Lanjut
class SimpanTindakLanjutRequest extends FormRequest
{
    public const PESAN_BUKAN_MILIK_ANAK = 'Alternatif strategi bukan milik anak yang dipilih.';

    public const PESAN_BELUM_DIPILIH = 'Alternatif strategi harus berstatus dipilih.';

    public const PESAN_SUDAH_ADA = 'Alternatif strategi ini sudah punya tindak lanjut.';

    /**
     * Guru hanya boleh mencatat untuk anak di kelas yang diampunya (NFR-04, AnakPolicy).
     * Anak yang tidak ada dibiarkan lolos ke sini agar ditolak oleh aturan validasi.
     */
    public function authorize(): bool
    {
        $anak = ctype_digit((string) $this->input('anak_id'))
            ? Anak::query()->find($this->input('anak_id'))
            : null;

        return $anak === null || $this->user()->can('kelola', $anak);
    }

    /**
     * @return array<string, list<mixed>>
     */
    public function rules(): array
    {
        return [
            'anak_id' => ['required', 'integer', Rule::exists('anak', 'id')->where('aktif', true)],
            'alternatif_id' => ['required', 'integer', Rule::exists('alternatif_strategi', 'id')],
            'kategori_respons' => ['required', Rule::enum(KategoriRespons::class)],
            'tanggal' => ['required', 'date_format:Y-m-d'],
            'catatan' => ['nullable', 'string', 'max:1000'],
        ];
    }

    /**
     * Alternatif harus milik anak terpilih, berstatus dipilih, dan belum punya tindak lanjut.
     *
     * @return array<int, callable>
     */
    public function after(): array
    {
        return [
            function (Validator $validator) {
                if ($validator->errors()->hasAny(['anak_id', 'alternatif_id'])) {
                    return;
                }

                $pesan = self::alasanTolak(
                    AlternatifStrategi::query()->findOrFail($this->input('alternatif_id')),
                    (int) $this->input('anak_id'),
                );

                if ($pesan !== null) {
                    $validator->errors()->add('alternatif_id', $pesan);
                }
            },
        ];
    }

    /**
     * Dipakai juga oleh controller saat baris alternatif sudah dikunci.
     */
    public static function alasanTolak(AlternatifStrategi $alternatif, int $anakId): ?string
    {
        return match (true) {
            (int) $alternatif->anak_id !== $anakId => self::PESAN_BUKAN_MILIK_ANAK,
            $alternatif->status !== AlternatifStrategiService::STATUS_DIPILIH => self::PESAN_BELUM_DIPILIH,
            $alternatif->tindakLanjut()->exists() => self::PESAN_SUDAH_ADA,
            default => null,
        };
    }

    /**
     * @return array<string, string>
     */
    public function messages(): array
    {
        return [
            'anak_id.required' => 'Anak wajib dipilih.',
            'anak_id.integer' => 'Anak tidak valid.',
            'anak_id.exists' => 'Anak tidak ditemukan atau sudah nonaktif.',
            'alternatif_id.required' => 'Alternatif strategi wajib dipilih.',
            'alternatif_id.integer' => 'Alternatif strategi tidak valid.',
            'alternatif_id.exists' => 'Alternatif strategi tidak ditemukan.',
            'kategori_respons.required' => 'Kategori respons wajib dipilih.',
            'kategori_respons.enum' => 'Kategori respons tidak valid.',
            'tanggal.required' => 'Tanggal wajib diisi.',
            'tanggal.date_format' => 'Tanggal tidak valid.',
            'catatan.string' => 'Catatan pendukung tidak valid.',
            'catatan.max' => 'Catatan pendukung maksimal 1000 karakter.',
        ];
    }
}
