<?php

declare(strict_types=1);

use Filament\Contracts\Plugin;
use Filament\Panel;
use Illuminate\Support\ServiceProvider;
use Linkxtr\FilamentQr\FilamentQrPlugin;
use Linkxtr\FilamentQr\FilamentQrServiceProvider;
use Linkxtr\FilamentQr\Services\QrCodeRenderer;

it('implements filament plugin contract', function (): void {
    $filamentQrPlugin = FilamentQrPlugin::make();

    expect($filamentQrPlugin)->toBeInstanceOf(Plugin::class);
    expect($filamentQrPlugin->getId())->toBe('filament-qr');
});

it('registers in panel lifecycle without errors', function (): void {
    $filamentQrPlugin = FilamentQrPlugin::make();
    $panel = Panel::make();

    $filamentQrPlugin->register($panel);
    $filamentQrPlugin->boot($panel);

    expect($filamentQrPlugin->getId())->toBe('filament-qr');
});

it('binds qr code renderer as a singleton in the service container', function (): void {
    $qrCodeRenderer = app(QrCodeRenderer::class);
    $renderer2 = app(QrCodeRenderer::class);

    expect($qrCodeRenderer)->toBeInstanceOf(QrCodeRenderer::class);
    expect($qrCodeRenderer)->toBe($renderer2);
});

it('merges default package configuration', function (): void {
    expect(config('filament-qr.defaults.size'))->toBe(160);
    expect(config('filament-qr.defaults.format'))->toBe('svg');
    expect(config('filament-qr.defaults.error_correction'))->toBe('M');
});

it('registers publishable tags for configuration, views, and combined assets', function (): void {
    $publishes = ServiceProvider::$publishes[FilamentQrServiceProvider::class] ?? [];
    $groups = ServiceProvider::$publishGroups;

    expect($groups)->toHaveKey('filament-qr-config');
    expect($groups)->toHaveKey('filament-qr-views');
    expect($groups)->toHaveKey('filament-qr');
});
