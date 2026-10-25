<?php

declare(strict_types=1);

use Illuminate\Database\Eloquent\Collection;
use Linkxtr\FilamentQr\Actions\DownloadQrBulkAction;
use Symfony\Component\HttpFoundation\BinaryFileResponse;
use Symfony\Component\HttpFoundation\Response;

it('can initialize download qr bulk action with defaults', function (): void {
    $downloadQrBulkAction = DownloadQrBulkAction::make();

    expect($downloadQrBulkAction->getName())->toBe('downloadQrBulk');
    expect($downloadQrBulkAction->getLabel())->toBe('Download QR Codes');
    expect($downloadQrBulkAction->getArchiveFilename())->toBe('qrcodes.zip');
    expect($downloadQrBulkAction->getQrFormat())->toBe('svg');
});

it('supports custom archive filename and entry filename callback', function (): void {
    $downloadQrBulkAction = DownloadQrBulkAction::make()
        ->archiveFilename('custom-export')
        ->entryFilename(fn ($record, $index, $format): string => "item-{$record['id']}.{$format}");

    $record = ['id' => 99];

    expect($downloadQrBulkAction->getArchiveFilename())->toBe('custom-export.zip');
    expect($downloadQrBulkAction->resolveEntryFilename($record, 0, 'svg'))->toBe('item-99.svg');
});

it('executes download qr bulk action and returns zip download', function (): void {
    $records = new Collection([
        ['id' => 1, 'qr' => 'https://example.com/1'],
        ['id' => 2, 'qr' => 'https://example.com/2'],
    ]);

    $downloadQrBulkAction = DownloadQrBulkAction::make()
        ->records($records)
        ->payload(fn ($record) => $record['qr'])
        ->archiveFilename('batch-qrs');

    $response = $downloadQrBulkAction->call();

    expect($response)->toBeInstanceOf(Response::class);
    expect($response->headers->get('content-type'))->toBe('application/zip');
    expect($response->headers->get('content-disposition'))->toContain('batch-qrs.zip');

    if ($response instanceof BinaryFileResponse) {
        $zipPath = $response->getFile()->getPathname();
        $zip = new ZipArchive;
        expect($zip->open($zipPath))->toBeTrue();
        expect($zip->numFiles)->toBe(2);

        $file1 = $zip->getNameIndex(0);
        $file2 = $zip->getNameIndex(1);
        expect([$file1, $file2])->toContain('1.svg', '2.svg');

        $content = $zip->getFromName($file1);
        expect($content)->toBeString();
        expect($content)->toContain('<svg');

        $zip->close();
    } else {
        ob_start();
        $response->sendContent();
        $binaryContent = ob_get_clean();

        $tempZip = tempnam(sys_get_temp_dir(), 'test_zip_');
        file_put_contents($tempZip, $binaryContent);

        $zip = new ZipArchive;
        expect($zip->open($tempZip))->toBeTrue();
        expect($zip->numFiles)->toBe(2);

        $file1 = $zip->getNameIndex(0);
        $file2 = $zip->getNameIndex(1);
        expect([$file1, $file2])->toContain('1.svg', '2.svg');

        $content = $zip->getFromName($file1);
        expect($content)->toBeString();
        expect($content)->toContain('<svg');

        $zip->close();
        @unlink($tempZip);
    }
});

it('bulk action handles empty records gracefully', function (): void {
    $records = new Collection([
        ['id' => 1, 'qr' => null],
    ]);

    $downloadQrBulkAction = DownloadQrBulkAction::make()
        ->records($records)
        ->payload(fn ($record) => $record['qr']);

    $response = $downloadQrBulkAction->call();

    expect($response)->toBeNull();
});
