<?php

declare(strict_types=1);

namespace Linkxtr\FilamentQr\Concerns;

use Closure;
use Illuminate\Database\Eloquent\Model;
use Linkxtr\FilamentQr\Services\QrCodeRenderer;
use Stringable;

trait HasQrInteractions
{
    protected bool|Closure $isDownloadable = false;

    protected string|Closure|null $downloadFilename = null;

    protected bool|Closure $isClickableToPreview = true;

    protected int|Closure|null $previewSize = null;

    protected string|Closure|null $previewHeading = null;

    protected bool|Closure $isCopyable = true;

    protected string|Closure $copyMessage = 'Copied to clipboard!';

    public function downloadable(bool|Closure $condition = true): static
    {
        $this->isDownloadable = $condition;

        return $this;
    }

    public function isDownloadable(): bool
    {
        return (bool) $this->evaluate($this->isDownloadable);
    }

    public function downloadFilename(string|Closure $filename): static
    {
        $this->downloadFilename = $filename;

        return $this;
    }

    public function getDownloadFilename(): string
    {
        if ($this->downloadFilename !== null) {
            $evaluated = $this->evaluate($this->downloadFilename);

            return is_string($evaluated) ? $evaluated : (is_scalar($evaluated) || $evaluated instanceof Stringable ? (string) $evaluated : 'qr-code');
        }

        $record = $this->getRecord();
        if ($record instanceof Model) {
            $key = $record->getKey();
        } elseif (is_array($record)) {
            $key = $record['id'] ?? $record['key'] ?? 'code';
        } else {
            $key = 'code';
        }

        $keyString = is_string($key) ? $key : (is_scalar($key) || $key instanceof Stringable ? (string) $key : 'code');

        return "qr-{$keyString}";
    }

    public function previewable(bool|Closure $condition = true): static
    {
        $this->isClickableToPreview = $condition;

        return $this;
    }

    public function isPreviewable(): bool
    {
        return (bool) $this->evaluate($this->isClickableToPreview);
    }

    public function previewSize(int|Closure $size): static
    {
        $this->previewSize = $size;

        return $this;
    }

    public function getPreviewSize(): int
    {
        $evaluated = $this->evaluate($this->previewSize);
        if (is_int($evaluated)) {
            return $evaluated;
        }

        if (is_numeric($evaluated)) {
            return (int) $evaluated;
        }

        $configSize = config('filament-qr.preview.render_size', 320);

        return is_numeric($configSize) ? (int) $configSize : 320;
    }

    public function previewHeading(string|Closure $heading): static
    {
        $this->previewHeading = $heading;

        return $this;
    }

    public function getPreviewHeading(): string
    {
        $evaluated = $this->evaluate($this->previewHeading);
        if (is_string($evaluated)) {
            return $evaluated;
        }

        if (is_scalar($evaluated) || $evaluated instanceof Stringable) {
            return (string) $evaluated;
        }

        return 'QR Code Preview';
    }

    public function copyable(bool|Closure $condition = true): static
    {
        $this->isCopyable = $condition;

        return $this;
    }

    public function isCopyable(): bool
    {
        return (bool) $this->evaluate($this->isCopyable);
    }

    public function copyMessage(string|Closure $message): static
    {
        $this->copyMessage = $message;

        return $this;
    }

    public function getCopyMessage(): string
    {
        $evaluated = $this->evaluate($this->copyMessage);
        if (is_string($evaluated)) {
            return $evaluated;
        }

        if (is_scalar($evaluated) || $evaluated instanceof Stringable) {
            return (string) $evaluated;
        }

        return 'Copied to clipboard!';
    }

    public function renderPreviewQrCode(): ?string
    {
        $payload = $this->getQrPayload();

        if (empty($payload)) {
            return null;
        }

        /** @var QrCodeRenderer $qrCodeRenderer */
        $qrCodeRenderer = app(QrCodeRenderer::class);

        return $qrCodeRenderer->render(
            payload: $payload,
            size: $this->getPreviewSize(),
            margin: $this->getQrMargin(),
            format: $this->getQrFormat(),
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
    }
}
