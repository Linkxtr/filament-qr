<?php

namespace Linkxtr\FilamentQr;

use Filament\Contracts\Plugin;
use Filament\Panel;

class FilamentQrPlugin implements Plugin
{
    public static function make(): static
    {
        return app(static::class);
    }

    public static function get(): static
    {
        /** @var static $plugin */
        $plugin = filament(app(static::class)->getId());

        return $plugin;
    }

    public function getId(): string
    {
        return 'filament-qr';
    }

    public function register(Panel $panel): void
    {
        // Panel-level registrations
    }

    public function boot(Panel $panel): void
    {
        // Panel boot hooks
    }
}
