<?php

namespace Linkxtr\FilamentQr\Concerns;

use Closure;
use Filament\Support\Colors\Color;
use Filament\Support\Facades\FilamentColor;
use Linkxtr\FilamentQr\Enums\ErrorCorrectionLevel;
use Linkxtr\FilamentQr\Enums\QrFormat;

trait HasQrCodeStyling
{
    protected int | Closure | null $qrSize = null;

    protected int | Closure | null $qrMargin = null;

    protected QrFormat | string | Closure | null $qrFormat = null;

    protected ErrorCorrectionLevel | string | Closure | null $qrErrorCorrection = null;

    protected string | Closure | null $qrColor = null;

    protected string | Closure | null $qrBackgroundColor = null;

    protected ?array $qrGradient = null;

    protected ?array $qrEyeColors = null;

    protected string | Closure | null $qrLogoPath = null;

    protected float | Closure | null $qrLogoSize = null;

    public function qrSize(int | Closure $size): static
    {
        $this->qrSize = $size;

        return $this;
    }

    public function getQrSize(): int
    {
        return (int) ($this->evaluate($this->qrSize) ?? config('filament-qr.defaults.size', 160));
    }

    public function qrMargin(int | Closure $margin): static
    {
        $this->qrMargin = $margin;

        return $this;
    }

    public function getQrMargin(): int
    {
        return (int) ($this->evaluate($this->qrMargin) ?? config('filament-qr.defaults.margin', 1));
    }

    public function qrFormat(QrFormat | string | Closure $format): static
    {
        $this->qrFormat = $format;

        return $this;
    }

    public function getQrFormat(): string
    {
        $format = $this->evaluate($this->qrFormat) ?? config('filament-qr.defaults.format', 'svg');

        if ($format instanceof QrFormat) {
            return $format->value;
        }

        return (string) $format;
    }

    public function qrErrorCorrection(ErrorCorrectionLevel | string | Closure $level): static
    {
        $this->qrErrorCorrection = $level;

        return $this;
    }

    public function getQrErrorCorrection(): string
    {
        $level = $this->evaluate($this->qrErrorCorrection) ?? config('filament-qr.defaults.error_correction', 'M');

        if ($level instanceof ErrorCorrectionLevel) {
            return $level->value;
        }

        return (string) $level;
    }

    public function qrColor(string | Closure $color): static
    {
        $this->qrColor = $color;

        return $this;
    }

    public function getQrColor(): string
    {
        $color = $this->evaluate($this->qrColor) ?? config('filament-qr.defaults.color', '#000000');

        return $this->resolveColorToken($color);
    }

    public function qrBackgroundColor(string | Closure $color): static
    {
        $this->qrBackgroundColor = $color;

        return $this;
    }

    public function getQrBackgroundColor(): string
    {
        $color = $this->evaluate($this->qrBackgroundColor) ?? config('filament-qr.defaults.background_color', '#ffffff');

        return $this->resolveColorToken($color);
    }

    public function qrGradient(string $from, string $to, string $type = 'vertical'): static
    {
        $this->qrGradient = [
            'from' => $this->resolveColorToken($from),
            'to' => $this->resolveColorToken($to),
            'type' => $type,
        ];

        return $this;
    }

    public function getQrGradient(): ?array
    {
        return $this->qrGradient;
    }

    public function qrEyeColor(int $eyeIndex, string $inner, string $outer): static
    {
        $this->qrEyeColors[$eyeIndex] = [
            'inner' => $this->resolveColorToken($inner),
            'outer' => $this->resolveColorToken($outer),
        ];

        return $this;
    }

    public function getQrEyeColors(): ?array
    {
        return $this->qrEyeColors;
    }

    public function qrLogo(string | Closure $path, float | Closure $size = 0.2): static
    {
        $this->qrLogoPath = $path;
        $this->qrLogoSize = $size;

        return $this;
    }

    public function getQrLogoPath(): ?string
    {
        return $this->evaluate($this->qrLogoPath);
    }

    public function getQrLogoSize(): float
    {
        return (float) ($this->evaluate($this->qrLogoSize) ?? 0.2);
    }

    protected function resolveColorToken(string $color): string
    {
        if (str_starts_with($color, '#') || str_starts_with($color, 'rgb')) {
            return $color;
        }

        $colorMap = [
            'primary' => '#fbbf24',
            'danger' => '#ef4444',
            'gray' => '#6b7280',
            'info' => '#3b82f6',
            'success' => '#10b981',
            'warning' => '#f59e0b',
        ];

        return $colorMap[$color] ?? $color;
    }
}
