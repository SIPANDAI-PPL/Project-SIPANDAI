<?php

namespace Tests\Browser\Support;

use Laravel\Dusk\Browser;

/**
 * Browser Dusk dengan jeda sebelum setiap aksi, supaya jalannya test bisa diikuti
 * saat ditonton lewat noVNC. Lama jeda diatur dengan DUSK_SLOW_MS di .env.dusk.local
 * (milidetik, bawaan 0 = tanpa jeda).
 */
class BrowserBerjeda extends Browser
{
    protected function jeda(): void
    {
        $milidetik = (int) env('DUSK_SLOW_MS', 0);

        if ($milidetik > 0) {
            usleep($milidetik * 1000);
        }
    }

    public function visit($url)
    {
        $this->jeda();

        return parent::visit($url);
    }

    public function click($selector = null)
    {
        $this->jeda();

        return parent::click($selector);
    }

    public function clickLink($link, $element = 'a')
    {
        $this->jeda();

        return parent::clickLink($link, $element);
    }

    public function press($button)
    {
        $this->jeda();

        return parent::press($button);
    }

    public function type($field, $value)
    {
        $this->jeda();

        return parent::type($field, $value);
    }

    public function select($field, $value = null)
    {
        $this->jeda();

        return parent::select($field, $value);
    }

    public function check($field, $value = null)
    {
        $this->jeda();

        return parent::check($field, $value);
    }

    public function radio($field, $value)
    {
        $this->jeda();

        return parent::radio($field, $value);
    }
}
