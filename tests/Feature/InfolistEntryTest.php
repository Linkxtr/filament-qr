<?php

declare(strict_types=1);

use Linkxtr\FilamentQr\Infolists\Entries\QrCodeEntry;
use Linkxtr\FilamentQr\Tests\Fixtures\Models\Record;

it('can initialize qr code infolist entry', function (): void {
    $qrCodeEntry = QrCodeEntry::make('qr')
        ->qrSize(240)
        ->qrMargin(3)
        ->qrColor('#1e293b')
        ->qrBackgroundColor('#f8fafc');

    expect($qrCodeEntry->getQrSize())->toBe(240);
    expect($qrCodeEntry->getQrMargin())->toBe(3);
    expect($qrCodeEntry->getQrColor())->toBe('#1e293b');
    expect($qrCodeEntry->getQrBackgroundColor())->toBe('#f8fafc');
});

it('supports infolist entry previewable, copyable, and downloadable configuration', function (): void {
    $qrCodeEntry = QrCodeEntry::make('qr')
        ->previewable(true)
        ->previewSize(360)
        ->previewHeading('Entry Modal Preview')
        ->copyable(true)
        ->copyMessage('Copied successfully!')
        ->downloadable(true)
        ->downloadFilename('infolist-qr');

    expect($qrCodeEntry->isPreviewable())->toBeTrue();
    expect($qrCodeEntry->getPreviewSize())->toBe(360);
    expect($qrCodeEntry->getPreviewHeading())->toBe('Entry Modal Preview');
    expect($qrCodeEntry->isCopyable())->toBeTrue();
    expect($qrCodeEntry->getCopyMessage())->toBe('Copied successfully!');
    expect($qrCodeEntry->isDownloadable())->toBeTrue();
    expect($qrCodeEntry->getDownloadFilename())->toBe('infolist-qr');
});

it('renders preview qr code on infolist entry', function (): void {
    $qrCodeEntry = QrCodeEntry::make('qr')
        ->state('https://filamentphp.com')
        ->previewSize(300);

    $rendered = $qrCodeEntry->renderPreviewQrCode();

    expect($rendered)->toBeString();
    expect($rendered)->toContain('<svg');
});

it('renders infolist entry with eloquent model record', function (): void {
    $record = Record::create([
        'name' => 'Demo Infolist Record',
        'qr' => 'https://linkxtr.io/demo',
    ]);

    $qrCodeEntry = QrCodeEntry::make('qr')
        ->payload(fn () => $record->qr);

    expect($qrCodeEntry->getQrPayload())->toBe('https://linkxtr.io/demo');
    $svg = $qrCodeEntry->renderQrCode();
    expect($svg)->toBeString()->and($svg)->toContain('<svg');
});

it('formats payloads on infolist entry using wifi, url, and email helpers', function (): void {
    $record = Record::create([
        'name' => 'WiFi Credentials',
        'wifi_ssid' => 'Corporate-WiFi',
        'wifi_password' => 'supersecret',
    ]);

    $qrCodeEntry = QrCodeEntry::make('wifi')
        ->asWifi($record->wifi_ssid, $record->wifi_password);

    expect($qrCodeEntry->getQrPayload())->toBe('WIFI:S:Corporate-WiFi;T:WPA;P:supersecret;H:false;;');

    $urlEntry = QrCodeEntry::make('url')
        ->asUrl('https://laravel.com');
    expect($urlEntry->getQrPayload())->toBe('https://laravel.com');

    $emailEntry = QrCodeEntry::make('email')
        ->asEmail('hello@example.com', 'Subject Test');
    expect($emailEntry->getQrPayload())->toBe('mailto:hello@example.com?subject=Subject+Test');
});

it('renders blade view with rendered svg and payload text', function (): void {
    $qrCodeEntry = QrCodeEntry::make('qr')
        ->state('https://filamentphp.com/demo');

    $html = view('filament-qr::infolists.entries.qr-code-entry', [
        'renderQrCode' => $qrCodeEntry->renderQrCode(...),
        'getQrPayload' => $qrCodeEntry->getQrPayload(...),
        'getQrFormat' => $qrCodeEntry->getQrFormat(...),
        'renderPreviewQrCode' => $qrCodeEntry->renderPreviewQrCode(...),
        'getPreviewHeading' => $qrCodeEntry->getPreviewHeading(...),
        'getDownloadFilename' => $qrCodeEntry->getDownloadFilename(...),
        'isDownloadable' => $qrCodeEntry->isDownloadable(...),
        'isPreviewable' => $qrCodeEntry->isPreviewable(...),
        'isCopyable' => $qrCodeEntry->isCopyable(...),
        'getCopyMessage' => $qrCodeEntry->getCopyMessage(...),
    ])->render();

    expect($html)->toBeString()
        ->and($html)->toContain('fi-in-qr-code')
        ->and($html)->toContain('<svg')
        ->and($html)->toContain('https://filamentphp.com/demo');
});

it('renders fallback text when payload is empty on infolist entry', function (): void {
    $qrCodeEntry = QrCodeEntry::make('qr')
        ->state(null);

    $html = view('filament-qr::infolists.entries.qr-code-entry', [
        'renderQrCode' => $qrCodeEntry->renderQrCode(...),
        'getQrPayload' => $qrCodeEntry->getQrPayload(...),
        'getQrFormat' => $qrCodeEntry->getQrFormat(...),
        'renderPreviewQrCode' => $qrCodeEntry->renderPreviewQrCode(...),
        'getPreviewHeading' => $qrCodeEntry->getPreviewHeading(...),
        'getDownloadFilename' => $qrCodeEntry->getDownloadFilename(...),
        'isDownloadable' => $qrCodeEntry->isDownloadable(...),
        'isPreviewable' => $qrCodeEntry->isPreviewable(...),
        'isCopyable' => $qrCodeEntry->isCopyable(...),
        'getCopyMessage' => $qrCodeEntry->getCopyMessage(...),
    ])->render();

    expect($html)->toBeString()
        ->and($html)->toContain('No QR Code Available');
});
