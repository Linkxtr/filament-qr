<?php

namespace Linkxtr\FilamentQr;

use Illuminate\Support\ServiceProvider;

class FilamentQrServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        $this->mergeConfigFrom(__DIR__ . '/../config/filament-qr.php', 'filament-qr');

        $this->app->singleton(Services\QrCodeRenderer::class, function () {
            return new Services\QrCodeRenderer();
        });
    }

    public function boot(): void
    {
        $this->loadViewsFrom(__DIR__ . '/../resources/views', 'filament-qr');

        if ($this->app->runningInConsole()) {
            $this->publishes([
                __DIR__ . '/../config/filament-qr.php' => config_path('filament-qr.php'),
            ], 'filament-qr-config');

            $this->publishes([
                __DIR__ . '/../resources/views' => resource_path('views/vendor/filament-qr'),
            ], 'filament-qr-views');
        }
    }
}
