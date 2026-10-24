<?php

use Linkxtr\FilamentQr\Actions\DownloadQrAction;
use Linkxtr\FilamentQr\Tests\Fixtures\Models\Record;

it('configures filename dynamically via closure', function () {
    $record = new Record(['id' => 42, 'name' => 'Badge']);

    $action = DownloadQrAction::make()
        ->record($record)
        ->filename(fn (Record $record) => "badge-{$record->id}");

    expect($action->getFilename())->toBe('badge-42');
});

it('resolves payload automatically from model attribute', function () {
    $record = new Record(['qr' => 'https://example.com/qr/100']);
    $action = DownloadQrAction::make();

    expect($action->resolvePayloadForRecord($record))->toBe('https://example.com/qr/100');
});

it('resolves payload from array records', function () {
    $action = DownloadQrAction::make();

    expect($action->resolvePayloadForRecord(['url' => 'https://foo.bar']))->toBe('https://foo.bar');
});
