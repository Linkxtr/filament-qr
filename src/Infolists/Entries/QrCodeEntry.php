<?php

declare(strict_types=1);

namespace Linkxtr\FilamentQr\Infolists\Entries;

use Closure;
use Filament\Infolists\Components\Entry;
use Illuminate\Database\Eloquent\Model;
use Linkxtr\FilamentQr\Concerns\HasPayloadFormatters;
use Linkxtr\FilamentQr\Concerns\HasQrCodeStyling;
use Linkxtr\FilamentQr\Concerns\HasQrInteractions;
use Linkxtr\FilamentQr\Services\QrCodeRenderer;
use Throwable;

final class QrCodeEntry extends Entry
{
    use HasPayloadFormatters;
    use HasQrCodeStyling;
    use HasQrInteractions;

    protected string $view = 'filament-qr::infolists.entries.qr-code-entry';

    private ?Closure $payloadResolver = null;

    /**
     * @param  array<string, mixed>|class-string<Model>|Closure|Model|null  $record
     */
    public function record(Model|array|Closure|string|null $record): static
    {
        $this->model($record);

        return $this;
    }

    public function payload(Closure $callback): static
    {
        $this->payloadResolver = $callback;

        return $this;
    }

    public function getQrPayload(): ?string
    {
        if ($this->payloadResolver instanceof Closure) {
            $raw = $this->evaluate($this->payloadResolver);
        } else {
            try {
                $raw = $this->getState();
            } catch (Throwable) {
                $raw = null;
            }
        }

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
