# Filament QR

[![Latest Version on Packagist](https://img.shields.io/packagist/v/linkxtr/filament-qr.svg?style=flat-square)](https://packagist.org/packages/linkxtr/filament-qr)
[![Total Downloads](https://img.shields.io/packagist/dt/linkxtr/filament-qr.svg?style=flat-square)](https://packagist.org/packages/linkxtr/filament-qr)
[![GitHub Tests Action Status](https://img.shields.io/github/actions/workflow/status/linkxtr/filament-qr/run-tests.yml?branch=main&label=tests&style=flat-square)](https://github.com/linkxtr/filament-qr/actions?query=workflow%3Arun-tests+branch%3Amain)
[![Software License](https://img.shields.io/badge/license-MIT-brightgreen.svg?style=flat-square)](LICENSE.md)
[![PHP Version](https://img.shields.io/badge/PHP-8.2%20--%208.5-blue.svg?style=flat-square)](https://php.net)
[![Laravel Version](https://img.shields.io/badge/Laravel-12%20%7C%2013-red.svg?style=flat-square)](https://laravel.com)
[![Filament Version](https://img.shields.io/badge/Filament-3%20%7C%204%20%7C%205-orange.svg?style=flat-square)](https://filamentphp.com)

A clean, modern Filament plugin to generate, render, style, and export QR codes powered by [`linkxtr/laravel-qrcode`](https://github.com/Linkxtr/laravel-qrcode).

Seamlessly embed dynamic QR codes inside **Filament Table Columns**, display rich codes in **Infolist Entries**, and provide one-click exports using single and bulk **Table Actions**.

---

## Highlights

- **Table Column (`QrCodeColumn`)**: Inline preview, responsive sizing, dark mode support, title tooltip, and instant click-to-preview or download.
- **Infolist Entry (`QrCodeEntry`)**: High-fidelity QR code display in view pages, infolist schemas, and slide-overs with state readout.
- **Table Actions (`DownloadQrAction` & `DownloadQrBulkAction`)**: Single-record streaming downloads and multi-record ZIP archives in SVG, PNG, or WebP.
- **Fluent Styling**: Custom dimensions, quiet-zone margins, error correction levels (L, M, Q, H), and brand color palette tokens (`primary`, `success`, `danger`, `warning`, `info`, `gray`, or hex).
- **Advanced Aesthetics**: Linear gradients, custom finder eye inner/outer colors, and embedded logo images.
- **Built-in Payload Helpers**: Fluent formatters for Wi-Fi credentials, URLs, emails, SMS, and custom closures.
- **Multi-Version Compatibility**: Supports **PHP 8.2 through 8.5**, **Laravel 12 and 13**, and **Filament 3, 4, and 5**.

---

## Installation

Install the package via Composer:

```bash
composer require linkxtr/filament-qr
```

### 1. Panel Plugin Registration

Register the `FilamentQrPlugin` in your Filament Panel configuration provider (usually `app/Providers/Filament/AdminPanelProvider.php`):

```php
namespace App\Providers\Filament;

use Filament\Panel;
use Filament\PanelProvider;
use Linkxtr\FilamentQr\FilamentQrPlugin;

class AdminPanelProvider extends PanelProvider
{
    public function panel(Panel $panel): Panel
    {
        return $panel
            ->default()
            ->id('admin')
            ->path('admin')
            ->plugin(FilamentQrPlugin::make());
    }
}
```

### 2. Publish Configuration (Optional)

Publish the configuration file to customize package-wide defaults:

```bash
php artisan vendor:publish --tag="filament-qr-config"
```

This creates `config/filament-qr.php`:

```php
return [
    'defaults' => [
        'size' => 160,
        'margin' => 1,
        'format' => 'svg', // svg, png, webp
        'error_correction' => 'M', // L, M, Q, H
        'color' => '#000000',
        'background_color' => '#ffffff',
    ],
    'preview' => [
        'modal_width' => 'sm',
        'render_size' => 320,
    ],
];
```

### 3. Publish Blade Views (Optional)

If you need to customize the HTML or Tailwind layout of columns or infolists, publish the views:

```bash
php artisan vendor:publish --tag="filament-qr-views"
```

---

## Usage Guide & Recipes

### 1. Table Columns (`QrCodeColumn`)

Add a QR code column directly to any Filament Resource table schema:

```php
namespace App\Filament\Resources\OrderResource\Pages;

use Filament\Tables\Table;
use Linkxtr\FilamentQr\Tables\Columns\QrCodeColumn;

public function table(Table $table): Table
{
    return $table
        ->columns([
            QrCodeColumn::make('order_number')
                ->label('Tracking QR')
                ->qrSize(100)
                ->qrColor('primary')
                ->downloadable(),
        ]);
}
```

#### Custom Sizing, Margins & Error Correction

```php
use Linkxtr\FilamentQr\Enums\ErrorCorrectionLevel;
use Linkxtr\FilamentQr\Enums\QrFormat;
use Linkxtr\FilamentQr\Tables\Columns\QrCodeColumn;

QrCodeColumn::make('ticket_uuid')
    ->qrSize(140)
    ->qrMargin(2)
    ->qrFormat(QrFormat::Svg) // or 'png', 'webp'
    ->qrErrorCorrection(ErrorCorrectionLevel::High); // 'L', 'M', 'Q', 'H'
```

#### Color Tokens, Hex Values & Gradients

You can pass Filament theme color tokens (`primary`, `danger`, `warning`, `success`, `info`, `gray`) or standard HEX colors:

```php
QrCodeColumn::make('access_token')
    ->qrColor('success')
    ->qrBackgroundColor('#f8fafc')
    ->qrGradient(from: '#0ea5e9', to: '#6366f1', type: 'vertical');
```

#### Eye Customization & Embedded Logos

```php
QrCodeColumn::make('company_uuid')
    ->qrEyeColor(eyeIndex: 0, inner: '#ef4444', outer: '#b91c1c')
    ->qrEyeColor(eyeIndex: 1, inner: '#3b82f6', outer: '#1d4ed8')
    ->qrLogo(path: public_path('images/logo-icon.png'), size: 0.22);
```

#### Custom Payload Resolution & Formatters

Format payloads using built-in helpers or dynamically resolve payloads from the model record:

```php
// Built-in Wi-Fi formatter
QrCodeColumn::make('wifi_qr')
    ->asWifi(
        ssid: fn ($record) => $record->network_name,
        password: fn ($record) => $record->network_password,
        encryption: 'WPA'
    );

// Built-in URL formatter
QrCodeColumn::make('invoice_url')
    ->asUrl(fn ($record) => route('invoices.public.show', $record));

// Custom formatter closure
QrCodeColumn::make('payload')
    ->formatPayloadUsing(fn ($record) => "ORDER-{$record->id}:{$record->total}");
```

---

### 2. Infolist Entries (`QrCodeEntry`)

Display large, high-contrast QR codes in resource view pages, relation managers, or modal slide-overs:

```php
namespace App\Filament\Resources\DeviceResource\Pages;

use Filament\Infolists\Infolist;
use Linkxtr\FilamentQr\Infolists\Entries\QrCodeEntry;

public function infolist(Infolist $infolist): Infolist
{
    return $infolist
        ->schema([
            QrCodeEntry::make('device_serial')
                ->label('Device Provisioning QR')
                ->qrSize(260)
                ->qrColor('#1e293b')
                ->asWifi(
                    ssid: fn ($record) => $record->ssid,
                    password: fn ($record) => $record->wpa_key
                ),
        ]);
}
```

The `QrCodeEntry` component inherits the exact same fluent styling and formatting traits (`HasQrCodeStyling`, `HasPayloadFormatters`) as `QrCodeColumn`.

---

### 3. Table Actions

#### Single-Record Action (`DownloadQrAction`)

Add a row action to your table allowing users to instantly download an SVG, PNG, or WebP QR code:

```php
use Filament\Tables\Table;
use Linkxtr\FilamentQr\Actions\DownloadQrAction;

public function table(Table $table): Table
{
    return $table
        ->actions([
            DownloadQrAction::make()
                ->filename(fn ($record) => "qr-order-{$record->id}")
                ->qrFormat('svg')
                ->qrSize(400)
                ->qrColor('primary'),
        ]);
}
```

#### Bulk Action (`DownloadQrBulkAction`)

Allow administrators to select multiple records in a table and download a combined `.zip` file containing individual QR codes:

```php
use Filament\Tables\Table;
use Linkxtr\FilamentQr\Actions\DownloadQrBulkAction;

public function table(Table $table): Table
{
    return $table
        ->bulkActions([
            DownloadQrBulkAction::make()
                ->archiveFilename('exported-tickets')
                ->qrFormat('png')
                ->qrSize(500)
                ->qrColor('#000000'),
        ]);
}
```

---

## Payload Formatters Reference

Both `QrCodeColumn`, `QrCodeEntry`, and `DownloadQrAction` share fluent payload helper methods:

| Method | Parameters | Output Payload |
|---|---|---|
| `asUrl($url)` | `string \| Closure $url` | Direct URL string |
| `asWifi($ssid, $password, $encryption, $hidden)` | `ssid`, `password = null`, `encryption = 'WPA'`, `hidden = false` | Standard `WIFI:S:...;T:...;P:...;H:...;;` format |
| `asEmail($email, $subject, $body)` | `email`, `subject = null`, `body = null` | `mailto:name@example.com?subject=...` |
| `asSms($phone, $message)` | `phone`, `message = null` | `smsto:+123456789:Message` |
| `formatPayloadUsing($callback)` | `Closure $callback` | Custom string returned by closure |

---

## Fluent Method API Reference

Every component implementing `HasQrCodeStyling` supports the following fluent methods:

| Method | Signature | Description |
|---|---|---|
| `qrSize()` | `int \| Closure $size` | Size in pixels (default: `160`). |
| `qrMargin()` | `int \| Closure $margin` | Quiet-zone margin in modules (default: `1`). |
| `qrFormat()` | `QrFormat \| string \| Closure $format` | `svg`, `png`, or `webp` (default: `svg`). |
| `qrErrorCorrection()` | `ErrorCorrectionLevel \| string \| Closure $level` | `L` (7%), `M` (15%), `Q` (25%), or `H` (30%). |
| `qrColor()` | `string \| Closure $color` | Foreground color (HEX string or Filament token). |
| `qrBackgroundColor()` | `string \| Closure $color` | Background color (HEX string or Filament token). |
| `qrGradient()` | `string $from, string $to, string $type = 'vertical'` | Linear gradient with starting/ending colors. |
| `qrEyeColor()` | `int $eyeIndex, string $inner, string $outer` | Custom colors for finder eyes (`0`, `1`, or `2`). |
| `qrLogo()` | `string \| Closure $path, float \| Closure $size = 0.2` | Overlay logo image path and relative scaling. |

### Component-Specific Methods

- **`QrCodeColumn`**:
  - `downloadable(bool | Closure $condition = true)`: Enables instant file download on click.
  - `previewable(bool | Closure $condition = true)`: Enables click-to-preview behavior.
  - `payload(Closure $callback)`: Overrides the record field resolver for the raw payload.
- **`DownloadQrAction`**:
  - `filename(string $name)`: Base filename without extension for single download.
- **`DownloadQrBulkAction`**:
  - `archiveFilename(string $name)`: Base name for the generated `.zip` file.

---

## v0.1.0 Release Notes & Packagist Checklist

### Release Highlights (v0.1.0)

- Initial release of `linkxtr/filament-qr`.
- First-class support for **Filament 3, 4, and 5**.
- Powered by `linkxtr/laravel-qrcode` v3.x.
- Zero-configuration table column (`QrCodeColumn`) with dark mode support.
- Rich infolist entry (`QrCodeEntry`) with mono-font payload inspector.
- Single and bulk download actions (`DownloadQrAction` and `DownloadQrBulkAction`).
- Comprehensive payload helpers for Wi-Fi, URLs, Emails, and SMS.
- Full SVG, PNG, and WebP rendering pipeline with gradients, eye styling, and logos.

### Packagist Submission Checklist

- [x] Canonical package name `linkxtr/filament-qr` registered in `composer.json`.
- [x] Semantic versioning Git tag `v0.1.0` applied.
- [x] Auto-discovery service provider configured under `extra.laravel.providers`.
- [x] PHP compatibility matrix validated (`^8.2 || ^8.3 || ^8.4 || ^8.5`).
- [x] Filament dependency configured for versions `^3.0 || ^4.0 || ^5.0`.
- [x] Packagist GitHub webhook configured for automated release publishing.

---

## Testing

Run tests with Pest:

```bash
composer test
```

---

## Contributing

Please review [CONTRIBUTING.md](.github/CONTRIBUTING.md) for details on submitting pull requests and running our quality test suite.

---

## License

The MIT License (MIT). Please see [License File](LICENSE) for more information.
