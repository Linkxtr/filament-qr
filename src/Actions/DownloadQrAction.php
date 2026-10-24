<?php

namespace Linkxtr\FilamentQr\Actions;

use Filament\Actions\Action;
use Illuminate\Support\Facades\Response;
use Linkxtr\FilamentQr\Concerns\HasPayloadFormatters;
use Linkxtr\FilamentQr\Concerns\HasQrCodeStyling;
use Linkxtr\FilamentQr\Services\QrCodeRenderer;

class DownloadQrAction extends Action
{
    use HasPayloadFormatters;
    use HasQrCodeStyling;

    protected string $filename = 'qrcode';

    public static function getDefaultName(): ?string
    {
        return 'downloadQr';
    }

    protected function setUp(): void
    {
        parent::setUp();

        $this->label('Download QR');
        $this->icon('heroicon-o-arrow-down-tray');

        $this->action(function () {
            $payload = $this->resolveFormattedPayload($this->getRecord());
            $format = $this->getQrFormat();

            /** @var QrCodeRenderer $renderer */
            $renderer = app(QrCodeRenderer::class);

            $output = $renderer->render(
                payload: (string) $payload,
                size: $this->getQrSize(),
                margin: $this->getQrMargin(),
                format: $format,
                errorCorrection: $this->getQrErrorCorrection(),
                color: $this->getQrColor(),
                backgroundColor: $this->getQrBackgroundColor(),
                gradient: $this->getQrGradient(),
                eyeColors: $this->getQrEyeColors(),
                logoPath: $this->getQrLogoPath(),
                logoSize: $this->getQrLogoSize()
            );

            $contentType = match ($format) {
                'svg' => 'image/svg+xml',
                'webp' => 'image/webp',
                default => 'image/png',
            };

            return response()->streamDownload(function () use ($output) {
                echo $output;
            }, "{$this->filename}.{$format}", ['Content-Type' => $contentType]);
        });
    }

    public function filename(string $name): static
    {
        $this->filename = $name;

        return $this;
    }
}
