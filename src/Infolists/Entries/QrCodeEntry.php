<?php

namespace Linkxtr\FilamentQr\Infolists\Entries;

use Closure;
use Filament\Infolists\Components\Entry;
use Linkxtr\FilamentQr\Concerns\HasPayloadFormatters;
use Linkxtr\FilamentQr\Concerns\HasQrCodeStyling;
use Linkxtr\FilamentQr\Services\QrCodeRenderer;

class QrCodeEntry extends Entry
{
    use HasPayloadFormatters;
    use HasQrCodeStyling;

    protected string $view = 'filament-qr::infolists.entries.qr-code-entry';

    protected ?Closure $payloadResolver = null;

    public function payload(Closure $callback): static
    {
        $this->payloadResolver = $callback;

        return $this;
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
