<?php

namespace Tests;

use Facebook\WebDriver\Chrome\ChromeOptions;
use Facebook\WebDriver\Remote\DesiredCapabilities;
use Facebook\WebDriver\Remote\RemoteWebDriver;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Str;
use Laravel\Dusk\Browser;
use Laravel\Dusk\TestCase as BaseTestCase;
use PHPUnit\Framework\Attributes\BeforeClass;
use Tests\Browser\Support\BrowserBerjeda;

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
     * Test Dusk memakai database khusus (sipandai_dusk) yang tidak pernah dikosongkan,
     * supaya data hasil test bisa dicek setelah test selesai. Migration yang belum
     * dijalankan ditambahkan di sini; data yang sudah ada tidak dihapus.
     */
    protected function setUp(): void
    {
        parent::setUp();

        $database = (string) config('database.connections.'.config('database.default').'.database');

        if (! Str::endsWith($database, '_dusk')) {
            $this->fail(
                "Test Dusk harus memakai database khusus Dusk, bukan \"{$database}\". "
                .'Isi DB_DATABASE=sipandai_dusk di .env.dusk.local (lihat README bagian Dusk).'
            );
        }

        Artisan::call('migrate', ['--force' => true]);
    }

    /**
     * Create the RemoteWebDriver instance.
     *
     * Chrome tampil dengan GUI (ditonton lewat noVNC http://localhost:7900).
     * Mode tanpa tampilan hanya dipakai jika DUSK_HEADLESS=true di .env.dusk.local.
     */
    protected function driver(): RemoteWebDriver
    {
        $options = (new ChromeOptions)->addArguments(collect([
            $this->shouldStartMaximized() ? '--start-maximized' : '--window-size=1920,1080',
            '--disable-search-engine-choice-screen',
            '--disable-smooth-scrolling',
        ])->when($this->jalankanTanpaTampilan(), function (Collection $items) {
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

    /**
     * Browser dengan jeda sebelum setiap aksi (DUSK_SLOW_MS) agar gerakan test mudah diikuti.
     */
    protected function newBrowser($driver)
    {
        return new BrowserBerjeda($driver);
    }

    protected function jalankanTanpaTampilan(): bool
    {
        return filter_var(env('DUSK_HEADLESS', false), FILTER_VALIDATE_BOOLEAN);
    }

    /**
     * Dipertahankan sementara agar test PBI-3 yang memanggilnya tetap berjalan.
     * Jeda kini diatur oleh DUSK_SLOW_MS lewat BrowserBerjeda, jadi method ini
     * tidak menambah jeda lagi. Hapus setelah test PBI-3 disesuaikan dengan panduan.
     */
    protected function visualPause(Browser $browser, ?int $ms = null): Browser
    {
        return $browser;
    }
}