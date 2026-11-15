<?php

namespace App\Http\Requests\Admin;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;
use Illuminate\Validation\Validator;

class SimpanKelasRequest extends FormRequest
{
    /**
     * Kelompok usia TK yang tersedia (kolom kelas.kelompok, maksimal 10 karakter).
     */
    public const KELOMPOK = ['TK A', 'TK B'];

    public const PESAN_TAHUN_AJARAN = 'Tahun ajaran harus berformat YYYY/YYYY dengan tahun berurutan, contoh 2026/2027.';

    public function authorize(): bool
    {
        return $this->user()?->peran === 'admin';
    }

    /**
     * ID kelas yang diabaikan saat cek duplikat. Null untuk kelas baru.
     */
    protected function idDiabaikan(): ?int
    {
        return null;
    }

    /**
     * Form tambah kelas sekaligus memilih guru pengampu; form ubah tidak.
     */
    protected function denganGuru(): bool
    {
        return true;
    }

    protected function prepareForValidation(): void
    {
        $this->merge([
            'nama_kelas' => is_string($this->input('nama_kelas')) ? trim($this->input('nama_kelas')) : $this->input('nama_kelas'),
            'tahun_ajaran' => is_string($this->input('tahun_ajaran')) ? trim($this->input('tahun_ajaran')) : $this->input('tahun_ajaran'),
        ]);
    }

    /**
     * @return array<string, list<mixed>>
     */
    public function rules(): array
    {
        $aturan = [
            'nama_kelas' => [
                'required',
                'string',
                'max:50',
                // Satu kombinasi nama kelas + kelompok + tahun ajaran hanya boleh ada sekali.
                Rule::unique('kelas', 'nama_kelas')
                    ->where('kelompok', (string) $this->input('kelompok'))
                    ->where('tahun_ajaran', (string) $this->input('tahun_ajaran'))
                    ->ignore($this->idDiabaikan()),
            ],
            'kelompok' => ['required', Rule::in(self::KELOMPOK)],
            'tahun_ajaran' => ['required', 'string', 'regex:/^\d{4}\/\d{4}$/'],
        ];

        if ($this->denganGuru()) {
            $aturan['guru_id'] = self::aturanGuru();
        }

        return $aturan;
    }

    /**
     * Hanya akun guru yang aktif yang boleh ditugaskan sebagai pengampu.
     *
     * @return list<mixed>
     */
    public static function aturanGuru(): array
    {
        return [
            'required',
            'integer',
            Rule::exists('users', 'id')->where('peran', 'guru')->where('aktif', true),
        ];
    }

    /**
     * Tahun kedua harus tepat satu tahun setelah tahun pertama.
     *
     * @return list<callable>
     */
    public function after(): array
    {
        return [
            function (Validator $validator) {
                $tahun = $this->input('tahun_ajaran');

                if ($validator->errors()->has('tahun_ajaran') || ! is_string($tahun)) {
                    return;
                }

                [$awal, $akhir] = array_map('intval', explode('/', $tahun));

                if ($akhir !== $awal + 1) {
                    $validator->errors()->add('tahun_ajaran', self::PESAN_TAHUN_AJARAN);
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
            'nama_kelas.required' => 'Nama kelas wajib diisi.',
            'nama_kelas.max' => 'Nama kelas maksimal 50 karakter.',
            'nama_kelas.unique' => 'Kelas dengan nama, kelompok, dan tahun ajaran ini sudah ada.',
            'kelompok.required' => 'Kelompok wajib dipilih.',
            'kelompok.in' => 'Kelompok tidak valid.',
            'tahun_ajaran.required' => 'Tahun ajaran wajib diisi.',
            'tahun_ajaran.regex' => self::PESAN_TAHUN_AJARAN,
            ...self::pesanGuru(),
            '*.string' => 'Isian tidak valid.',
        ];
    }

    /**
     * @return array<string, string>
     */
    public static function pesanGuru(): array
    {
        return [
            'guru_id.required' => 'Pilih satu guru pengampu.',
            'guru_id.integer' => 'Guru pengampu tidak valid atau sudah nonaktif.',
            'guru_id.exists' => 'Guru pengampu tidak valid atau sudah nonaktif.',
        ];
    }
}
