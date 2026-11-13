<?php

namespace App\Http\Requests\Guru;

use App\Models\Anak;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class TambahAlternatifStrategiRequest extends FormRequest
{
    /**
     * Hanya guru pengampu kelas anak (NFR-04), dan hanya untuk anak aktif.
     */
    public function authorize(): bool
    {
        $anak = $this->route('anak');
        $pengguna = $this->user();

        return $anak instanceof Anak
            && $anak->aktif
            && $pengguna?->peran === 'guru'
            && (int) $anak->kelas?->guru_id === (int) $pengguna->id;
    }

    protected function prepareForValidation(): void
    {
        $catatan = trim((string) $this->input('catatan', ''));

        $this->merge(['catatan' => $catatan === '' ? null : $catatan]);
    }

    /**
     * @return array<string, list<mixed>>
     */
    public function rules(): array
    {
        return [
            'strategi_id' => ['required', 'integer', Rule::exists('strategi_pembelajaran', 'id')->where('aktif', true)],
            'catatan' => ['nullable', 'string', 'max:1000'],
        ];
    }

    /**
     * @return array<string, string>
     */
    public function messages(): array
    {
        return [
            'strategi_id.required' => 'Strategi wajib dipilih.',
            'strategi_id.integer' => 'Strategi tidak valid.',
            'strategi_id.exists' => 'Strategi tidak ditemukan atau sudah nonaktif.',
            'catatan.string' => 'Catatan tidak valid.',
            'catatan.max' => 'Catatan maksimal 1000 karakter.',
        ];
    }
}
