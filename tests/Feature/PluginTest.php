<?php

use Filament\Panel;
use Linkxtr\FilamentQr\FilamentQrPlugin;

it('creates plugin instance', function () {
    $plugin = FilamentQrPlugin::make();

    expect($plugin)->toBeInstanceOf(FilamentQrPlugin::class)
        ->and($plugin->getId())->toBe('filament-qr');
});

it('registers plugin inside filament panel without error', function () {
    $panel = Panel::make()
        ->id('admin')
        ->default();

    $plugin = FilamentQrPlugin::make();
    $plugin->register($panel);
    $plugin->boot($panel);

    expect($plugin->getId())->toBe('filament-qr');
});
