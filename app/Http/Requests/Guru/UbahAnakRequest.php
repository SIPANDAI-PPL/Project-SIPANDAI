<?php

namespace App\Http\Requests\Guru;

use App\Models\Anak;

class UbahAnakRequest extends SimpanAnakRequest
{
    /**
     * NFR-04: hanya guru pengampu kelas anak ini yang boleh mengubah.
     */
    public function authorize(): bool
    {
        $anak = $this->route('anak');

        return $anak instanceof Anak
            && $this->user()?->can('kelola', $anak) === true;
    }

    protected function perluCekUsia(): bool
    {
        $anak = $this->route('anak');

        if (! $anak instanceof Anak) {
            return true;
        }

        return $anak->tanggal_lahir?->format('Y-m-d') !== $this->input('tanggal_lahir');
    }
}
