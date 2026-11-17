<?php

namespace App\Http\Requests\Guru;

use App\Models\Anak;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;
use Illuminate\Validation\Validator;

class SimpanAnakRequest extends FormRequest
{
    public const USIA_MIN_BULAN = 24;

    public const USIA_MAKS_BULAN = 59;

    public const PESAN_USIA = 'Usia anak harus 24–59 bulan.';

    public function authorize(): bool
    {
        return $this->user()?->peran === 'guru';
    }

    /**
     * Saat mengubah, batas usia hanya dicek jika tanggal lahir diganti,
     * supaya data anak yang sudah lewat 59 bulan tetap bisa diperbaiki.
     */
    protected function perluCekUsia(): bool
    {
        return true;
    }

    protected function prepareForValidation(): void
    {
        $rapikan = fn (string $kolom) => is_string($this->input($kolom)) ? trim($this->input($kolom)) : $this->input($kolom);

        $this->merge([
            'nama' => $rapikan('nama'),
            'nama_panggilan' => $rapikan('nama_panggilan') === '' ? null : $rapikan('nama_panggilan'),
        ]);
    }

    /**
     * @return array<string, list<mixed>>
     */
    public function rules(): array
    {
        return [
            'nama' => ['required', 'string', 'max:100'],
            'nama_panggilan' => ['nullable', 'string', 'max:30'],
            'tanggal_lahir' => ['required', 'date_format:Y-m-d', 'before_or_equal:today'],
            // NFR-04: guru hanya boleh memilih kelas yang diampunya.
            'kelas_id' => [
                'required',
                'integer',
                Rule::exists('kelas', 'id')->where('guru_id', $this->user()?->id),
            ],
        ];
    }

    /**
     * @return list<callable>
     */
    public function after(): array
    {
        return [
            function (Validator $validator) {
                if ($validator->errors()->has('tanggal_lahir') || ! $this->perluCekUsia()) {
                    return;
                }

                $usiaBulan = Anak::hitungUsiaBulan($this->input('tanggal_lahir'));

                if ($usiaBulan < self::USIA_MIN_BULAN || $usiaBulan > self::USIA_MAKS_BULAN) {
                    $validator->errors()->add('tanggal_lahir', self::PESAN_USIA);
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
            'nama.required' => 'Nama lengkap wajib diisi.',
            'nama.max' => 'Nama lengkap maksimal 100 karakter.',
            'nama_panggilan.max' => 'Nama panggilan maksimal 30 karakter.',
            'tanggal_lahir.required' => 'Tanggal lahir wajib diisi.',
            'tanggal_lahir.date_format' => 'Tanggal lahir tidak valid.',
            'tanggal_lahir.before_or_equal' => 'Tanggal lahir tidak valid.',
            'kelas_id.required' => 'Kelas wajib dipilih.',
            'kelas_id.integer' => 'Kelas tidak valid.',
            'kelas_id.exists' => 'Kelas tidak valid.',
            '*.string' => 'Isian tidak valid.',
        ];
    }

    /**
     * @return array{nama: string, nama_panggilan: ?string, tanggal_lahir: string, kelas_id: int}
     */
    public function dataAnak(): array
    {
        $data = $this->validated();

        return [
            'nama' => $data['nama'],
            'nama_panggilan' => $data['nama_panggilan'] ?? null,
            'tanggal_lahir' => $data['tanggal_lahir'],
            'kelas_id' => (int) $data['kelas_id'],
        ];
    }
}
