<?php

use Linkxtr\FilamentQr\Services\QrCodeRenderer;

it('renders raw svg qr code', function () {
    /** @var QrCodeRenderer $renderer */
    $renderer = app(QrCodeRenderer::class);

    $output = $renderer->renderRaw('https://linkxtr.com', size: 200, format: 'svg');

    expect($output)
        ->toBeString()
        ->toContain('<svg');
});

it('renders base64 data uri', function () {
    /** @var QrCodeRenderer $renderer */
    $renderer = app(QrCodeRenderer::class);

    $dataUri = $renderer->renderDataUri('Hello Data URI', size: 150, format: 'svg');

    expect($dataUri)
        ->toBeString()
        ->toStartWith('data:image/svg+xml;base64,');
});

it('converts hex to rgb triplets accurately', function () {
    /** @var QrCodeRenderer $renderer */
    $renderer = app(QrCodeRenderer::class);

    expect($renderer->hexToRgb('#000000'))->toBe([0, 0, 0])
        ->and($renderer->hexToRgb('#ffffff'))->toBe([255, 255, 255])
        ->and($renderer->hexToRgb('#ff0000'))->toBe([255, 0, 0]);
});
