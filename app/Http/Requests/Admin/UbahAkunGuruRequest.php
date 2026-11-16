<?php

namespace App\Http\Requests\Admin;

use App\Models\User;

class UbahAkunGuruRequest extends SimpanAkunGuruRequest
{
    protected function idDiabaikan(): ?int
    {
        $guru = $this->route('guru');

        return $guru instanceof User ? $guru->id : null;
    }
}
