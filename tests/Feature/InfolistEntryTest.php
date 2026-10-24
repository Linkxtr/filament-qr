<?php

use Linkxtr\FilamentQr\Infolists\Entries\QrCodeEntry;
use Linkxtr\FilamentQr\Tests\Fixtures\Models\Record;

it('instantiates infolist entry with defaults', function () {
    $entry = QrCodeEntry::make('qr');

    expect($entry)->toBeInstanceOf(QrCodeEntry::class)
        ->and($entry->getQrSize())->toBe(160)
        ->and($entry->getQrFormat())->toBe('svg')
        ->and($entry->getQrMargin())->toBe(1);
});

it('resolves payload and renders data uri', function () {
    $record = new Record(['id' => 1, 'url' => 'https://google.com']);

    $entry = QrCodeEntry::make('url')
        ->record($record);

    expect($entry->getQrPayload())->toBe('https://google.com');

    $uri = $entry->renderQrCode();
    expect($uri)
        ->toBeString()
        ->toStartWith('data:image/svg+xml;base64,');
});

it('supports custom payload resolver on infolist entry', function () {
    $entry = QrCodeEntry::make('qr')
        ->payload(fn () => 'INSPECT_PAYLOAD_123');

    expect($entry->getQrPayload())->toBe('INSPECT_PAYLOAD_123');
});

it('supports preview and copy interactions', function () {
    $entry = QrCodeEntry::make('qr')
        ->previewable(true)
        ->previewSize(500)
        ->previewHeading('Scan Me')
        ->copyable(true)
        ->copyMessage('Payload copied!')
        ->downloadable(true)
        ->downloadFilename('infolist-qr');

    expect($entry->isPreviewable())->toBeTrue()
        ->and($entry->getPreviewSize())->toBe(500)
        ->and($entry->getPreviewHeading())->toBe('Scan Me')
        ->and($entry->isCopyable())->toBeTrue()
        ->and($entry->getCopyMessage())->toBe('Payload copied!')
        ->and($entry->isDownloadable())->toBeTrue()
        ->and($entry->getDownloadFilename())->toBe('infolist-qr');
});
