<?php

use Linkxtr\FilamentQr\Actions\DownloadQrAction;
use Symfony\Component\HttpFoundation\StreamedResponse;

it('can initialize download qr action with defaults', function () {
    $action = DownloadQrAction::make();

    expect($action->getName())->toBe('downloadQr');
    expect($action->getLabel())->toBe('Download QR');
    expect($action->getFilename())->toBe('qrcode');
    expect($action->getQrFormat())->toBe('svg');
});

it('supports custom filename and payload callback', function () {
    $action = DownloadQrAction::make()
        ->filename('custom-filename')
        ->payload(fn ($record) => "payload-for-{$record['id']}");

    $record = ['id' => 42];

    expect($action->getFilename())->toBe('custom-filename');
    expect($action->resolvePayloadForRecord($record))->toBe('payload-for-42');
});

it('executes download qr action and returns streamed response', function () {
    $record = ['qr' => 'https://example.com'];

    $action = DownloadQrAction::make()
        ->record($record)
        ->payload(fn ($record) => $record['qr'])
        ->filename('qr-example');

    $response = $action->call();

    expect($response)->toBeInstanceOf(StreamedResponse::class);
    expect($response->headers->get('content-type'))->toBe('image/svg+xml');
    expect($response->headers->get('content-disposition'))->toContain('qr-example.svg');
});
