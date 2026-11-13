<?php

namespace App\Http\Requests\DataMaster;

class UbahAspekPerkembanganRequest extends SimpanAspekPerkembanganRequest
{
    protected function idDiabaikan(): ?int
    {
        return $this->route('aspek')?->getKey();
    }
}
