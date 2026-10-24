<?php

namespace Linkxtr\FilamentQr\Tables\Columns;

use Closure;
use Filament\Tables\Columns\Column;
use Linkxtr\FilamentQr\Concerns\HasPayloadFormatters;
use Linkxtr\FilamentQr\Concerns\HasQrCodeStyling;
use Linkxtr\FilamentQr\Services\QrCodeRenderer;

class QrCodeColumn extends Column
{
    use HasPayloadFormatters;
    use HasQrCodeStyling;

    protected string $view = 'filament-qr::tables.columns.qr-code-column';

    protected bool | Closure $isDownloadable = false;

    protected bool | Closure $isClickableToPreview = true;

    protected ?Closure $payloadResolver = null;

    public function payload(Closure $callback): static
    {
        $this->payloadResolver = $callback;

        return $this;
    }

    public function downloadable(bool | Closure $condition = true): static
    {
        $this->isDownloadable = $condition;

        return $this;
    }

    public function isDownloadable(): bool
    {
        return (bool) $this->evaluate($this->isDownloadable);
    }

    public function previewable(bool | Closure $condition = true): static
    {
        $this->isClickableToPreview = $condition;

        return $this;
    }

    public function isPreviewable(): bool
    {
        return (bool) $this->evaluate($this->isClickableToPreview);
    }

    public function getQrPayload(): ?string
    {
        $raw = $this->payloadResolver ? $this->evaluate($this->payloadResolver) : $this->getState();

        return $this->resolveFormattedPayload($raw);
    }

    public function renderQrCode(): ?string
    {
        $payload = $this->getQrPayload();

        if (empty($payload)) {
            return null;
        }

        /** @var QrCodeRenderer $renderer */
        $renderer = app(QrCodeRenderer::class);

        return $renderer->render(
            payload: $payload,
            size: $this->getQrSize(),
            margin: $this->getQrMargin(),
            format: $this->getQrFormat(),
            errorCorrection: $this->getQrErrorCorrection(),
            color: $this->getQrColor(),
            backgroundColor: $this->getQrBackgroundColor(),
            gradient: $this->getQrGradient(),
            eyeColors: $this->getQrEyeColors(),
            logoPath: $this->getQrLogoPath(),
            logoSize: $this->getQrLogoSize()
        );
    }
}
