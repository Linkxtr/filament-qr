<?php

namespace Linkxtr\FilamentQr\Concerns;

use Closure;
use Filament\Support\Colors\Color;
use Filament\Support\Facades\FilamentColor;
use Linkxtr\FilamentQr\Enums\ErrorCorrectionLevel;
use Linkxtr\FilamentQr\Enums\EyeStyle;
use Linkxtr\FilamentQr\Enums\GradientType;
use Linkxtr\FilamentQr\Enums\QrFormat;
use Linkxtr\FilamentQr\Enums\QrStyle;

trait HasQrCodeStyling
{
    protected int|Closure|null $qrSize = null;

    protected int|Closure|null $qrMargin = null;

    protected QrFormat|string|Closure|null $qrFormat = null;

    protected ErrorCorrectionLevel|string|Closure|null $qrErrorCorrection = null;

    protected string|Closure|null $qrColor = null;

    protected string|Closure|null $qrBackgroundColor = null;

    /** @var array{from: string|Closure, to: string|Closure, type?: string|GradientType|Closure}|Closure|null */
    protected array|Closure|null $qrGradient = null;

    /** @var array<int, array{inner: string|Closure, outer?: string|Closure|null}>|Closure|null */
    protected array|Closure|null $qrEyeColors = null;

    protected EyeStyle|string|Closure|null $qrEyeStyle = null;

    protected EyeStyle|string|Closure|null $qrInternalEyeStyle = null;

    protected QrStyle|string|Closure|null $qrStyle = null;

    protected float|Closure|null $qrStyleSize = null;

    protected string|Closure|null $qrLogoPath = null;

    protected float|Closure|null $qrLogoSize = null;

    protected int|Closure|null $qrDpi = null;

    protected int|Closure|null $qrResolution = null;

    protected string|Closure|null $qrEncoding = null;

    public function qrSize(int|Closure $size): static
    {
        $this->qrSize = $size;

        return $this;
    }

    public function getQrSize(): int
    {
        return (int) ($this->evaluateStylingValue($this->qrSize) ?? config('filament-qr.defaults.size', 160));
    }

    public function qrMargin(int|Closure $margin): static
    {
        $this->qrMargin = $margin;

        return $this;
    }

    public function getQrMargin(): int
    {
        return (int) ($this->evaluateStylingValue($this->qrMargin) ?? config('filament-qr.defaults.margin', 1));
    }

    public function qrFormat(QrFormat|string|Closure $format): static
    {
        $this->qrFormat = $format;

        return $this;
    }

    public function getQrFormat(): string
    {
        $format = $this->evaluateStylingValue($this->qrFormat) ?? config('filament-qr.defaults.format', 'svg');

        if ($format instanceof QrFormat) {
            return $format->value;
        }

        return (string) $format;
    }

    public function svg(): static
    {
        return $this->qrFormat(QrFormat::Svg);
    }

    public function png(): static
    {
        return $this->qrFormat(QrFormat::Png);
    }

    public function webp(): static
    {
        return $this->qrFormat(QrFormat::Webp);
    }

    public function eps(): static
    {
        return $this->qrFormat(QrFormat::Eps);
    }

    public function qrErrorCorrection(ErrorCorrectionLevel|string|Closure $level): static
    {
        $this->qrErrorCorrection = $level;

        return $this;
    }

    public function getQrErrorCorrection(): string
    {
        $level = $this->evaluateStylingValue($this->qrErrorCorrection) ?? config('filament-qr.defaults.error_correction', 'M');

        if ($level instanceof ErrorCorrectionLevel) {
            return $level->value;
        }

        return (string) $level;
    }

    public function qrColor(string|Closure $color): static
    {
        $this->qrColor = $color;

        return $this;
    }

    public function getQrColor(): string
    {
        $color = $this->evaluateStylingValue($this->qrColor) ?? config('filament-qr.defaults.color', '#000000');

        return $this->resolveColorToken($color);
    }

    public function qrBackgroundColor(string|Closure $color): static
    {
        $this->qrBackgroundColor = $color;

        return $this;
    }

    public function getQrBackgroundColor(): string
    {
        $color = $this->evaluateStylingValue($this->qrBackgroundColor) ?? config('filament-qr.defaults.background_color', '#ffffff');

        return $this->resolveColorToken($color);
    }

    public function qrGradient(string|Closure $from, string|Closure $to, GradientType|string|Closure $type = 'vertical'): static
    {
        $this->qrGradient = [
            'from' => $from,
            'to' => $to,
            'type' => $type,
        ];

        return $this;
    }

    /**
     * @return array{from: string, to: string, type?: string}|null
     */
    public function getQrGradient(): ?array
    {
        if ($this->qrGradient === null) {
            return null;
        }

        $gradient = $this->evaluateStylingValue($this->qrGradient);

        if (! is_array($gradient) || empty($gradient['from']) || empty($gradient['to'])) {
            return null;
        }

        $from = $this->evaluateStylingValue($gradient['from']);
        $to = $this->evaluateStylingValue($gradient['to']);
        $rawType = $this->evaluateStylingValue($gradient['type'] ?? 'vertical');

        $type = $rawType instanceof GradientType ? $rawType->value : (string) $rawType;

        return [
            'from' => $this->resolveColorToken((string) $from),
            'to' => $this->resolveColorToken((string) $to),
            'type' => $type,
        ];
    }

    /**
     * Set specific eye colors.
     *
     * @param  array<int, array{inner: string|Closure, outer?: string|Closure|null}>|Closure  $eyeColors
     */
    public function qrEyeColors(array|Closure $eyeColors): static
    {
        $this->qrEyeColors = $eyeColors;

        return $this;
    }

    /**
     * Shortcut to set all three eye colors simultaneously.
     */
    public function qrEyeColor(string|Closure $inner, string|Closure|null $outer = null): static
    {
        $this->qrEyeColors = [
            0 => ['inner' => $inner, 'outer' => $outer],
            1 => ['inner' => $inner, 'outer' => $outer],
            2 => ['inner' => $inner, 'outer' => $outer],
        ];

        return $this;
    }

    /**
     * @return array<int, array{inner: string, outer?: string|null}>|null
     */
    public function getQrEyeColors(): ?array
    {
        if ($this->qrEyeColors === null) {
            return null;
        }

        $rawEyes = $this->evaluateStylingValue($this->qrEyeColors);

        if (! is_array($rawEyes)) {
            return null;
        }

        $resolved = [];
        foreach ($rawEyes as $index => $eye) {
            if (! is_array($eye) || ! isset($eye['inner'])) {
                continue;
            }

            $inner = $this->evaluateStylingValue($eye['inner']);
            $outer = isset($eye['outer']) ? $this->evaluateStylingValue($eye['outer']) : null;

            $resolved[(int) $index] = [
                'inner' => $this->resolveColorToken((string) $inner),
                'outer' => $outer !== null ? $this->resolveColorToken((string) $outer) : null,
            ];
        }

        return $resolved;
    }

    public function qrEyeStyle(EyeStyle|string|Closure $style): static
    {
        $this->qrEyeStyle = $style;

        return $this;
    }

    public function getQrEyeStyle(): ?string
    {
        $style = $this->evaluateStylingValue($this->qrEyeStyle);

        if ($style instanceof EyeStyle) {
            return $style->value;
        }

        return $style !== null ? (string) $style : null;
    }

    public function qrInternalEyeStyle(EyeStyle|string|Closure $style): static
    {
        $this->qrInternalEyeStyle = $style;

        return $this;
    }

    public function getQrInternalEyeStyle(): ?string
    {
        $style = $this->evaluateStylingValue($this->qrInternalEyeStyle);

        if ($style instanceof EyeStyle) {
            return $style->value;
        }

        return $style !== null ? (string) $style : null;
    }

    public function qrStyle(QrStyle|string|Closure $style, float|Closure|null $size = null): static
    {
        $this->qrStyle = $style;
        if ($size !== null) {
            $this->qrStyleSize = $size;
        }

        return $this;
    }

    public function getQrStyle(): ?string
    {
        $style = $this->evaluateStylingValue($this->qrStyle);

        if ($style instanceof QrStyle) {
            return $style->value;
        }

        return $style !== null ? (string) $style : null;
    }

    public function getQrStyleSize(): ?float
    {
        $size = $this->evaluateStylingValue($this->qrStyleSize);

        return $size !== null ? (float) $size : null;
    }

    public function qrLogo(string|Closure $path, float|Closure $size = 0.2): static
    {
        $this->qrLogoPath = $path;
        $this->qrLogoSize = $size;

        return $this;
    }

    public function getQrLogoPath(): ?string
    {
        return $this->evaluateStylingValue($this->qrLogoPath);
    }

    public function getQrLogoSize(): float
    {
        return (float) ($this->evaluateStylingValue($this->qrLogoSize) ?? 0.2);
    }

    public function qrDpi(int|Closure $dpi): static
    {
        $this->qrDpi = $dpi;

        return $this;
    }

    public function getQrDpi(): ?int
    {
        $dpi = $this->evaluateStylingValue($this->qrDpi);

        return $dpi !== null ? (int) $dpi : null;
    }

    public function qrResolution(int|Closure $resolution): static
    {
        $this->qrResolution = $resolution;

        return $this;
    }

    public function getQrResolution(): ?int
    {
        $res = $this->evaluateStylingValue($this->qrResolution);

        return $res !== null ? (int) $res : null;
    }

    public function qrEncoding(string|Closure $encoding): static
    {
        $this->qrEncoding = $encoding;

        return $this;
    }

    public function getQrEncoding(): string
    {
        return (string) ($this->evaluateStylingValue($this->qrEncoding) ?? 'UTF-8');
    }

    public function resolveColorToken(string $color): string
    {
        $color = trim($color);

        if (str_starts_with($color, '#') || str_starts_with($color, 'rgb')) {
            return $color;
        }

        // Check for shade notation, e.g. "primary-600", "primary.600", "primary:600"
        $shade = 500;
        $token = $color;
        if (preg_match('/^([a-z0-9_-]+)[.:-](\d{2,3})$/i', $color, $matches)) {
            $token = strtolower($matches[1]);
            $shade = (int) $matches[2];
        } else {
            $token = strtolower($token);
        }

        // Resolve dynamically from FilamentColor facade if available
        if (class_exists(FilamentColor::class)) {
            try {
                /** @var mixed $registeredColor */
                $registeredColor = FilamentColor::getColor($token);

                if (! empty($registeredColor)) {
                    if (is_array($registeredColor)) {
                        $shadeVal = $registeredColor[$shade] ?? $registeredColor[500] ?? $registeredColor[600] ?? reset($registeredColor);
                        if (is_string($shadeVal)) {
                            if (str_starts_with($shadeVal, '#')) {
                                return $shadeVal;
                            }
                            if (class_exists(Color::class)) {
                                return Color::convertToHex($shadeVal);
                            }
                        }
                    } elseif (is_string($registeredColor)) {
                        if (str_starts_with($registeredColor, '#')) {
                            return $registeredColor;
                        }
                        if (class_exists(Color::class)) {
                            return Color::convertToHex($registeredColor);
                        }
                    }
                }
            } catch (\Throwable) {
                // Ignore and fall back
            }
        }

        $fallbackMap = [
            'primary' => '#fe9a00',
            'danger' => '#ef4444',
            'gray' => '#6b7280',
            'info' => '#3b82f6',
            'success' => '#10b981',
            'warning' => '#f59e0b',
            'secondary' => '#64748b',
        ];

        return $fallbackMap[$token] ?? $color;
    }

    /**
     * @param  array<string, mixed>  $namedInjections
     */
    protected function evaluateStylingValue(mixed $value, array $namedInjections = []): mixed
    {
        return $this->evaluate($value, $namedInjections);
    }
}
