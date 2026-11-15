<?php

namespace App\Http\Requests\Admin;

use App\Models\Kelas;

class UbahKelasRequest extends SimpanKelasRequest
{
    protected function idDiabaikan(): ?int
    {
        $kelas = $this->route('kelas');

        return $kelas instanceof Kelas ? $kelas->id : null;
    }

    /**
     * Guru pengampu diganti lewat PATCH /classes/{kelas}/guru-pengampu.
     */
    protected function denganGuru(): bool
    {
        return false;
    }
}
