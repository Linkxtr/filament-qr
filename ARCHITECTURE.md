# Architecture & Standards Review: linkxtr/filament-qr

**Reviewer:** Omar Fathy, Engineering Lead & Maintainer (`linkxtr/laravel-qrcode`, `linkxtr/docs`)  
**Package:** `linkxtr/filament-qr`  
**Status:** Approved with Architecture & Quality Recommendations  
**Date:** October 2026

---

## 1. Executive Summary

This architecture and standards review establishes the architectural foundations, dependency contracts, Filament integration conventions, and quality bar for `linkxtr/filament-qr`. 

The package is designed as a companion to `linkxtr/laravel-qrcode` (v3.x), bringing native QR code generation, styling, modal previews, and exports into the Filament admin panel ecosystem.

---

## 2. Repository Structure & Component Inventory

The codebase adheres to standard PSR-4 conventions under namespace `Linkxtr\FilamentQr`:

```text
linkxtr/filament-qr/
├── config/
│   └── filament-qr.php                  # Publishable default styling and preview configuration
├── resources/
│   └── views/
│       ├── infolists/entries/
│       │   └── qr-code-entry.blade.php  # Infolist entry Blade view
│       └── tables/columns/
│           └── qr-code-column.blade.php # Table column Blade view
├── src/
│   ├── Actions/
│   │   ├── DownloadQrAction.php         # Single record QR download action
│   │   └── DownloadQrBulkAction.php     # Bulk selection QR archive download action
│   ├── Concerns/
│   │   ├── HasPayloadFormatters.php     # Fluent helpers: asWifi, asUrl, asEmail, asSms
│   │   └── HasQrCodeStyling.php         # Fluent setters for size, colors, format, error correction, logo
│   ├── Enums/
│   │   ├── ErrorCorrectionLevel.php     # Backed enum: L, M, Q, H
│   │   └── QrFormat.php                 # Backed enum: svg, png, webp
│   ├── Infolists/Entries/
│   │   └── QrCodeEntry.php              # Filament Infolist Entry
│   ├── Services/
│   │   └── QrCodeRenderer.php           # Core rendering service bridging to linkxtr/laravel-qrcode
│   ├── Tables/Columns/
│   │   └── QrCodeColumn.php             # Filament Table Column
│   ├── FilamentQrPlugin.php             # Filament\Contracts\Plugin implementation
│   └── FilamentQrServiceProvider.php    # Laravel Service Provider
└── tests/
    ├── Feature/
    │   └── TableColumnTest.php          # Table column integration tests
    ├── Pest.php                         # Pest configuration and custom helpers
    └── TestCase.php                     # Orchestra Testbench setup
```

### Architectural Strengths
- **Separation of Concerns:** Business logic for generating QR codes remains in `QrCodeRenderer` and the underlying `linkxtr/laravel-qrcode` package; Filament classes act solely as presentation and action wrappers.
- **Trait Reusability:** `HasQrCodeStyling` and `HasPayloadFormatters` are shared cleanly across `QrCodeColumn`, `QrCodeEntry`, and `DownloadQrAction`.
- **Decoupled Fallback:** Component defaults read from `config('filament-qr.defaults')`, allowing components to function reliably even when rendered outside of a Filament Panel context (such as standalone Livewire tables or standalone infolists).

---

## 3. Dependency Matrix & Composer Constraints

### Validated Constraints (`composer.json`)

| Package / Runtime | Constraint | Status | Notes |
|---|---|---|---|
| `php` | `^8.2 \|\| ^8.3 \|\| ^8.4 \|\| ^8.5` | Validated | Fully aligns with modern PHP release cycle |
| `filament/filament` | `^3.0 \|\| ^4.0 \|\| ^5.0` | Validated | Covers Filament v3, v4, and forward-looking v5 |
| `illuminate/contracts` | `^11.0 \|\| ^12.0 \|\| ^13.0` | Validated | Aligns with Laravel 11, 12, and 13 support |
| `illuminate/support` | `^11.0 \|\| ^12.0 \|\| ^13.0` | Validated | Core support helpers |
| `linkxtr/laravel-qrcode` | `^3.0` | Validated | Leverages core QR engine v3.x |

### Upstream Dependency Advisory
> **Important:** While `linkxtr/filament-qr` correctly declares compatibility with `illuminate/contracts: ^11.0 || ^12.0 || ^13.0`, the upstream package `linkxtr/laravel-qrcode` v3.0 in `dev-main` must confirm that its own `composer.json` constraint accommodates `illuminate/contracts: ^11.0 || ^12.0 || ^13.0` so that Laravel 11 users do not experience installation resolution conflicts. As maintainer of `laravel-qrcode`, this will be coordinated across both repositories.

---

## 4. Filament Plugin Contract Validation

`Linkxtr\FilamentQr\FilamentQrPlugin` implements `Filament\Contracts\Plugin`:

- `getId(): string`: Returns `'filament-qr'`, providing a stable identifier within `FilamentManager`.
- `register(Panel $panel): void`: Available for panel-level asset and component registration.
- `boot(Panel $panel): void`: Available for lifecycle boot hooks.
- `make(): static`: Factory instantiation adhering to Filament plugin conventions.
- `get(): static`: Resolves the current plugin instance from the active panel via `filament('filament-qr')`.

---

## 5. Service Provider & Resource Namespaces

`Linkxtr\FilamentQr\FilamentQrServiceProvider` establishes proper container bindings and asset publishing:

1. **Configuration:**
   - Merges default configuration from `config/filament-qr.php` under the `'filament-qr'` key.
   - Publishes configuration via `--tag="filament-qr-config"`.

2. **Singleton Binding:**
   - Registers `Linkxtr\FilamentQr\Services\QrCodeRenderer` as a singleton in the Laravel container.

3. **View Loading & Publishing:**
   - Registers view namespace `'filament-qr'` pointing to `resources/views`.
   - Views follow Filament standard naming conventions:
     - `filament-qr::tables.columns.qr-code-column`
     - `filament-qr::infolists.entries.qr-code-entry`
   - Publishes Blade views via `--tag="filament-qr-views"`.
   - Publishes all assets combined via `--tag="filament-qr"`.

---

## 6. ApricotCake Quality Bar for Downstream PRs

Every pull request submitted to `linkxtr/filament-qr` must satisfy the following strict quality gates before merge approval:

1. **Code Style (Laravel Pint):**
   - Must pass `vendor/bin/pint --test` with 0 violations.
   - Clean imports with `no_unused_imports`.

2. **Static Analysis (PHPStan):**
   - PHPStan Level 8 / Max with `vendor/bin/phpstan analyse --ansi`.
   - Zero errors policy without suppressions unless documented and architecturally justified.

3. **Test Suite & Coverage (Pest):**
   - Must achieve **100% Unit Coverage** (`--coverage --min=100`).
   - Must achieve **100% Type Coverage** (`--type-coverage --min=100`).
   - Test suites must cover:
     - Column rendering, formatters (`asWifi`, `asUrl`, `asEmail`, `asSms`), and download flags.
     - Infolist entries and state resolution.
     - Single and bulk download actions (`DownloadQrAction`, `DownloadQrBulkAction`).
     - Renderer error handling and color conversions.
     - Plugin registration and panel boot lifecycles.
     - Service provider config merging and publishable tags.

4. **Mutation Testing:**
   - Run `pest --mutate --covered-only` to ensure assertions catch mutated conditions with zero surviving mutants on core domain methods.

5. **Refactoring & Clean Code (Rector):**
   - Automated code refactoring to modern PHP 8.2+ constructor property promotion, match expressions, and typed properties.
