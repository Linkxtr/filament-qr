<?php

declare(strict_types=1);

use Linkxtr\FilamentQr\Services\QrCodeRenderer;

beforeEach(function (): void {
    $this->renderer = app(QrCodeRenderer::class);
});

it('renders inline svg by default', function (): void {
    $svg = $this->renderer->render('https://example.com');

    expect($svg)->toBeString()
        ->and($svg)->toContain('<svg')
        ->and($svg)->toContain('</svg>');
});

it('renders raw svg string identical to render', function (): void {
    $raw = $this->renderer->renderRaw('https://example.com', format: 'svg');
    $rendered = $this->renderer->render('https://example.com', format: 'svg');

    expect($raw)->toBe($rendered);
});

it('renders png as base64 data uri', function (): void {
    $png = $this->renderer->render('https://example.com', format: 'png');

    expect($png)->toBeString()
        ->and($png)->toStartWith('data:image/png;base64,');
});

it('renders raw png as binary string with png signature', function (): void {
    $rawPng = $this->renderer->renderRaw('https://example.com', format: 'png');

    expect($rawPng)->toBeString()
        ->and(substr($rawPng, 0, 8))->toBe("\x89PNG\r\n\x1a\n");
});

it('renders data uri for svg format when requested', function (): void {
    $dataUri = $this->renderer->renderDataUri('https://example.com', format: 'svg');

    expect($dataUri)->toBeString()
        ->and($dataUri)->toStartWith('data:image/svg+xml;base64,');
});

it('applies custom size and margin', function (): void {
    $svg = $this->renderer->render('https://example.com', size: 300, margin: 4);

    expect($svg)->toBeString()
        ->and($svg)->toContain('width="300"')
        ->and($svg)->toContain('height="300"');
});

it('applies custom foreground and background colors', function (): void {
    $svg = $this->renderer->render(
        payload: 'https://example.com',
        color: '#ff0000',
        backgroundColor: '#00ff00'
    );

    expect($svg)->toBeString()
        ->and($svg)->toMatch('/(#ff0000|rgb\(255,\s*0,\s*0\))/')
        ->and($svg)->toMatch('/(#00ff00|rgb\(0,\s*255,\s*0\))/');
});

it('applies gradients', function (): void {
    $svg = $this->renderer->render(
        payload: 'https://example.com',
        gradient: [
            'from' => '#ff0000',
            'to' => '#0000ff',
            'type' => 'vertical',
        ]
    );

    expect($svg)->toBeString()
        ->and($svg)->toContain('linearGradient');
});

it('applies eye colors', function (): void {
    $svg = $this->renderer->render(
        payload: 'https://example.com',
        eyeColors: [
            0 => ['inner' => '#ff0000', 'outer' => '#00ff00'],
        ]
    );

    expect($svg)->toBeString();
});

it('applies eye style', function (): void {
    $svg = $this->renderer->render(
        payload: 'https://example.com',
        eyeStyle: 'circle'
    );

    expect($svg)->toBeString();
});

it('converts 3-character and 6-character hex strings to rgb arrays', function (): void {
    expect($this->renderer->hexToRgb('#fff'))->toBe([255, 255, 255]);
    expect($this->renderer->hexToRgb('#000'))->toBe([0, 0, 0]);
    expect($this->renderer->hexToRgb('#112233'))->toBe([17, 34, 51]);
    expect($this->renderer->hexToRgb('ffffff'))->toBe([255, 255, 255]);
});
