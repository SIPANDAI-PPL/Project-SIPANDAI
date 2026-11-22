<?php

namespace App\Http\Requests\Guru;

use App\Models\TindakLanjut;
use Illuminate\Foundation\Http\FormRequest;

// Fitur: PBI-17 Pencatatan Tindak Lanjut (ubah tanggal mulai dan catatan pendukung)
class UbahTindakLanjutRequest extends FormRequest
{
    /**
     * Hanya guru pengampu kelas anak (NFR-04, AnakPolicy). Guru lain dan Admin ditolak (403).
     */
    public function authorize(): bool
    {
        $tindakLanjut = $this->route('tindakLanjut');
        $anak = $tindakLanjut instanceof TindakLanjut ? $tindakLanjut->alternatif?->anak : null;

        return $anak !== null && $this->user()->can('kelola', $anak);
    }

    /**
     * Strategi, kategori respons awal, dan status tidak termasuk: status dihitung ulang dari tanggal.
     *
     * @return array<string, list<string>>
     */
    public function rules(): array
    {
        return [
            'tanggal' => ['required', 'date_format:Y-m-d'],
            'catatan' => ['nullable', 'string', 'max:1000'],
        ];
    }

    /**
     * @return array<string, string>
     */
    public function messages(): array
    {
        return [
            'tanggal.required' => 'Tanggal wajib diisi.',
            'tanggal.date_format' => 'Tanggal tidak valid.',
            'catatan.string' => 'Catatan pendukung tidak valid.',
            'catatan.max' => 'Catatan pendukung maksimal 1000 karakter.',
        ];
    }
}
