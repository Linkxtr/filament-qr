<?php

use Linkxtr\FilamentQr\Enums\ErrorCorrectionLevel;
use Linkxtr\FilamentQr\Enums\EyeStyle;
use Linkxtr\FilamentQr\Enums\GradientType;
use Linkxtr\FilamentQr\Enums\QrFormat;
use Linkxtr\FilamentQr\Enums\QrStyle;
use Linkxtr\FilamentQr\Services\QrCodeRenderer;
use Linkxtr\FilamentQr\Tables\Columns\QrCodeColumn;

it('supports all styling fluent methods', function () {
    $column = QrCodeColumn::make('qr')
        ->qrSize(250)
        ->qrMargin(4)
        ->qrColor('#123456')
        ->qrBackgroundColor('#abcdef')
        ->qrErrorCorrection(ErrorCorrectionLevel::High)
        ->qrFormat(QrFormat::Png)
        ->qrStyle(QrStyle::Dot, 0.8)
        ->qrEyeStyle(EyeStyle::Circle)
        ->qrInternalEyeStyle(EyeStyle::Rounded)
        ->qrDpi(300)
        ->qrResolution(1000)
        ->qrEncoding('ISO-8859-1');

    expect($column->getQrSize())->toBe(250)
        ->and($column->getQrMargin())->toBe(4)
        ->and($column->getQrColor())->toBe('#123456')
        ->and($column->getQrBackgroundColor())->toBe('#abcdef')
        ->and($column->getQrErrorCorrection())->toBe('H')
        ->and($column->getQrFormat())->toBe('png')
        ->and($column->getQrStyle())->toBe('dot')
        ->and($column->getQrStyleSize())->toBe(0.8)
        ->and($column->getQrEyeStyle())->toBe('circle')
        ->and($column->getQrInternalEyeStyle())->toBe('rounded')
        ->and($column->getQrDpi())->toBe(300)
        ->and($column->getQrResolution())->toBe(1000)
        ->and($column->getQrEncoding())->toBe('ISO-8859-1');
});

it('supports format shortcut methods', function () {
    $column = QrCodeColumn::make('qr');

    $column->svg();
    expect($column->getQrFormat())->toBe('svg');

    $column->png();
    expect($column->getQrFormat())->toBe('png');

    $column->webp();
    expect($column->getQrFormat())->toBe('webp');

    $column->eps();
    expect($column->getQrFormat())->toBe('eps');
});

it('supports linear gradient styling', function () {
    $column = QrCodeColumn::make('qr')
        ->qrGradient('#ff0000', '#0000ff', GradientType::Diagonal);

    $gradient = $column->getQrGradient();

    expect($gradient)->toBeArray()
        ->and($gradient['from'])->toBe('#ff0000')
        ->and($gradient['to'])->toBe('#0000ff')
        ->and($gradient['type'])->toBe('diagonal');
});

it('supports eye colors configuration', function () {
    $column = QrCodeColumn::make('qr')
        ->qrEyeColor(0, '#111111', '#222222')
        ->qrEyeColor(1, '#333333');

    $colors = $column->getQrEyeColors();

    expect($colors)->toBeArray()
        ->and($colors[0]['inner'])->toBe('#111111')
        ->and($colors[0]['outer'])->toBe('#222222')
        ->and($colors[1]['inner'])->toBe('#333333')
        ->and($colors[1]['outer'])->toBeNull();
});

it('supports uniform eye colors across all three corners', function () {
    $column = QrCodeColumn::make('qr')
        ->qrEyeColors('#444444', '#555555');

    $colors = $column->getQrEyeColors();

    expect($colors)->toHaveCount(3)
        ->and($colors[0]['inner'])->toBe('#444444')
        ->and($colors[0]['outer'])->toBe('#555555')
        ->and($colors[2]['inner'])->toBe('#444444');
});

it('renders with gradient and eyes without throwing', function () {
    $renderer = app(QrCodeRenderer::class);

    $svg = $renderer->render(
        payload: 'Test Gradient',
        gradient: ['from' => '#ff0000', 'to' => '#00ff00', 'type' => 'vertical'],
        eyeColors: [
            0 => ['inner' => '#000000', 'outer' => '#333333'],
            1 => ['inner' => '#000000', 'outer' => '#333333'],
            2 => ['inner' => '#000000', 'outer' => '#333333'],
        ],
    );

    expect($svg)->toBeString()->toContain('data:image/svg+xml;base64,');
});
