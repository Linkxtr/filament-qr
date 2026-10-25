<?php

declare(strict_types=1);

use Linkxtr\FilamentQr\Actions\DownloadQrAction;
use Linkxtr\FilamentQr\Actions\DownloadQrBulkAction;

it('can initialize download qr action with defaults and fluent setters', function (): void {
    $downloadQrAction = DownloadQrAction::make('downloadQr')
        ->filename('custom-code')
        ->qrSize(240)
        ->qrFormat('png')
        ->qrColor('#123456');

    expect($downloadQrAction->getName())->toBe('downloadQr');
    expect($downloadQrAction->getQrSize())->toBe(240);
    expect($downloadQrAction->getQrFormat())->toBe('png');
    expect($downloadQrAction->getQrColor())->toBe('#123456');
});

it('can initialize download qr bulk action with archive filename and styling', function (): void {
    $downloadQrBulkAction = DownloadQrBulkAction::make('downloadQrBulk')
        ->archiveFilename('batch-qr')
        ->qrSize(180)
        ->qrFormat('svg')
        ->qrColor('#333333');

    expect($downloadQrBulkAction->getName())->toBe('downloadQrBulk');
    expect($downloadQrBulkAction->getQrSize())->toBe(180);
    expect($downloadQrBulkAction->getQrFormat())->toBe('svg');
    expect($downloadQrBulkAction->getQrColor())->toBe('#333333');
});
