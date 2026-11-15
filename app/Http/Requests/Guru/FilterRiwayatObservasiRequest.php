<?php

namespace App\Http\Requests\Guru;

use App\Enums\KategoriRespons;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

/**
 * Filter riwayat observasi (PBI-11).
 * Semua filter opsional dan bisa dikombinasikan.
 */
class FilterRiwayatObservasiRequest extends FormRequest
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
            // 30/7 hari terakhir, semua periode, atau bulan tertentu (YYYY-MM).
            'periode' => ['nullable', 'regex:/^(\d{4}-\d{2}|30|7|semua)$/'],
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
            'periode.regex' => 'Pilihan periode tidak dikenal.',
            'aspek.exists' => 'Aspek perkembangan tidak ditemukan.',
            'aktivitas.exists' => 'Aktivitas tidak ditemukan.',
            'strategi.exists' => 'Strategi tidak ditemukan.',
            'respons.enum' => 'Kategori respons tidak dikenal.',
        ];
    }
}
