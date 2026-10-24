<?php

use Linkxtr\FilamentQr\Tables\Columns\QrCodeColumn;
use Linkxtr\FilamentQr\Tests\Fixtures\Models\Record;

it('instantiates qr code column with defaults', function () {
    $column = QrCodeColumn::make('code');

    expect($column)->toBeInstanceOf(QrCodeColumn::class)
        ->and($column->getQrFormat())->toBe('svg')
        ->and($column->getQrSize())->toBe(160)
        ->and($column->getQrMargin())->toBe(1)
        ->and($column->getQrErrorCorrection())->toBe('M');
});

it('resolves payload correctly from state', function () {
    $column = QrCodeColumn::make('qr')
        ->state('https://linkxtr.com');

    expect($column->getQrPayload())->toBe('https://linkxtr.com');
});

it('supports custom payload resolver callback', function () {
    $record = new Record(['name' => 'Custom Target']);

    $column = QrCodeColumn::make('qr')
        ->record($record)
        ->payload(fn (Record $record) => 'CUSTOM:'.$record->name);

    expect($column->getQrPayload())->toBe('CUSTOM:Custom Target');
});

it('renders inline qr code data uri', function () {
    $column = QrCodeColumn::make('qr')
        ->state('Hello World');

    $rendered = $column->renderQrCode();

    expect($rendered)
        ->toBeString()
        ->toStartWith('data:image/svg+xml;base64,');
});

it('returns null when payload is empty', function () {
    $column = QrCodeColumn::make('qr')
        ->state('');

    expect($column->renderQrCode())->toBeNull();
});

it('configures interactive preview and copy features', function () {
    $column = QrCodeColumn::make('qr')
        ->previewable(true)
        ->previewSize(400)
        ->previewHeading('Custom Heading')
        ->copyable(true)
        ->copyMessage('Payload saved!')
        ->downloadable(true)
        ->downloadFilename('my-code');

    expect($column->isPreviewable())->toBeTrue()
        ->and($column->getPreviewSize())->toBe(400)
        ->and($column->getPreviewHeading())->toBe('Custom Heading')
        ->and($column->isCopyable())->toBeTrue()
        ->and($column->getCopyMessage())->toBe('Payload saved!')
        ->and($column->isDownloadable())->toBeTrue()
        ->and($column->getDownloadFilename())->toBe('my-code');
});
