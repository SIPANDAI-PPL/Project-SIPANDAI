<?php

namespace App\Http\Requests;

use App\Enums\KategoriRespons;
use App\Models\Observasi;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Support\Arr;
use Illuminate\Validation\Rule;

class ObservasiRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true; // akses dibatasi middleware auth + peran:guru di route
    }

    public function rules(): array
    {
        /** @var Observasi|null $observasi */
        $observasi = $this->route('observasi'); // null saat tambah

        return [
            'kelas_id' => ['required', 'integer', Rule::exists('kelas', 'id')],
            'anak_id' => [
                'required', 'integer',
                Rule::exists('anak', 'id')->where(function ($q) use ($observasi) {
                    $q->where('kelas_id', $this->input('kelas_id'));
                    $q->where(fn ($w) => $w->where('aktif', true)->orWhere('id', $observasi?->anak_id));
                }),
            ],
            'tanggal' => ['required', 'date', 'before_or_equal:today'],
            'aktivitas_id' => [
                'required', 'integer',
                Rule::exists('aktivitas', 'id')->where(
                    fn ($q) => $q->where(fn ($w) => $w->where('aktif', true)->orWhere('id', $observasi?->aktivitas_id))
                ),
            ],
            'strategi_id' => [
                'required', 'integer',
                Rule::exists('strategi_pembelajaran', 'id')->where(
                    fn ($q) => $q->where(fn ($w) => $w->where('aktif', true)->orWhere('id', $observasi?->strategi_id))
                ),
            ],
            'aspek_id' => ['required', 'integer', Rule::exists('aspek_perkembangan', 'id')],
            'kategori_respons' => ['required', Rule::enum(KategoriRespons::class)],
            'catatan_pendukung' => ['nullable', 'string', 'max:2000'],
        ];
    }

    public function attributes(): array
    {
        return [
            'kelas_id' => 'kelas',
            'anak_id' => 'anak',
            'tanggal' => 'tanggal observasi',
            'aktivitas_id' => 'aktivitas',
            'strategi_id' => 'strategi pembelajaran',
            'aspek_id' => 'aspek perkembangan',
            'kategori_respons' => 'respons anak',
            'catatan_pendukung' => 'catatan pendukung',
        ];
    }

    public function messages(): array
    {
        return [
            'required' => ':attribute wajib diisi.',
            'exists' => ':attribute yang dipilih tidak valid.',
            'date' => ':attribute bukan tanggal yang valid.',
            'before_or_equal' => ':attribute tidak boleh melewati hari ini.',
            'max' => ':attribute maksimal :max karakter.',
            'kategori_respons.required' => 'Pilih salah satu respons anak.',
            'kategori_respons.Illuminate\Validation\Rules\Enum' => 'Respons anak yang dipilih tidak valid.',
        ];
    }

    /** Hanya kolom tabel observasi (kelas_id hanya pembantu di formulir). */
    public function dataObservasi(): array
    {
        return Arr::only($this->validated(), [
            'anak_id',
            'aktivitas_id',
            'strategi_id',
            'aspek_id',
            'tanggal',
            'kategori_respons',
            'catatan_pendukung',
        ]);
    }
}