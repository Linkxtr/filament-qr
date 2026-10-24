<?php

use Linkxtr\FilamentQr\Infolists\Entries\QrCodeEntry;
use Linkxtr\FilamentQr\Tests\Fixtures\Models\Record;

it('can initialize qr code infolist entry', function () {
    $entry = QrCodeEntry::make('qr')
        ->qrSize(240)
        ->qrMargin(3)
        ->qrColor('#1e293b')
        ->qrBackgroundColor('#f8fafc');

    expect($entry->getQrSize())->toBe(240);
    expect($entry->getQrMargin())->toBe(3);
    expect($entry->getQrColor())->toBe('#1e293b');
    expect($entry->getQrBackgroundColor())->toBe('#f8fafc');
});

it('supports infolist entry previewable, copyable, and downloadable configuration', function () {
    $entry = QrCodeEntry::make('qr')
        ->previewable(true)
        ->previewSize(360)
        ->previewHeading('Entry Modal Preview')
        ->copyable(true)
        ->copyMessage('Copied successfully!')
        ->downloadable(true)
        ->downloadFilename('infolist-qr');

    expect($entry->isPreviewable())->toBeTrue();
    expect($entry->getPreviewSize())->toBe(360);
    expect($entry->getPreviewHeading())->toBe('Entry Modal Preview');
    expect($entry->isCopyable())->toBeTrue();
    expect($entry->getCopyMessage())->toBe('Copied successfully!');
    expect($entry->isDownloadable())->toBeTrue();
    expect($entry->getDownloadFilename())->toBe('infolist-qr');
});

it('renders preview qr code on infolist entry', function () {
    $entry = QrCodeEntry::make('qr')
        ->state('https://filamentphp.com')
        ->previewSize(300);

    $rendered = $entry->renderPreviewQrCode();

    expect($rendered)->toBeString();
    expect($rendered)->toContain('<svg');
});

it('renders infolist entry with eloquent model record', function () {
    $record = Record::create([
        'name' => 'Demo Infolist Record',
        'qr' => 'https://linkxtr.io/demo',
    ]);

    $entry = QrCodeEntry::make('qr')
        ->payload(fn () => $record->qr);

    expect($entry->getQrPayload())->toBe('https://linkxtr.io/demo');
    $svg = $entry->renderQrCode();
    expect($svg)->toBeString()->and($svg)->toContain('<svg');
});

it('formats payloads on infolist entry using wifi, url, and email helpers', function () {
    $record = Record::create([
        'name' => 'WiFi Credentials',
        'wifi_ssid' => 'Corporate-WiFi',
        'wifi_password' => 'supersecret',
    ]);

    $wifiEntry = QrCodeEntry::make('wifi')
        ->asWifi($record->wifi_ssid, $record->wifi_password);

    expect($wifiEntry->getQrPayload())->toBe('WIFI:S:Corporate-WiFi;T:WPA;P:supersecret;H:false;;');

    $urlEntry = QrCodeEntry::make('url')
        ->asUrl('https://laravel.com');
    expect($urlEntry->getQrPayload())->toBe('https://laravel.com');

    $emailEntry = QrCodeEntry::make('email')
        ->asEmail('hello@example.com', 'Subject Test');
    expect($emailEntry->getQrPayload())->toBe('mailto:hello@example.com?subject=Subject+Test');
});

it('renders blade view with rendered svg and payload text', function () {
    $entry = QrCodeEntry::make('qr')
        ->state('https://filamentphp.com/demo');

    $html = view('filament-qr::infolists.entries.qr-code-entry', [
        'renderQrCode' => fn () => $entry->renderQrCode(),
        'getQrPayload' => fn () => $entry->getQrPayload(),
        'getQrFormat' => fn () => $entry->getQrFormat(),
        'renderPreviewQrCode' => fn () => $entry->renderPreviewQrCode(),
        'getPreviewHeading' => fn () => $entry->getPreviewHeading(),
        'getDownloadFilename' => fn () => $entry->getDownloadFilename(),
        'isDownloadable' => fn () => $entry->isDownloadable(),
        'isPreviewable' => fn () => $entry->isPreviewable(),
        'isCopyable' => fn () => $entry->isCopyable(),
        'getCopyMessage' => fn () => $entry->getCopyMessage(),
    ])->render();

    expect($html)->toBeString()
        ->and($html)->toContain('fi-in-qr-code')
        ->and($html)->toContain('<svg')
        ->and($html)->toContain('https://filamentphp.com/demo');
});

it('renders fallback text when payload is empty on infolist entry', function () {
    $entry = QrCodeEntry::make('qr')
        ->state(null);

    $html = view('filament-qr::infolists.entries.qr-code-entry', [
        'renderQrCode' => fn () => $entry->renderQrCode(),
        'getQrPayload' => fn () => $entry->getQrPayload(),
        'getQrFormat' => fn () => $entry->getQrFormat(),
        'renderPreviewQrCode' => fn () => $entry->renderPreviewQrCode(),
        'getPreviewHeading' => fn () => $entry->getPreviewHeading(),
        'getDownloadFilename' => fn () => $entry->getDownloadFilename(),
        'isDownloadable' => fn () => $entry->isDownloadable(),
        'isPreviewable' => fn () => $entry->isPreviewable(),
        'isCopyable' => fn () => $entry->isCopyable(),
        'getCopyMessage' => fn () => $entry->getCopyMessage(),
    ])->render();

    expect($html)->toBeString()
        ->and($html)->toContain('No QR Code Available');
});
