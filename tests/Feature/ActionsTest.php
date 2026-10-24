<?php

use Linkxtr\FilamentQr\Actions\DownloadQrAction;
use Linkxtr\FilamentQr\Actions\DownloadQrBulkAction;

it('creates single download qr action with correct defaults', function () {
    $action = DownloadQrAction::make();

    expect($action->getName())->toBe('downloadQr')
        ->and($action->getLabel())->toBe('Download QR')
        ->and($action->getFilename())->toBe('qrcode');
});

it('creates bulk download action with correct defaults', function () {
    $action = DownloadQrBulkAction::make();

    expect($action->getName())->toBe('downloadQrBulk')
        ->and($action->getLabel())->toBe('Download QR Codes')
        ->and($action->getFilename())->toBe('qrcodes.zip');
});
