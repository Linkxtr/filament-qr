<?php

declare(strict_types=1);

namespace Linkxtr\FilamentQr\Actions;

use Closure;
use Filament\Actions\Action;
use Illuminate\Database\Eloquent\Model;
use Linkxtr\FilamentQr\Concerns\HasPayloadFormatters;
use Linkxtr\FilamentQr\Concerns\HasQrCodeStyling;
use Linkxtr\FilamentQr\Services\QrCodeRenderer;
use Stringable;
use Symfony\Component\HttpFoundation\StreamedResponse;

final class DownloadQrAction extends Action
{
    use HasPayloadFormatters;
    use HasQrCodeStyling;

    private string|Closure $filename = 'qrcode';

    private ?Closure $payloadResolver = null;

    private mixed $customRecord = null;

    protected function setUp(): void
    {
        parent::setUp();

        $this->label('Download QR');
        $this->icon('heroicon-o-arrow-down-tray');

        $this->action(function (): ?StreamedResponse {
            $record = $this->customRecord ?? $this->getRecord();
            $payload = $this->resolvePayloadForRecord($record);

            if (empty($payload)) {
                return null;
            }

            $format = $this->getQrFormat();

            /** @var QrCodeRenderer $qrCodeRenderer */
            $qrCodeRenderer = app(QrCodeRenderer::class);

            $output = $qrCodeRenderer->renderRaw(
                payload: $payload,
                size: $this->getQrSize(),
                margin: $this->getQrMargin(),
                format: $format,
                errorCorrection: $this->getQrErrorCorrection(),
                color: $this->getQrColor(),
                backgroundColor: $this->getQrBackgroundColor(),
                gradient: $this->getQrGradient(),
                eyeColors: $this->getQrEyeColors(),
                eyeStyle: $this->getQrEyeStyle(),
                internalEyeStyle: $this->getQrInternalEyeStyle(),
                style: $this->getQrStyle(),
                styleSize: $this->getQrStyleSize(),
                logoPath: $this->getQrLogoPath(),
                logoSize: $this->getQrLogoSize(),
                dpi: $this->getQrDpi(),
                resolution: $this->getQrResolution(),
                encoding: $this->getQrEncoding(),
            );

            $contentType = match ($format) {
                'svg' => 'image/svg+xml',
                'webp' => 'image/webp',
                'eps' => 'application/postscript',
                default => 'image/png',
            };

            $filename = $this->getFilename();

            return response()->streamDownload(function () use ($output): void {
                echo $output;
            }, "{$filename}.{$format}", ['Content-Type' => $contentType]);
        });
    }

    public static function getDefaultName(): string
    {
        return 'downloadQr';
    }

    public function payload(Closure $callback): static
    {
        $this->payloadResolver = $callback;

        return $this;
    }

    public function resolvePayloadForRecord(mixed $record): ?string
    {
        if ($this->payloadResolver instanceof Closure) {
            $raw = $this->evaluate($this->payloadResolver, ['record' => $record]);
        } elseif ($record instanceof Model) {
            $raw = $record->getAttribute('qr') ?? $record->getAttribute('qr_code') ?? $record->getAttribute('url') ?? $record->getKey();
        } elseif (is_array($record)) {
            $raw = $record['qr'] ?? $record['qr_code'] ?? $record['url'] ?? $record['id'] ?? null;
        } elseif (is_object($record) && isset($record->qr)) {
            $raw = $record->qr;
        } elseif (is_object($record) && isset($record->url)) {
            $raw = $record->url;
        } else {
            $raw = $record;
        }

        return $this->resolveFormattedPayload($raw);
    }

    public function filename(string|Closure $name): static
    {
        $this->filename = $name;

        return $this;
    }

    public function getFilename(): string
    {
        $resolved = $this->evaluate($this->filename);

        return is_string($resolved) ? $resolved : (is_scalar($resolved) || $resolved instanceof Stringable ? (string) $resolved : 'qrcode');
    }
}
