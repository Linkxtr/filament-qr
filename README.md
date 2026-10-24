# Filament QR

A clean, modern Filament plugin to generate, render, style, and export QR codes powered by [linkxtr/laravel-qrcode](https://github.com/Linkxtr/laravel-qrcode).

Supports **PHP 8.2 through 8.5**, **Laravel 11, 12, and 13**, and **Filament 3, 4, and 5**.

## Features

- **Table Column (`QrCodeColumn`)**: Inline preview, dark mode support, click-to-preview modal, and quick download.
- **Infolist Entry (`QrCodeEntry`)**: High-fidelity QR code display in resource view pages and slide-overs.
- **Actions (`DownloadQrAction`)**: One-click download in SVG, PNG, and WebP.
- **Fluent Styling**: Size, margins, eye styles, logo integration, error correction levels, and color palette tokens.
- **Payload Helpers**: Built-in fluent helpers for WiFi credentials, vCards, URLs, emails, and SMS.

## Installation

```bash
composer require linkxtr/filament-qr
```

Optionally publish the configuration file:

```bash
php artisan vendor:publish --tag="filament-qr-config"
```

## Quick Start

### 1. In Filament Table Columns

```php
use Linkxtr\FilamentQr\Tables\Columns\QrCodeColumn;

QrCodeColumn::make('qr_code')
    ->qrSize(120)
    ->qrColor('primary')
    ->downloadable();
```

### 2. In Filament Infolists

```php
use Linkxtr\FilamentQr\Infolists\Entries\QrCodeEntry;

QrCodeEntry::make('qr_code')
    ->qrSize(250)
    ->asWifi(fn ($record) => $record->wifi_ssid, fn ($record) => $record->wifi_password);
```

## Testing

```bash
composer test
```

## License

MIT License. Developed with care by [Khaled Sadek](https://github.com/khaled-sadek) and the [Linkxtr](https://github.com/Linkxtr) team.
