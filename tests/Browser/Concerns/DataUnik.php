<?php

namespace Tests\Browser\Concerns;

use Illuminate\Support\Str;

/**
 * Data uji Dusk tidak dihapus setelah test, jadi nilai yang harus unik
 * (email, nama pengguna, nama kelas, nama aktivitas, dll.) diberi kode acak
 * agar test yang sama bisa dijalankan berulang kali.
 */
trait DataUnik
{
    private ?string $kodeUnikTest = null;

    /**
     * Kode acak 5 karakter (huruf kecil dan angka), sama selama satu fungsi test.
     */
    protected function kodeUnik(): string
    {
        return $this->kodeUnikTest ??= Str::lower(Str::random(5));
    }
}
