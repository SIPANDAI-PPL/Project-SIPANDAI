<?php

namespace App\Http\Requests\Auth;

use Closure;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Support\Facades\Hash;

class GantiKataSandiRequest extends FormRequest
{
    public function authorize(): bool
    {
        // Halaman ini hanya untuk mengganti kata sandi sementara.
        return (bool) $this->user()?->wajib_ganti_sandi;
    }

    /**
     * @return array<string, list<mixed>>
     */
    public function rules(): array
    {
        return [
            'kata_sandi_baru' => [
                'required',
                'string',
                'min:8',
                'confirmed',
                function (string $attribute, mixed $value, Closure $fail): void {
                    if (Hash::check((string) $value, $this->user()->getAuthPassword())) {
                        $fail('Kata sandi baru harus berbeda dari kata sandi sementara.');
                    }
                },
            ],
        ];
    }

    /**
     * @return array<string, string>
     */
    public function messages(): array
    {
        return [
            'kata_sandi_baru.required' => 'Kata sandi baru wajib diisi.',
            'kata_sandi_baru.string' => 'Kata sandi baru tidak valid.',
            'kata_sandi_baru.min' => 'Kata sandi baru minimal 8 karakter.',
            'kata_sandi_baru.confirmed' => 'Konfirmasi kata sandi baru tidak cocok.',
        ];
    }
}
