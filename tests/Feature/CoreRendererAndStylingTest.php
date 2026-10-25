<?php

declare(strict_types=1);

use Linkxtr\FilamentQr\Enums\ErrorCorrectionLevel;
use Linkxtr\FilamentQr\Enums\EyeStyle;
use Linkxtr\FilamentQr\Enums\GradientType;
use Linkxtr\FilamentQr\Enums\QrFormat;
use Linkxtr\FilamentQr\Enums\QrStyle;
use Linkxtr\FilamentQr\Services\QrCodeRenderer;
use Linkxtr\FilamentQr\Tables\Columns\QrCodeColumn;

beforeEach(function (): void {
    $this->renderer = app(QrCodeRenderer::class);
});

it('renders qr codes in all supported formats: svg, png, and webp', function (): void {
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

it('renders raw binary output using renderRaw for svg, png, and webp', function (): void {
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

it('embeds custom dpi and resolution settings into raster images', function (): void {
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

it('supports module styling, eye styles, and internal eye styles', function (): void {
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

it('supports fluent styling methods on filament qr column', function (): void {
    $qrCodeColumn = QrCodeColumn::make('test_qr')
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

    expect($qrCodeColumn->getQrSize())->toBe(250);
    expect($qrCodeColumn->getQrMargin())->toBe(3);
    expect($qrCodeColumn->getQrFormat())->toBe('webp');
    expect($qrCodeColumn->getQrErrorCorrection())->toBe('H');
    expect($qrCodeColumn->getQrColor())->toBe('#112233');
    expect($qrCodeColumn->getQrBackgroundColor())->toBe('#ffffff');
    expect($qrCodeColumn->getQrGradient())->toBe([
        'from' => '#ff0000',
        'to' => '#0000ff',
        'type' => 'diagonal',
    ]);
    expect($qrCodeColumn->getQrEyeColors()[0])->toBe([
        'inner' => '#ff0000',
        'outer' => '#00ff00',
    ]);
    expect($qrCodeColumn->getQrEyeStyle())->toBe('circle');
    expect($qrCodeColumn->getQrInternalEyeStyle())->toBe('square');
    expect($qrCodeColumn->getQrStyle())->toBe('round');
    expect($qrCodeColumn->getQrStyleSize())->toBe(0.7);
    expect($qrCodeColumn->getQrDpi())->toBe(300);
    expect($qrCodeColumn->getQrResolution())->toBe(300);
    expect($qrCodeColumn->getQrEncoding())->toBe('UTF-8');
});

it('supports format shortcuts svg, png, and webp', function (): void {
    $qrCodeColumn = QrCodeColumn::make('c1')->svg();
    expect($qrCodeColumn->getQrFormat())->toBe('svg');

    $colPng = QrCodeColumn::make('c2')->png();
    expect($colPng->getQrFormat())->toBe('png');

    $colWebp = QrCodeColumn::make('c3')->webp();
    expect($colWebp->getQrFormat())->toBe('webp');
});

it('evaluates closures on styling setters', function (): void {
    $qrCodeColumn = QrCodeColumn::make('dynamic')
        ->qrSize(fn (): int => 180)
        ->qrMargin(fn (): int => 2)
        ->qrColor(fn (): string => '#445566')
        ->qrDpi(fn (): int => 150);

    expect($qrCodeColumn->getQrSize())->toBe(180);
    expect($qrCodeColumn->getQrMargin())->toBe(2);
    expect($qrCodeColumn->getQrColor())->toBe('#445566');
    expect($qrCodeColumn->getQrDpi())->toBe(150);
});

it('resolves filament theme color tokens primary, success, and danger to hex colors', function (): void {
    $qrCodeColumn = QrCodeColumn::make('token_qr');

    $primaryHex = $qrCodeColumn->resolveColorToken('primary');
    $successHex = $qrCodeColumn->resolveColorToken('success');
    $dangerHex = $qrCodeColumn->resolveColorToken('danger');
    $warningHex = $qrCodeColumn->resolveColorToken('warning');
    $infoHex = $qrCodeColumn->resolveColorToken('info');
    $grayHex = $qrCodeColumn->resolveColorToken('gray');

    expect($primaryHex)->toStartWith('#');
    expect($successHex)->toStartWith('#');
    expect($dangerHex)->toStartWith('#');
    expect($warningHex)->toStartWith('#');
    expect($infoHex)->toStartWith('#');
    expect($grayHex)->toStartWith('#');
});

it('resolves color tokens with specific shade notation', function (): void {
    $qrCodeColumn = QrCodeColumn::make('shade_qr');

    $shadeHex = $qrCodeColumn->resolveColorToken('primary-600');
    expect($shadeHex)->toStartWith('#');
});

it('preserves existing hex and rgb colors during token resolution', function (): void {
    $qrCodeColumn = QrCodeColumn::make('hex_qr');

    expect($qrCodeColumn->resolveColorToken('#123456'))->toBe('#123456');
    expect($qrCodeColumn->resolveColorToken('#abc'))->toBe('#abc');
    expect($qrCodeColumn->resolveColorToken('rgb(10, 20, 30)'))->toBe('rgb(10, 20, 30)');
});
