<?php

use Linkxtr\FilamentQr\Tables\Columns\QrCodeColumn;

it('can initialize qr code column', function () {
    $column = QrCodeColumn::make('qr')
        ->qrSize(200)
        ->qrMargin(2)
        ->qrColor('#000000');

    expect($column->getQrSize())->toBe(200);
    expect($column->getQrMargin())->toBe(2);
    expect($column->getQrColor())->toBe('#000000');
});

it('formats payload as wifi', function () {
    $column = QrCodeColumn::make('wifi')
        ->asWifi('Office-Guest', 'secret123', 'WPA');

    expect($column->getQrPayload())->toBe('WIFI:S:Office-Guest;T:WPA;P:secret123;H:false;;');
});
