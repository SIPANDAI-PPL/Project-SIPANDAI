<?php

namespace App\Http\Requests\Admin;

class UbahAturanStrategiRequest extends SimpanAturanStrategiRequest
{
    protected function idDiabaikan(): ?int
    {
        return $this->route('aturan')?->getKey();
    }
}
