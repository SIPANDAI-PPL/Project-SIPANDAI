<?php

namespace App\Http\Requests\Admin;

use Illuminate\Foundation\Http\FormRequest;

class TugaskanGuruPengampuRequest extends FormRequest
{
    /**
     * Bag terpisah agar galat ganti guru tidak tampil di form tambah kelas.
     *
     * @var string
     */
    protected $errorBag = 'penugasan';

    public function authorize(): bool
    {
        return $this->user()?->peran === 'admin';
    }

    /**
     * @return array<string, list<mixed>>
     */
    public function rules(): array
    {
        return [
            'guru_id' => SimpanKelasRequest::aturanGuru(),
        ];
    }

    /**
     * @return array<string, string>
     */
    public function messages(): array
    {
        return SimpanKelasRequest::pesanGuru();
    }
}
