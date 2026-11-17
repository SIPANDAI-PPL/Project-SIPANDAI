<?php

namespace Tests;

use Facebook\WebDriver\Chrome\ChromeOptions;
use Facebook\WebDriver\Remote\DesiredCapabilities;
use Facebook\WebDriver\Remote\RemoteWebDriver;
use Illuminate\Support\Collection;
use Laravel\Dusk\Browser;
use Laravel\Dusk\TestCase as BaseTestCase;
use PHPUnit\Framework\Attributes\BeforeClass;

abstract class DuskTestCase extends BaseTestCase
{
    /**
     * Prepare for Dusk test execution.
     */
    #[BeforeClass]
    public static function prepare(): void
    {
        if (! static::runningInSail()) {
            static::startChromeDriver(['--port=9515']);
        }
    }

    /**
     * Jeda singkat agar alur test mudah diikuti di layar.
     *
     * Hanya aktif saat mode GUI (`php artisan dusk --browse` atau DUSK_HEADLESS_DISABLED).
     * Di mode headless (CI) jeda dilewati supaya test tetap cepat.
     * Lama jeda bawaan bisa diubah lewat variabel lingkungan DUSK_JEDA_MS.
     */
    protected function visualPause(Browser $browser, ?int $ms = null): Browser
    {
        if (! $this->hasHeadlessDisabled()) {
            return $browser;
        }

        $ms ??= (int) ($_SERVER['DUSK_JEDA_MS'] ?? $_ENV['DUSK_JEDA_MS'] ?? 800);

        return $browser->pause($ms);
    }

    /**
     * Create the RemoteWebDriver instance.
     */
    protected function driver(): RemoteWebDriver
    {
        $options = (new ChromeOptions)->addArguments(collect([
            $this->shouldStartMaximized() ? '--start-maximized' : '--window-size=1920,1080',
            '--disable-search-engine-choice-screen',
            '--disable-smooth-scrolling',
        ])->unless($this->hasHeadlessDisabled(), function (Collection $items) {
            return $items->merge([
                '--disable-gpu',
                '--headless=new',
            ]);
        })->all());

        return RemoteWebDriver::create(
            $_ENV['DUSK_DRIVER_URL'] ?? env('DUSK_DRIVER_URL') ?? 'http://localhost:9515',
            DesiredCapabilities::chrome()->setCapability(
                ChromeOptions::CAPABILITY, $options
            )
        );
    }
}
