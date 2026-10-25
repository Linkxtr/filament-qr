<?php

declare(strict_types=1);

use Linkxtr\FilamentQr\Actions\DownloadQrAction;
use Symfony\Component\HttpFoundation\StreamedResponse;

it('can initialize download qr action with defaults', function (): void {
    $downloadQrAction = DownloadQrAction::make();

    expect($downloadQrAction->getName())->toBe('downloadQr');
    expect($downloadQrAction->getLabel())->toBe('Download QR');
    expect($downloadQrAction->getFilename())->toBe('qrcode');
    expect($downloadQrAction->getQrFormat())->toBe('svg');
});

it('supports custom filename and payload callback', function (): void {
    $downloadQrAction = DownloadQrAction::make()
        ->filename('custom-filename')
        ->payload(fn ($record): string => "payload-for-{$record['id']}");

    $record = ['id' => 42];

    expect($downloadQrAction->getFilename())->toBe('custom-filename');
    expect($downloadQrAction->resolvePayloadForRecord($record))->toBe('payload-for-42');
});

it('executes download qr action and returns streamed response', function (): void {
    $record = ['qr' => 'https://example.com'];

    $downloadQrAction = DownloadQrAction::make()
        ->record($record)
        ->payload(fn ($record) => $record['qr'])
        ->filename('qr-example');

    $response = $downloadQrAction->call();

    expect($response)->toBeInstanceOf(StreamedResponse::class);
    expect($response->headers->get('content-type'))->toBe('image/svg+xml');
    expect($response->headers->get('content-disposition'))->toContain('qr-example.svg');
});
