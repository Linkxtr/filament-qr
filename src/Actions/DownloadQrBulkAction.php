<?php

declare(strict_types=1);

namespace Linkxtr\FilamentQr\Actions;

use Closure;
use Filament\Actions\BulkAction;
use Illuminate\Database\Eloquent\Collection as EloquentCollection;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Collection;
use Illuminate\Support\LazyCollection;
use Linkxtr\FilamentQr\Concerns\HasPayloadFormatters;
use Linkxtr\FilamentQr\Concerns\HasQrCodeStyling;
use Linkxtr\FilamentQr\Services\QrCodeRenderer;
use RuntimeException;
use Stringable;
use Symfony\Component\HttpFoundation\StreamedResponse;
use ZipArchive;

final class DownloadQrBulkAction extends BulkAction
{
    use HasPayloadFormatters;
    use HasQrCodeStyling;

    private string|Closure $zipFilename = 'qrcodes.zip';

    private string|Closure|null $recordFilename = null;

    private ?Closure $payloadResolver = null;

    /** @var EloquentCollection<int, Model>|Collection<int, mixed>|null */
    private EloquentCollection|Collection|null $recordsCollection = null;

    protected function setUp(): void
    {
        parent::setUp();

        $this->label('Download QR Codes');
        $this->icon('heroicon-o-arrow-down-tray');

        $this->action(fn (EloquentCollection|Collection $records): ?StreamedResponse => $this->downloadZip($records));

        $this->deselectRecordsAfterCompletion();
    }

    public static function getDefaultName(): string
    {
        return 'downloadQrBulk';
    }

    /**
     * @return EloquentCollection<int, Model>|Collection<int, mixed>|LazyCollection<int, mixed>
     */
    public function getSelectedRecords(): EloquentCollection|Collection|LazyCollection
    {
        if ($this->recordsCollection instanceof Collection) {
            return $this->recordsCollection;
        }

        return parent::getSelectedRecords();
    }

    public function shouldDeselectRecordsAfterCompletion(): bool
    {
        return parent::shouldDeselectRecordsAfterCompletion() && $this->getLivewire() !== null;
    }

    /**
     * @param  EloquentCollection<int, Model>|Collection<int, mixed>  $records
     */
    public function records(EloquentCollection|Collection $records): static
    {
        $this->recordsCollection = $records;

        return $this;
    }

    public function payload(Closure $callback): static
    {
        $this->payloadResolver = $callback;

        return $this;
    }

    public function getQrPayload(mixed $record): ?string
    {
        if ($this->payloadResolver instanceof Closure) {
            $raw = $this->evaluate($this->payloadResolver, ['record' => $record]);
        } elseif ($record instanceof Model) {
            $raw = $record->getAttribute('qr') ?? $record->getAttribute('qr_code') ?? $record->getKey();
        } elseif (is_array($record)) {
            $raw = $record['qr'] ?? $record['qr_code'] ?? $record['url'] ?? $record['id'] ?? null;
        } elseif (is_object($record) && isset($record->qr)) {
            $raw = $record->qr;
        } elseif (is_object($record) && isset($record->id)) {
            $raw = $record->id;
        } else {
            $raw = $record;
        }

        return $this->resolveFormattedPayload($raw);
    }

    public function filename(string|Closure $name): static
    {
        $this->zipFilename = $name;

        return $this;
    }

    public function zipFilename(string|Closure $name): static
    {
        return $this->filename($name);
    }

    public function archiveFilename(string|Closure $name): static
    {
        return $this->filename($name);
    }

    public function getFilename(): string
    {
        $evaluated = $this->evaluate($this->zipFilename);
        $filename = is_string($evaluated) ? $evaluated : (is_scalar($evaluated) || $evaluated instanceof Stringable ? (string) $evaluated : 'qrcodes.zip');
        if ($filename === '') {
            $filename = 'qrcodes.zip';
        }

        return str_ends_with(strtolower($filename), '.zip') ? $filename : "{$filename}.zip";
    }

    public function getZipFilename(): string
    {
        return $this->getFilename();
    }

    public function getArchiveFilename(): string
    {
        return $this->getFilename();
    }

    public function recordFilename(string|Closure $callback): static
    {
        $this->recordFilename = $callback;

        return $this;
    }

    public function entryFilename(string|Closure $callback): static
    {
        return $this->recordFilename($callback);
    }

    public function getRecordFilename(mixed $record, int $index = 1): string
    {
        if ($this->recordFilename) {
            $evaluated = $this->evaluate($this->recordFilename, [
                'record' => $record,
                'index' => $index,
                'format' => $this->getQrFormat(),
            ]);

            return is_string($evaluated) ? $evaluated : (is_scalar($evaluated) || $evaluated instanceof Stringable ? (string) $evaluated : "qrcode_{$index}");
        }

        if ($record instanceof Model) {
            $key = $record->getKey();

            return is_scalar($key) || $key instanceof Stringable ? (string) $key : "qrcode_{$index}";
        }

        if (is_array($record) && isset($record['id'])) {
            $id = $record['id'];

            return is_scalar($id) || $id instanceof Stringable ? (string) $id : "qrcode_{$index}";
        }

        if (is_object($record) && isset($record->id)) {
            $id = $record->id;

            return is_scalar($id) || $id instanceof Stringable ? (string) $id : "qrcode_{$index}";
        }

        return "qrcode_{$index}";
    }

    public function resolveEntryFilename(mixed $record, int $index = 0, ?string $format = null): string
    {
        $format ??= $this->getQrFormat();
        $filename = $this->getRecordFilename($record, $index + 1);

        if (! str_ends_with(strtolower($filename), '.'.strtolower($format))) {
            $filename .= '.'.$format;
        }

        return $filename;
    }

    /**
     * @param  EloquentCollection<int, Model>|Collection<int, mixed>  $records
     */
    public function downloadZip(EloquentCollection|Collection $records): ?StreamedResponse
    {
        $format = $this->getQrFormat();
        /** @var QrCodeRenderer $qrCodeRenderer */
        $qrCodeRenderer = app(QrCodeRenderer::class);

        $tempFile = tempnam(sys_get_temp_dir(), 'qr_zip_');
        if ($tempFile === false) {
            throw new RuntimeException('Failed to create temporary file for ZIP archive.');
        }

        $zipArchive = new ZipArchive;
        if ($zipArchive->open($tempFile, ZipArchive::CREATE | ZipArchive::OVERWRITE) !== true) {
            throw new RuntimeException('Failed to initialize ZIP archive.');
        }

        $index = 1;
        $count = 0;
        foreach ($records as $record) {
            $payload = $this->getQrPayload($record);
            if (empty($payload)) {
                ++$index;

                continue;
            }

            $output = $qrCodeRenderer->renderRaw(
                payload: $payload,
                size: $this->getQrSize(),
                margin: $this->getQrMargin(),
                format: $format,
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

            $filename = $this->resolveEntryFilename($record, $index - 1, $format);
            $zipArchive->addFromString($filename, $output);
            ++$index;
            ++$count;
        }

        $zipArchive->close();

        if ($count === 0) {
            if (file_exists($tempFile)) {
                @unlink($tempFile);
            }

            return null;
        }

        return response()->streamDownload(function () use ($tempFile): void {
            $stream = fopen($tempFile, 'rb');
            if (is_resource($stream)) {
                fpassthru($stream);
                fclose($stream);
            }

            if (file_exists($tempFile)) {
                @unlink($tempFile);
            }
        }, $this->getFilename(), [
            'Content-Type' => 'application/zip',
        ]);
    }
}
