<?php

use Linkxtr\FilamentQr\Tables\Columns\QrCodeColumn;

it('formats wifi payload with WPA by default', function () {
    $column = QrCodeColumn::make('qr')
        ->state('HomeNetwork')
        ->asWifi(password: 'Secret123');

    expect($column->getQrPayload())->toBe('WIFI:S:HomeNetwork;T:WPA;P:Secret123;H:false;;');
});

it('formats wifi with no password', function () {
    $column = QrCodeColumn::make('qr')
        ->state('FreeWifi')
        ->asWifi(encryption: 'NOPASS');

    expect($column->getQrPayload())->toBe('WIFI:S:FreeWifi;T:NOPASS;H:false;;');
});

it('formats email with subject and body query parameters', function () {
    $column = QrCodeColumn::make('qr')
        ->state('support@example.com')
        ->asEmail(subject: 'Hello Support', body: 'Need assistance');

    expect($column->getQrPayload())->toBe('mailto:support@example.com?subject=Hello+Support&body=Need+assistance');
});

it('formats sms scheme correctly', function () {
    $column = QrCodeColumn::make('qr')
        ->state('+1234567890')
        ->asSms(message: 'Hi there');

    expect($column->getQrPayload())->toBe('smsto:+1234567890:Hi there');
});

it('formats telephone number scheme', function () {
    $column = QrCodeColumn::make('qr')
        ->state('+1987654321')
        ->asPhoneNumber();

    expect($column->getQrPayload())->toBe('tel:+1987654321');
});

it('formats bitcoin crypto address and query', function () {
    $column = QrCodeColumn::make('qr')
        ->state('1A1zP1eP5QGefi2DMPTfTL5SLmv7DivfNa')
        ->asBitcoin(amount: 0.05, label: 'Satoshi');

    expect($column->getQrPayload())->toBe('bitcoin:1A1zP1eP5QGefi2DMPTfTL5SLmv7DivfNa?amount=0.05&label=Satoshi');
});

it('formats vcard 4.0 correctly', function () {
    $column = QrCodeColumn::make('qr')
        ->state('Jane Doe')
        ->asVCard(
            company: 'Acme Corp',
            job: 'Lead Architect',
            email: 'jane@acme.com',
            phone: '+15551234'
        );

    $vcard = $column->getQrPayload();

    expect($vcard)
        ->toContain('BEGIN:VCARD')
        ->toContain('VERSION:4.0')
        ->toContain('FN:Jane Doe')
        ->toContain('ORG:Acme Corp')
        ->toContain('TITLE:Lead Architect')
        ->toContain('EMAIL:jane@acme.com')
        ->toContain('TEL:+15551234')
        ->toContain('END:VCARD');
});

it('formats mecard schema correctly', function () {
    $column = QrCodeColumn::make('qr')
        ->state('Smith, John')
        ->asMeCard(
            phone: '+12345',
            email: 'john@smith.org'
        );

    expect($column->getQrPayload())->toBe('MECARD:N:Smith\\, John;TEL:+12345;EMAIL:john@smith.org;;');
});

it('formats geo location uri', function () {
    $column = QrCodeColumn::make('qr')
        ->asGeo(30.0444, 31.2357, name: 'Cairo');

    expect($column->getQrPayload())->toBe('geo:30.0444,31.2357?q=Cairo');
});
