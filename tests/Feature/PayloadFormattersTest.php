<?php

use Linkxtr\FilamentQr\Tables\Columns\QrCodeColumn;

it('formats wifi credentials with escaping and custom encryption', function () {
    $column = QrCodeColumn::make('wifi_col')
        ->asWifi('My-Home-Network', 'p@ss:w;ord,1', 'WPA', true);

    expect($column->getQrPayload())->toBe('WIFI:S:My-Home-Network;T:WPA;P:p@ss\\:w\\;ord\\,1;H:true;;');
});

it('formats open / nopass wifi networks', function () {
    $column = QrCodeColumn::make('open_wifi')
        ->asWifi('Public-Guest', null, 'NOPASS', false);

    expect($column->getQrPayload())->toBe('WIFI:S:Public-Guest;T:NOPASS;H:false;;');
});

it('formats bitcoin address with amount, label, and message', function () {
    $column = QrCodeColumn::make('btc')
        ->asBitcoin('1A1zP1eP5QGefi2DMPTfTL5SLmv7DivfNa', 0.05, 'Satoshi Store', 'Payment for order #123');

    $payload = $column->getQrPayload();

    expect($payload)->toStartWith('bitcoin:1A1zP1eP5QGefi2DMPTfTL5SLmv7DivfNa?')
        ->and($payload)->toContain('amount=0.05')
        ->and($payload)->toContain('label=Satoshi%20Store')
        ->and($payload)->toContain('message=Payment%20for%20order%20%23123');
});

it('supports asBtc alias without parameters', function () {
    $column = QrCodeColumn::make('btc_alias')
        ->asBtc('bc1qar0srrr7xfkvy5l643lydnw9re59gtzzwf5mdq');

    expect($column->getQrPayload())->toBe('bitcoin:bc1qar0srrr7xfkvy5l643lydnw9re59gtzzwf5mdq');
});

it('formats ethereum addresses with and without amount', function () {
    $col1 = QrCodeColumn::make('eth1')
        ->asEthereum('0x742d35Cc6634C0532925a3b844Bc454e4438f44e');
    expect($col1->getQrPayload())->toBe('ethereum:0x742d35Cc6634C0532925a3b844Bc454e4438f44e');

    $col2 = QrCodeColumn::make('eth2')
        ->asEth('0x742d35Cc6634C0532925a3b844Bc454e4438f44e', 1.75);
    expect($col2->getQrPayload())->toBe('ethereum:0x742d35Cc6634C0532925a3b844Bc454e4438f44e?amount=1.75');
});

it('formats arbitrary crypto addresses with asCrypto', function () {
    $col = QrCodeColumn::make('sol')
        ->asCrypto('solana', '7xKXtg2CW87d97TXJSDpbD5jBkheTqA83TZRuJosgAsU', 10.5, ['memo' => 'INV-99']);

    $payload = $col->getQrPayload();
    expect($payload)->toStartWith('solana:7xKXtg2CW87d97TXJSDpbD5jBkheTqA83TZRuJosgAsU?')
        ->and($payload)->toContain('amount=10.5')
        ->and($payload)->toContain('memo=INV-99');
});

it('formats vCard 4.0 contact with full RFC 6350 fields', function () {
    $column = QrCodeColumn::make('vcard')
        ->asVCard(
            name: 'Jane Doe',
            phone: '+1-555-0199',
            email: 'jane.doe@example.com',
            company: 'Acme Corp',
            job: 'Chief Architect',
            firstName: 'Jane',
            lastName: 'Doe',
            address: '100 Main St, Metropolis',
            url: 'https://janedoe.com',
            note: 'Met at Laravel Conference; good lead',
            birthday: '1990-05-15',
            version: '4.0'
        );

    $payload = $column->getQrPayload();

    expect($payload)->toContain("BEGIN:VCARD\r\n")
        ->and($payload)->toContain("VERSION:4.0\r\n")
        ->and($payload)->toContain("FN:Jane Doe\r\n")
        ->and($payload)->toContain("N:Doe;Jane;;;\r\n")
        ->and($payload)->toContain("ORG:Acme Corp\r\n")
        ->and($payload)->toContain("TITLE:Chief Architect\r\n")
        ->and($payload)->toContain("EMAIL:jane.doe@example.com\r\n")
        ->and($payload)->toContain("TEL:+1-555-0199\r\n")
        ->and($payload)->toContain("URL:https://janedoe.com\r\n")
        ->and($payload)->toContain("BDAY:1990-05-15\r\n")
        ->and($payload)->toContain('END:VCARD');
});

it('supports vCard 4.0 using associative array parameter', function () {
    $column = QrCodeColumn::make('vcard_arr')
        ->asVCard([
            'name' => 'John Smith',
            'phone' => '+44 20 7946 0912',
            'email' => 'john@example.co.uk',
            'company' => 'Stark Enterprises',
        ]);

    $payload = $column->getQrPayload();

    expect($payload)->toContain("BEGIN:VCARD\r\n")
        ->and($payload)->toContain("VERSION:4.0\r\n")
        ->and($payload)->toContain("FN:John Smith\r\n")
        ->and($payload)->toContain("ORG:Stark Enterprises\r\n")
        ->and($payload)->toContain("TEL:+44 20 7946 0912\r\n")
        ->and($payload)->toContain("EMAIL:john@example.co.uk\r\n")
        ->and($payload)->toContain('END:VCARD');
});

it('formats url, email, and sms payloads', function () {
    $urlCol = QrCodeColumn::make('url')->asUrl('https://laravel.com');
    expect($urlCol->getQrPayload())->toBe('https://laravel.com');

    $emailCol = QrCodeColumn::make('email')->asEmail('support@example.com', 'Inquiry', 'Hello team');
    expect($emailCol->getQrPayload())->toBe('mailto:support@example.com?subject=Inquiry&body=Hello+team');

    $smsCol = QrCodeColumn::make('sms')->asSms('+15551234567', 'Verify code: 8901');
    expect($smsCol->getQrPayload())->toBe('smsto:+15551234567:Verify code: 8901');
});

it('formats geo, phone number, and mecard payloads', function () {
    $geoCol = QrCodeColumn::make('geo')->asGeo(30.0444, 31.2357, 'Cairo Tower');
    expect($geoCol->getQrPayload())->toBe('geo:30.0444,31.2357?q=Cairo+Tower');

    $phoneCol = QrCodeColumn::make('phone')->asPhoneNumber('+1234567890');
    expect($phoneCol->getQrPayload())->toBe('tel:+1234567890');

    $meCardCol = QrCodeColumn::make('mecard')->asMeCard('Alex Green', '+100200300', 'alex@example.com');
    expect($meCardCol->getQrPayload())->toBe('MECARD:N:Alex Green;TEL:+100200300;EMAIL:alex@example.com;;');
});

it('evaluates record state when parameters are omitted', function () {
    $urlCol = QrCodeColumn::make('url_from_state')
        ->state('https://tailwindcss.com')
        ->asUrl();
    expect($urlCol->getQrPayload())->toBe('https://tailwindcss.com');

    $phoneCol = QrCodeColumn::make('phone_from_state')
        ->state('+9876543210')
        ->asPhoneNumber();
    expect($phoneCol->getQrPayload())->toBe('tel:+9876543210');
});
