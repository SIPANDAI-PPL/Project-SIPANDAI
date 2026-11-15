<?php

namespace App\Http\Requests\Admin;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class SimpanAkunGuruRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()?->peran === 'admin';
    }

    /**
     * ID akun yang diabaikan saat cek unik. Null untuk akun baru.
     */
    protected function idDiabaikan(): ?int
    {
        return null;
    }

    /**
     * @return array<string, list<mixed>>
     */
    public function rules(): array
    {
        return [
            'nama' => ['required', 'string', 'max:100'],
            'nama_pengguna' => [
                'required',
                'string',
                'max:50',
                'regex:/^[A-Za-z0-9._-]+$/',
                Rule::unique('users', 'nama_pengguna')->ignore($this->idDiabaikan()),
            ],
            'email' => [
                'required',
                'string',
                'email',
                'max:100',
                Rule::unique('users', 'email')->ignore($this->idDiabaikan()),
            ],
            'nip' => ['nullable', 'string', 'max:30'],
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
            'nama_pengguna.required' => 'Nama pengguna wajib diisi.',
            'nama_pengguna.max' => 'Nama pengguna maksimal 50 karakter.',
            'nama_pengguna.regex' => 'Nama pengguna hanya boleh berisi huruf, angka, titik, garis bawah, atau tanda hubung.',
            'nama_pengguna.unique' => 'Nama pengguna sudah terdaftar.',
            'email.required' => 'Email wajib diisi.',
            'email.email' => 'Format email tidak valid.',
            'email.max' => 'Email maksimal 100 karakter.',
            'email.unique' => 'Email sudah terdaftar.',
            'nip.max' => 'NIP maksimal 30 karakter.',
            '*.string' => 'Isian tidak valid.',
        ];
    }
}
