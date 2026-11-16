<?php

namespace App\Http\Requests\Guru;

use App\Enums\KategoriRespons;
use App\Services\RingkasanPolaResponsService;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

/**
 * Filter ringkasan pola respons (PBI-12).
 * Semua filter opsional dan bisa dikombinasikan.
 */
class FilterPolaResponsRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    /**
     * @return array<string, mixed>
     */
    public function rules(): array
    {
        return [
            'periode' => ['nullable', Rule::in(array_keys(RingkasanPolaResponsService::PERIODE))],
            'aspek' => ['nullable', 'integer', 'exists:aspek_perkembangan,id'],
            'aktivitas' => ['nullable', 'integer', 'exists:aktivitas,id'],
            'strategi' => ['nullable', 'integer', 'exists:strategi_pembelajaran,id'],
            'respons' => ['nullable', Rule::enum(KategoriRespons::class)],
        ];
    }

    /**
     * @return array<string, string>
     */
    public function messages(): array
    {
        return [
            'periode.in' => 'Pilihan periode tidak dikenal.',
            'aspek.integer' => 'Aspek perkembangan tidak ditemukan.',
            'aspek.exists' => 'Aspek perkembangan tidak ditemukan.',
            'aktivitas.integer' => 'Aktivitas tidak ditemukan.',
            'aktivitas.exists' => 'Aktivitas tidak ditemukan.',
            'strategi.integer' => 'Strategi tidak ditemukan.',
            'strategi.exists' => 'Strategi tidak ditemukan.',
            'respons.enum' => 'Kategori respons tidak dikenal.',
        ];
    }
}
