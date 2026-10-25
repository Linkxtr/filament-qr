<?php

declare(strict_types=1);

namespace Linkxtr\FilamentQr;

use Illuminate\Support\ServiceProvider;
use Linkxtr\FilamentQr\Services\QrCodeRenderer;

final class FilamentQrServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        $this->mergeConfigFrom(__DIR__.'/../config/filament-qr.php', 'filament-qr');

        $this->app->singleton(QrCodeRenderer::class, fn (): QrCodeRenderer => new QrCodeRenderer);
    }

    public function boot(): void
    {
        $this->loadViewsFrom(__DIR__.'/../resources/views', 'filament-qr');

        if ($this->app->runningInConsole()) {
            $this->publishes([
                __DIR__.'/../config/filament-qr.php' => config_path('filament-qr.php'),
            ], 'filament-qr-config');

            $this->publishes([
                __DIR__.'/../resources/views' => resource_path('views/vendor/filament-qr'),
            ], 'filament-qr-views');

            $this->publishes([
                __DIR__.'/../config/filament-qr.php' => config_path('filament-qr.php'),
                __DIR__.'/../resources/views' => resource_path('views/vendor/filament-qr'),
            ], 'filament-qr');
        }
    }
}
