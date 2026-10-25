<?php

declare(strict_types=1);

namespace Linkxtr\FilamentQr\Tables\Columns;

use Closure;
use Filament\Tables\Columns\Column;
use Linkxtr\FilamentQr\Concerns\HasPayloadFormatters;
use Linkxtr\FilamentQr\Concerns\HasQrCodeStyling;
use Linkxtr\FilamentQr\Concerns\HasQrInteractions;
use Linkxtr\FilamentQr\Services\QrCodeRenderer;

final class QrCodeColumn extends Column
{
    use HasPayloadFormatters;
    use HasQrCodeStyling;
    use HasQrInteractions;

    protected string $view = 'filament-qr::tables.columns.qr-code-column';

    private ?Closure $payloadResolver = null;

    public function payload(Closure $callback): static
    {
        $this->payloadResolver = $callback;

        return $this;
    }

    public function getQrPayload(): ?string
    {
        $raw = $this->payloadResolver instanceof Closure
            ? $this->evaluate($this->payloadResolver)
            : ($this->getState() ?? $this->evaluate($this->getStateUsing));

        return $this->resolveFormattedPayload($raw);
    }

    public function renderQrCode(): ?string
    {
        $payload = $this->getQrPayload();

        if (empty($payload)) {
            return null;
        }

        /** @var QrCodeRenderer $qrCodeRenderer */
        $qrCodeRenderer = app(QrCodeRenderer::class);

        return $qrCodeRenderer->render(
            payload: $payload,
            size: $this->getQrSize(),
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
