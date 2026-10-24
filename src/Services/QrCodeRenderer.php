<?php

namespace Linkxtr\FilamentQr\Services;

use Linkxtr\FilamentQr\Enums\ErrorCorrectionLevel;
use Linkxtr\FilamentQr\Enums\QrFormat;
use Linkxtr\QrCode\Facades\QrCode;

class QrCodeRenderer
{
    /**
     * Render the given payload as an inline SVG string or base64 data URI.
     */
    public function render(
        string $payload,
        int $size = 160,
        int $margin = 1,
        string $format = 'svg',
        string $errorCorrection = 'M',
        string $color = '#000000',
        string $backgroundColor = '#ffffff',
        ?array $gradient = null,
        ?array $eyeColors = null,
        ?string $logoPath = null,
        float $logoSize = 0.2
    ): string {
        $builder = QrCode::format($format)
            ->size($size)
            ->margin($margin)
            ->errorCorrection($errorCorrection)
            ->color($this->hexToRgb($color))
            ->backgroundColor($this->hexToRgb($backgroundColor));

        if ($gradient) {
            $from = $this->hexToRgb($gradient['from']);
            $to = $this->hexToRgb($gradient['to']);
            $builder->gradient($from[0], $from[1], $from[2], $to[0], $to[1], $to[2], $gradient['type'] ?? 'vertical');
        }

        if ($eyeColors) {
            foreach ($eyeColors as $index => $colors) {
                $inner = $this->hexToRgb($colors['inner']);
                $outer = $this->hexToRgb($colors['outer']);
                $builder->eyeColor($index, $inner[0], $inner[1], $inner[2], $outer[0], $outer[1], $outer[2]);
            }
        }

        if ($logoPath) {
            $builder->merge($logoPath, $logoSize, true);
        }

        $output = $builder->generate($payload);

        if ($format === 'svg') {
            return (string) $output;
        }

        return 'data:image/' . $format . ';base64,' . base64_encode((string) $output);
    }

    /**
     * Convert HEX string (#ffffff or #fff) to [r, g, b] array.
     */
    protected function hexToRgb(string $hex): array
    {
        $hex = ltrim($hex, '#');

        if (strlen($hex) === 3) {
            $hex = $hex[0] . $hex[0] . $hex[1] . $hex[1] . $hex[2] . $hex[2];
        }

        return [
            hexdec(substr($hex, 0, 2)),
            hexdec(substr($hex, 2, 2)),
            hexdec(substr($hex, 4, 2)),
        ];
    }
}
