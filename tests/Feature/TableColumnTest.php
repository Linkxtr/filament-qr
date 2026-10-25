<?php

declare(strict_types=1);

use Linkxtr\FilamentQr\Enums\ErrorCorrectionLevel;
use Linkxtr\FilamentQr\Enums\QrFormat;
use Linkxtr\FilamentQr\Tables\Columns\QrCodeColumn;

it('can initialize qr code column with styling', function (): void {
    $qrCodeColumn = QrCodeColumn::make('qr')
        ->qrSize(200)
        ->qrMargin(2)
        ->qrColor('#112233')
        ->qrBackgroundColor('#ffffff')
        ->qrFormat(QrFormat::Png)
        ->qrErrorCorrection(ErrorCorrectionLevel::High);

    expect($qrCodeColumn->getQrSize())->toBe(200);
    expect($qrCodeColumn->getQrMargin())->toBe(2);
    expect($qrCodeColumn->getQrColor())->toBe('#112233');
    expect($qrCodeColumn->getQrBackgroundColor())->toBe('#ffffff');
    expect($qrCodeColumn->getQrFormat())->toBe('png');
    expect($qrCodeColumn->getQrErrorCorrection())->toBe('H');
});

it('formats payload as wifi', function (): void {
    $qrCodeColumn = QrCodeColumn::make('wifi')
        ->asWifi('Office-Guest', 'secret123', 'WPA');

    expect($qrCodeColumn->getQrPayload())->toBe('WIFI:S:Office-Guest;T:WPA;P:secret123;H:false;;');
});

it('formats payload as url, email, and sms', function (): void {
    $qrCodeColumn = QrCodeColumn::make('url')->asUrl('https://example.com');
    expect($qrCodeColumn->getQrPayload())->toBe('https://example.com');

    $emailColumn = QrCodeColumn::make('email')->asEmail('test@example.com', 'Hello', 'World');
    expect($emailColumn->getQrPayload())->toBe('mailto:test@example.com?subject=Hello&body=World');

    $smsColumn = QrCodeColumn::make('sms')->asSms('+123456789', 'Hi there');
    expect($smsColumn->getQrPayload())->toBe('smsto:+123456789:Hi there');
});

it('supports previewable and downloadable configurations', function (): void {
    $qrCodeColumn = QrCodeColumn::make('qr')
        ->previewable(false)
        ->downloadable(true);

    expect($qrCodeColumn->isPreviewable())->toBeFalse();
    expect($qrCodeColumn->isDownloadable())->toBeTrue();

    $qrCodeColumn->previewable(true);
    expect($qrCodeColumn->isPreviewable())->toBeTrue();
});

it('renders qr code correctly', function (): void {
    $qrCodeColumn = QrCodeColumn::make('qr')
        ->payload(fn (): string => 'https://linkxtr.com');

    $rendered = $qrCodeColumn->renderQrCode();
    expect($rendered)->toBeString()
        ->and($rendered)->toContain('<svg');
});

it('renders blade view with modal preview when previewable', function (): void {
    $qrCodeColumn = QrCodeColumn::make('qr')
        ->payload(fn (): string => 'https://filamentphp.com')
        ->previewable(true)
        ->downloadable(true);

    $html = $qrCodeColumn->toHtml();

    expect($html)->toBeString()
        ->and($html)->toContain('fi-ta-qr-code')
        ->and($html)->toContain('fi-modal')
        ->and($html)->toContain('https://filamentphp.com')
        ->and($html)->toContain('Download QR');
});
