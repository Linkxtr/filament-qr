<?php

declare(strict_types=1);

namespace Linkxtr\FilamentQr\Services;

use Imagick;
use Linkxtr\FilamentQr\Enums\EyeStyle;
use Linkxtr\FilamentQr\Enums\QrStyle;
use Linkxtr\QrCode\Facades\QrCode;
use Linkxtr\QrCode\ValueObjects\Colors\Rgb;
use Throwable;

final class QrCodeRenderer
{
    /**
     * Render the given payload as raw output (raw SVG string or binary image).
     *
     * @param  array{from: string, to: string, type?: string}|null  $gradient
     * @param  array<int, array{inner: string, outer?: string|null}>|null  $eyeColors
     */
    public function renderRaw(
        string $payload,
        int $size = 160,
        int $margin = 1,
        string $format = 'svg',
        string $errorCorrection = 'M',
        string $color = '#000000',
        string $backgroundColor = '#ffffff',
        ?array $gradient = null,
        ?array $eyeColors = null,
        EyeStyle|string|null $eyeStyle = null,
        EyeStyle|string|null $internalEyeStyle = null,
        QrStyle|string|null $style = null,
        ?float $styleSize = null,
        ?string $logoPath = null,
        float $logoSize = 0.2,
        ?int $dpi = null,
        ?int $resolution = null,
        string $encoding = 'UTF-8'
    ): string {
        $colorRgb = Rgb::parse($color);
        $bgRgb = Rgb::parse($backgroundColor);

        $builder = QrCode::format($format)
            ->size($size)
            ->margin($margin)
            ->errorCorrection($errorCorrection)
            ->color($colorRgb->red, $colorRgb->green, $colorRgb->blue)
            ->backgroundColor($bgRgb->red, $bgRgb->green, $bgRgb->blue);

        if ($gradient) {
            $from = $gradient['from'];
            $to = $gradient['to'];
            $type = $gradient['type'] ?? 'vertical';
            $builder = $builder->gradient($from, $to, $type);
        }

        if ($eyeColors) {
            foreach ($eyeColors as $index => $colors) {
                $builder = $builder->eyeColor((int) $index, $colors['inner'], $colors['outer'] ?? null);
            }
        }

        if ($eyeStyle) {
            $eyeVal = $eyeStyle instanceof EyeStyle ? $eyeStyle->value : $eyeStyle;
            $builder = $builder->eye($eyeVal);
        }

        if ($internalEyeStyle) {
            $internalVal = $internalEyeStyle instanceof EyeStyle ? $internalEyeStyle->value : $internalEyeStyle;
            $builder = $builder->internalEye($internalVal);
        }

        if ($style) {
            $styleVal = $style instanceof QrStyle ? $style->value : $style;
            $builder = $builder->style($styleVal, $styleSize);
        }

        if ($encoding && $encoding !== 'UTF-8') {
            $builder = $builder->encoding($encoding);
        }

        if ($logoPath) {
            $builder = $builder->merge($logoPath, $logoSize);
        }

        $output = (string) $builder->generate($payload);

        if (($dpi || $resolution) && in_array($format, ['png', 'webp'], true) && extension_loaded('imagick')) {
            $dpiVal = (float) ($dpi ?? $resolution);
            try {
                $imagick = new Imagick;
                $imagick->readImageBlob($output);
                $imagick->setImageResolution($dpiVal, $dpiVal);
                $imagick->setImageUnits(Imagick::RESOLUTION_PIXELSPERINCH);
                $output = $imagick->getImageBlob();
            } catch (Throwable) {
                // If Imagick fails, retain original output
            }
        }

        return $output;
    }

    /**
     * Render the given payload as an inline SVG string or base64 data URI.
     *
     * @param  array{from: string, to: string, type?: string}|null  $gradient
     * @param  array<int, array{inner: string, outer?: string|null}>|null  $eyeColors
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
        EyeStyle|string|null $eyeStyle = null,
        EyeStyle|string|null $internalEyeStyle = null,
        QrStyle|string|null $style = null,
        ?float $styleSize = null,
        ?string $logoPath = null,
        float $logoSize = 0.2,
        ?int $dpi = null,
        ?int $resolution = null,
        string $encoding = 'UTF-8'
    ): string {
        $raw = $this->renderRaw(
            payload: $payload,
            size: $size,
            margin: $margin,
            format: $format,
            errorCorrection: $errorCorrection,
            color: $color,
            backgroundColor: $backgroundColor,
            gradient: $gradient,
            eyeColors: $eyeColors,
            eyeStyle: $eyeStyle,
            internalEyeStyle: $internalEyeStyle,
            style: $style,
            styleSize: $styleSize,
            logoPath: $logoPath,
            logoSize: $logoSize,
            dpi: $dpi,
            resolution: $resolution,
            encoding: $encoding
        );

        if ($format === 'svg') {
            return $raw;
        }

        return 'data:image/'.$format.';base64,'.base64_encode($raw);
    }

    /**
     * Render as a data URI regardless of format.
     *
     * @param  array{from: string, to: string, type?: string}|null  $gradient
     * @param  array<int, array{inner: string, outer?: string|null}>|null  $eyeColors
     */
    public function renderDataUri(
        string $payload,
        int $size = 160,
        int $margin = 1,
        string $format = 'svg',
        string $errorCorrection = 'M',
        string $color = '#000000',
        string $backgroundColor = '#ffffff',
        ?array $gradient = null,
        ?array $eyeColors = null,
        EyeStyle|string|null $eyeStyle = null,
        EyeStyle|string|null $internalEyeStyle = null,
        QrStyle|string|null $style = null,
        ?float $styleSize = null,
        ?string $logoPath = null,
        float $logoSize = 0.2,
        ?int $dpi = null,
        ?int $resolution = null,
        string $encoding = 'UTF-8'
    ): string {
        $raw = $this->renderRaw(
            payload: $payload,
            size: $size,
            margin: $margin,
            format: $format,
            errorCorrection: $errorCorrection,
            color: $color,
            backgroundColor: $backgroundColor,
            gradient: $gradient,
            eyeColors: $eyeColors,
            eyeStyle: $eyeStyle,
            internalEyeStyle: $internalEyeStyle,
            style: $style,
            styleSize: $styleSize,
            logoPath: $logoPath,
            logoSize: $logoSize,
            dpi: $dpi,
            resolution: $resolution,
            encoding: $encoding
        );

        $mime = $format === 'svg' ? 'image/svg+xml' : 'image/'.$format;

        return 'data:'.$mime.';base64,'.base64_encode($raw);
    }

    /**
     * Convert HEX string (#ffffff or #fff) to [r, g, b] array.
     *
     * @return array{0: int, 1: int, 2: int}
     */
    public function hexToRgb(string $hex): array
    {
        $rgb = Rgb::parse($hex);

        return [$rgb->red, $rgb->green, $rgb->blue];
    }
}
