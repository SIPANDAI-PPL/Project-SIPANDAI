<?php

namespace App\Http\Requests\Guru;

use App\Models\AlternatifStrategi;
use App\Services\AlternatifStrategiService;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;
use Illuminate\Validation\Validator;

class UbahStatusAlternatifRequest extends FormRequest
{
    /**
     * Hanya guru pengampu kelas anak (NFR-04), dan hanya untuk anak aktif.
     */
    public function authorize(): bool
    {
        $alternatif = $this->route('alternatif');
        $pengguna = $this->user();
        $anak = $alternatif instanceof AlternatifStrategi ? $alternatif->anak : null;

        return $anak !== null
            && $anak->aktif
            && $pengguna?->peran === 'guru'
            && (int) $anak->kelas?->guru_id === (int) $pengguna->id;
    }

    /**
     * @return array<string, list<mixed>>
     */
    public function rules(): array
    {
        return [
            'status' => ['required', 'string', Rule::in([
                AlternatifStrategiService::STATUS_DIPILIH,
                AlternatifStrategiService::STATUS_DIABAIKAN,
                AlternatifStrategiService::STATUS_DISARANKAN,
            ])],
        ];
    }

    /**
     * Transisi status dicek di AlternatifStrategiService::alasanTolak.
     *
     * @return array<int, callable>
     */
    public function after(AlternatifStrategiService $layanan): array
    {
        return [
            function (Validator $validator) use ($layanan) {
                if ($validator->errors()->has('status')) {
                    return;
                }

                $alasan = $layanan->alasanTolak($this->route('alternatif'), (string) $this->input('status'));
                if ($alasan !== null) {
                    $validator->errors()->add('status', $alasan);
                }
            },
        ];
    }

    /**
     * @return array<string, string>
     */
    public function messages(): array
    {
        return [
            'status.required' => 'Status wajib diisi.',
            'status.string' => 'Status tidak valid.',
            'status.in' => 'Status hanya boleh dipilih, diabaikan, atau disarankan.',
        ];
    }
}
