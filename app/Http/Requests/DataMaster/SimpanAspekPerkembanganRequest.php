<?php

namespace App\Http\Requests\DataMaster;

use App\Models\AspekPerkembangan;
use Closure;
use Illuminate\Foundation\Http\FormRequest;

class SimpanAspekPerkembanganRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()?->peran === 'admin';
    }

    /**
     * ID aspek yang diabaikan saat cek nama unik. Null untuk aspek baru.
     */
    protected function idDiabaikan(): ?int
    {
        return null;
    }

    protected function prepareForValidation(): void
    {
        $this->merge([
            'nama' => is_string($this->input('nama')) ? trim($this->input('nama')) : $this->input('nama'),
            'deskripsi' => is_string($this->input('deskripsi')) ? trim($this->input('deskripsi')) : $this->input('deskripsi'),
        ]);
    }

    /**
     * @return array<string, list<mixed>>
     */
    public function rules(): array
    {
        return [
            'nama' => [
                'bail',
                'required',
                'string',
                'max:100',
                // Nama unik tanpa membedakan huruf besar/kecil ("Bahasa" sama dengan "bahasa").
                function (string $attribute, mixed $value, Closure $fail) {
                    $sudahAda = AspekPerkembangan::query()
                        ->whereRaw('LOWER(nama) = ?', [mb_strtolower($value)])
                        ->when($this->idDiabaikan(), fn ($query, $id) => $query->whereKeyNot($id))
                        ->exists();

                    if ($sudahAda) {
                        $fail('Nama aspek sudah terdaftar.');
                    }
                },
            ],
            'deskripsi' => ['nullable', 'string', 'max:1000'],
        ];
    }

    /**
     * @return array<string, string>
     */
    public function messages(): array
    {
        return [
            'nama.required' => 'Nama wajib diisi.',
            'nama.string' => 'Nama tidak valid.',
            'nama.max' => 'Nama maksimal 100 karakter.',
            'deskripsi.string' => 'Deskripsi tidak valid.',
            'deskripsi.max' => 'Deskripsi maksimal 1000 karakter.',
        ];
    }
}
