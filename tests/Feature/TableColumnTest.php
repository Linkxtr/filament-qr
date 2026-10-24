<?php

use Linkxtr\FilamentQr\Enums\ErrorCorrectionLevel;
use Linkxtr\FilamentQr\Enums\QrFormat;
use Linkxtr\FilamentQr\Tables\Columns\QrCodeColumn;

it('can initialize qr code column with styling', function () {
    $column = QrCodeColumn::make('qr')
        ->qrSize(200)
        ->qrMargin(2)
        ->qrColor('#112233')
        ->qrBackgroundColor('#ffffff')
        ->qrFormat(QrFormat::Png)
        ->qrErrorCorrection(ErrorCorrectionLevel::High);

    expect($column->getQrSize())->toBe(200);
    expect($column->getQrMargin())->toBe(2);
    expect($column->getQrColor())->toBe('#112233');
    expect($column->getQrBackgroundColor())->toBe('#ffffff');
    expect($column->getQrFormat())->toBe('png');
    expect($column->getQrErrorCorrection())->toBe('H');
});

it('formats payload as wifi', function () {
    $column = QrCodeColumn::make('wifi')
        ->asWifi('Office-Guest', 'secret123', 'WPA');

    expect($column->getQrPayload())->toBe('WIFI:S:Office-Guest;T:WPA;P:secret123;H:false;;');
});

it('formats payload as url, email, and sms', function () {
    $urlColumn = QrCodeColumn::make('url')->asUrl('https://example.com');
    expect($urlColumn->getQrPayload())->toBe('https://example.com');

    $emailColumn = QrCodeColumn::make('email')->asEmail('test@example.com', 'Hello', 'World');
    expect($emailColumn->getQrPayload())->toBe('mailto:test@example.com?subject=Hello&body=World');

    $smsColumn = QrCodeColumn::make('sms')->asSms('+123456789', 'Hi there');
    expect($smsColumn->getQrPayload())->toBe('smsto:+123456789:Hi there');
});

it('supports previewable and downloadable configurations', function () {
    $column = QrCodeColumn::make('qr')
        ->previewable(false)
        ->downloadable(true);

    expect($column->isPreviewable())->toBeFalse();
    expect($column->isDownloadable())->toBeTrue();

    $column->previewable(true);
    expect($column->isPreviewable())->toBeTrue();
});

it('renders qr code correctly', function () {
    $column = QrCodeColumn::make('qr')
        ->payload(fn () => 'https://linkxtr.com');

    $rendered = $column->renderQrCode();
    expect($rendered)->toBeString()
        ->and($rendered)->toContain('<svg');
});

it('renders blade view with modal preview when previewable', function () {
    $column = QrCodeColumn::make('qr')
        ->payload(fn () => 'https://filamentphp.com')
        ->previewable(true)
        ->downloadable(true);

    $html = $column->toHtml();

    expect($html)->toBeString()
        ->and($html)->toContain('fi-ta-qr-code')
        ->and($html)->toContain('fi-modal')
        ->and($html)->toContain('https://filamentphp.com')
        ->and($html)->toContain('Download QR');
});
