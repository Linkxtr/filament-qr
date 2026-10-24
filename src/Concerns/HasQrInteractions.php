<?php

namespace Linkxtr\FilamentQr\Concerns;

use Closure;
use Illuminate\Database\Eloquent\Model;
use Linkxtr\FilamentQr\Services\QrCodeRenderer;

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
            return (string) $this->evaluate($this->downloadFilename);
        }

        $record = $this->getRecord();
        if ($record instanceof Model) {
            $key = $record->getKey();
        } elseif (is_array($record)) {
            $key = $record['id'] ?? $record['key'] ?? 'code';
        } else {
            $key = 'code';
        }

        return "qr-{$key}";
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
        return (int) ($this->evaluate($this->previewSize) ?? config('filament-qr.preview.render_size', 320));
    }

    public function previewHeading(string|Closure $heading): static
    {
        $this->previewHeading = $heading;

        return $this;
    }

    public function getPreviewHeading(): string
    {
        return (string) ($this->evaluate($this->previewHeading) ?? 'QR Code Preview');
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
        return (string) $this->evaluate($this->copyMessage);
    }

    public function renderPreviewQrCode(): ?string
    {
        $payload = $this->getQrPayload();

        if (empty($payload)) {
            return null;
        }

        /** @var QrCodeRenderer $renderer */
        $renderer = app(QrCodeRenderer::class);

        return $renderer->render(
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
