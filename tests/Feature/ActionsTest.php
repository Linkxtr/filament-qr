<?php

use Linkxtr\FilamentQr\Actions\DownloadQrAction;
use Linkxtr\FilamentQr\Actions\DownloadQrBulkAction;

it('can initialize download qr action with defaults and fluent setters', function () {
    $action = DownloadQrAction::make('downloadQr')
        ->filename('custom-code')
        ->qrSize(240)
        ->qrFormat('png')
        ->qrColor('#123456');

    expect($action->getName())->toBe('downloadQr');
    expect($action->getQrSize())->toBe(240);
    expect($action->getQrFormat())->toBe('png');
    expect($action->getQrColor())->toBe('#123456');
});

it('can initialize download qr bulk action with archive filename and styling', function () {
    $bulkAction = DownloadQrBulkAction::make('downloadQrBulk')
        ->archiveFilename('batch-qr')
        ->qrSize(180)
        ->qrFormat('svg')
        ->qrColor('#333333');

    expect($bulkAction->getName())->toBe('downloadQrBulk');
    expect($bulkAction->getQrSize())->toBe(180);
    expect($bulkAction->getQrFormat())->toBe('svg');
    expect($bulkAction->getQrColor())->toBe('#333333');
});
