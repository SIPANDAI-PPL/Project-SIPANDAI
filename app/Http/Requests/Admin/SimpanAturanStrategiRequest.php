<?php

namespace App\Http\Requests\Admin;

use App\Enums\KategoriRespons;
use App\Models\AturanStrategi;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;
use Illuminate\Validation\Rules\Enum;
use Illuminate\Validation\Validator;

class SimpanAturanStrategiRequest extends FormRequest
{
    public const MINIMAL_KEJADIAN_BAWAAN = 3;

    public function authorize(): bool
    {
        return $this->user()?->peran === 'admin';
    }

    /**
     * ID aturan yang diabaikan saat cek duplikat. Null untuk aturan baru.
     */
    protected function idDiabaikan(): ?int
    {
        return null;
    }

    protected function prepareForValidation(): void
    {
        $minimal = $this->input('minimal_kejadian');

        $this->merge([
            'minimal_kejadian' => $minimal === null || $minimal === '' ? self::MINIMAL_KEJADIAN_BAWAAN : $minimal,
            // Checkbox yang tidak dicentang tidak terkirim; tanpa isian, aturan baru dianggap aktif.
            'aktif' => $this->has('aktif') ? $this->boolean('aktif') : true,
        ]);
    }

    /**
     * @return array<string, list<mixed>>
     */
    public function rules(): array
    {
        $strategiAktif = Rule::exists('strategi_pembelajaran', 'id')->where('aktif', true);

        return [
            'strategi_asal_id' => ['required', 'integer', $strategiAktif],
            'strategi_alternatif_id' => ['required', 'integer', 'different:strategi_asal_id', $strategiAktif],
            'respons_pemicu' => ['required', Rule::enum(KategoriRespons::class)],
            'minimal_kejadian' => ['required', 'integer', 'min:2', 'max:100'],
            'aspek_id' => ['nullable', 'integer', Rule::exists('aspek_perkembangan', 'id')],
            'keterangan' => ['nullable', 'string', 'max:1000'],
            'aktif' => ['boolean'],
        ];
    }

    /**
     * Kombinasi strategi asal + respons pemicu + aspek (termasuk "Semua aspek") tidak boleh dobel.
     *
     * @return list<callable>
     */
    public function after(): array
    {
        return [
            function (Validator $validator) {
                if ($validator->errors()->hasAny(['strategi_asal_id', 'respons_pemicu', 'aspek_id'])) {
                    return;
                }

                $aspekId = $this->input('aspek_id');

                $duplikat = AturanStrategi::query()
                    ->where('strategi_asal_id', $this->input('strategi_asal_id'))
                    ->where('respons_pemicu', $this->input('respons_pemicu'))
                    ->when(
                        $aspekId === null || $aspekId === '',
                        fn ($query) => $query->whereNull('aspek_id'),
                        fn ($query) => $query->where('aspek_id', $aspekId),
                    )
                    ->when($this->idDiabaikan(), fn ($query, $id) => $query->whereKeyNot($id))
                    ->exists();

                if ($duplikat) {
                    $validator->errors()->add('aturan', 'Aturan dengan strategi asal, respons pemicu, dan aspek yang sama sudah ada.');
                }
            },
        ];
    }

    /**
     * @return array<string, string>
     */
    public function messages(): array
    {
        $responsTidakValid = 'Respons pemicu tidak valid.';

        return [
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
        ];
    }
}
