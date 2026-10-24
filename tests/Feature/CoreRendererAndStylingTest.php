<?php

use Linkxtr\FilamentQr\Enums\ErrorCorrectionLevel;
use Linkxtr\FilamentQr\Enums\EyeStyle;
use Linkxtr\FilamentQr\Enums\GradientType;
use Linkxtr\FilamentQr\Enums\QrFormat;
use Linkxtr\FilamentQr\Enums\QrStyle;
use Linkxtr\FilamentQr\Services\QrCodeRenderer;
use Linkxtr\FilamentQr\Tables\Columns\QrCodeColumn;

beforeEach(function () {
    $this->renderer = app(QrCodeRenderer::class);
});

it('renders qr codes in all supported formats: svg, png, and webp', function () {
    $svg = $this->renderer->render('https://filamentphp.com', format: 'svg');
    expect($svg)->toBeString()
        ->and($svg)->toContain('<svg')
        ->and($svg)->toContain('</svg>');

    $png = $this->renderer->render('https://filamentphp.com', format: 'png');
    expect($png)->toBeString()
        ->and($png)->toStartWith('data:image/png;base64,');

    $webp = $this->renderer->render('https://filamentphp.com', format: 'webp');
    expect($webp)->toBeString()
        ->and($webp)->toStartWith('data:image/webp;base64,');
});

it('renders raw binary output using renderRaw for svg, png, and webp', function () {
    $rawSvg = $this->renderer->renderRaw('https://filamentphp.com', format: 'svg');
    expect($rawSvg)->toBeString()
        ->and($rawSvg)->toContain('<svg');

    $rawPng = $this->renderer->renderRaw('https://filamentphp.com', format: 'png');
    expect($rawPng)->toBeString()
        ->and(substr($rawPng, 0, 8))->toBe("\x89PNG\r\n\x1a\n");

    $rawWebp = $this->renderer->renderRaw('https://filamentphp.com', format: 'webp');
    expect($rawWebp)->toBeString()
        ->and(substr($rawWebp, 0, 4))->toBe('RIFF')
        ->and(substr($rawWebp, 8, 4))->toBe('WEBP');
});

it('embeds custom dpi and resolution settings into raster images', function () {
    if (! extension_loaded('imagick')) {
        $this->markTestSkipped('Imagick extension not available.');
    }

    $rawPng = $this->renderer->renderRaw('https://filamentphp.com', format: 'png', dpi: 300);

    $imagick = new Imagick;
    $imagick->readImageBlob($rawPng);
    $res = $imagick->getImageResolution();

    // 300 dpi translates to approx 118.11 dots/cm
    expect($res['x'])->toBeGreaterThan(100);
});

it('supports module styling, eye styles, and internal eye styles', function () {
    $styledSvg = $this->renderer->render(
        payload: 'https://filamentphp.com',
        format: 'svg',
        eyeStyle: EyeStyle::Circle,
        internalEyeStyle: EyeStyle::Pointy,
        style: QrStyle::Dot,
        styleSize: 0.6
    );

    expect($styledSvg)->toBeString()
        ->and($styledSvg)->toContain('<svg');
});

it('supports fluent styling methods on filament qr column', function () {
    $column = QrCodeColumn::make('test_qr')
        ->qrSize(250)
        ->qrMargin(3)
        ->qrFormat(QrFormat::Webp)
        ->qrErrorCorrection(ErrorCorrectionLevel::High)
        ->qrColor('#112233')
        ->qrBackgroundColor('#ffffff')
        ->qrGradient('#ff0000', '#0000ff', GradientType::Diagonal)
        ->qrEyeColor(0, '#ff0000', '#00ff00')
        ->qrEyeStyle(EyeStyle::Circle)
        ->qrInternalEyeStyle(EyeStyle::Square)
        ->qrStyle(QrStyle::Round, 0.7)
        ->qrDpi(300)
        ->qrResolution(300)
        ->qrEncoding('UTF-8');

    expect($column->getQrSize())->toBe(250);
    expect($column->getQrMargin())->toBe(3);
    expect($column->getQrFormat())->toBe('webp');
    expect($column->getQrErrorCorrection())->toBe('H');
    expect($column->getQrColor())->toBe('#112233');
    expect($column->getQrBackgroundColor())->toBe('#ffffff');
    expect($column->getQrGradient())->toBe([
        'from' => '#ff0000',
        'to' => '#0000ff',
        'type' => 'diagonal',
    ]);
    expect($column->getQrEyeColors()[0])->toBe([
        'inner' => '#ff0000',
        'outer' => '#00ff00',
    ]);
    expect($column->getQrEyeStyle())->toBe('circle');
    expect($column->getQrInternalEyeStyle())->toBe('square');
    expect($column->getQrStyle())->toBe('round');
    expect($column->getQrStyleSize())->toBe(0.7);
    expect($column->getQrDpi())->toBe(300);
    expect($column->getQrResolution())->toBe(300);
    expect($column->getQrEncoding())->toBe('UTF-8');
});

it('supports format shortcuts svg, png, and webp', function () {
    $colSvg = QrCodeColumn::make('c1')->svg();
    expect($colSvg->getQrFormat())->toBe('svg');

    $colPng = QrCodeColumn::make('c2')->png();
    expect($colPng->getQrFormat())->toBe('png');

    $colWebp = QrCodeColumn::make('c3')->webp();
    expect($colWebp->getQrFormat())->toBe('webp');
});

it('evaluates closures on styling setters', function () {
    $column = QrCodeColumn::make('dynamic')
        ->qrSize(fn () => 180)
        ->qrMargin(fn () => 2)
        ->qrColor(fn () => '#445566')
        ->qrDpi(fn () => 150);

    expect($column->getQrSize())->toBe(180);
    expect($column->getQrMargin())->toBe(2);
    expect($column->getQrColor())->toBe('#445566');
    expect($column->getQrDpi())->toBe(150);
});

it('resolves filament theme color tokens primary, success, and danger to hex colors', function () {
    $column = QrCodeColumn::make('token_qr');

    $primaryHex = $column->resolveColorToken('primary');
    $successHex = $column->resolveColorToken('success');
    $dangerHex = $column->resolveColorToken('danger');
    $warningHex = $column->resolveColorToken('warning');
    $infoHex = $column->resolveColorToken('info');
    $grayHex = $column->resolveColorToken('gray');

    expect($primaryHex)->toStartWith('#');
    expect($successHex)->toStartWith('#');
    expect($dangerHex)->toStartWith('#');
    expect($warningHex)->toStartWith('#');
    expect($infoHex)->toStartWith('#');
    expect($grayHex)->toStartWith('#');
});

it('resolves color tokens with specific shade notation', function () {
    $column = QrCodeColumn::make('shade_qr');

    $shadeHex = $column->resolveColorToken('primary-600');
    expect($shadeHex)->toStartWith('#');
});

it('preserves existing hex and rgb colors during token resolution', function () {
    $column = QrCodeColumn::make('hex_qr');

    expect($column->resolveColorToken('#123456'))->toBe('#123456');
    expect($column->resolveColorToken('#abc'))->toBe('#abc');
    expect($column->resolveColorToken('rgb(10, 20, 30)'))->toBe('rgb(10, 20, 30)');
});
