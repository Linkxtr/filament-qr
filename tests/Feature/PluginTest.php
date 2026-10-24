<?php

use Filament\Contracts\Plugin;
use Filament\Panel;
use Illuminate\Support\ServiceProvider;
use Linkxtr\FilamentQr\FilamentQrPlugin;
use Linkxtr\FilamentQr\FilamentQrServiceProvider;
use Linkxtr\FilamentQr\Services\QrCodeRenderer;

it('implements filament plugin contract', function () {
    $plugin = FilamentQrPlugin::make();

    expect($plugin)->toBeInstanceOf(Plugin::class);
    expect($plugin->getId())->toBe('filament-qr');
});

it('registers in panel lifecycle without errors', function () {
    $plugin = FilamentQrPlugin::make();
    $panel = Panel::make();

    $plugin->register($panel);
    $plugin->boot($panel);

    expect($plugin->getId())->toBe('filament-qr');
});

it('binds qr code renderer as a singleton in the service container', function () {
    $renderer1 = app(QrCodeRenderer::class);
    $renderer2 = app(QrCodeRenderer::class);

    expect($renderer1)->toBeInstanceOf(QrCodeRenderer::class);
    expect($renderer1)->toBe($renderer2);
});

it('merges default package configuration', function () {
    expect(config('filament-qr.defaults.size'))->toBe(160);
    expect(config('filament-qr.defaults.format'))->toBe('svg');
    expect(config('filament-qr.defaults.error_correction'))->toBe('M');
});

it('registers publishable tags for configuration, views, and combined assets', function () {
    $publishes = ServiceProvider::$publishes[FilamentQrServiceProvider::class] ?? [];
    $groups = ServiceProvider::$publishGroups;

    expect($groups)->toHaveKey('filament-qr-config');
    expect($groups)->toHaveKey('filament-qr-views');
    expect($groups)->toHaveKey('filament-qr');
});
