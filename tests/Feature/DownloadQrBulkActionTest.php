<?php

use Illuminate\Database\Eloquent\Collection;
use Linkxtr\FilamentQr\Actions\DownloadQrBulkAction;
use Symfony\Component\HttpFoundation\BinaryFileResponse;
use Symfony\Component\HttpFoundation\Response;

it('can initialize download qr bulk action with defaults', function () {
    $action = DownloadQrBulkAction::make();

    expect($action->getName())->toBe('downloadQrBulk');
    expect($action->getLabel())->toBe('Download QR Codes');
    expect($action->getArchiveFilename())->toBe('qrcodes.zip');
    expect($action->getQrFormat())->toBe('svg');
});

it('supports custom archive filename and entry filename callback', function () {
    $action = DownloadQrBulkAction::make()
        ->archiveFilename('custom-export')
        ->entryFilename(fn ($record, $index, $format) => "item-{$record['id']}.{$format}");

    $record = ['id' => 99];

    expect($action->getArchiveFilename())->toBe('custom-export.zip');
    expect($action->resolveEntryFilename($record, 0, 'svg'))->toBe('item-99.svg');
});

it('executes download qr bulk action and returns zip download', function () {
    $records = new Collection([
        ['id' => 1, 'qr' => 'https://example.com/1'],
        ['id' => 2, 'qr' => 'https://example.com/2'],
    ]);

    $action = DownloadQrBulkAction::make()
        ->records($records)
        ->payload(fn ($record) => $record['qr'])
        ->archiveFilename('batch-qrs');

    $response = $action->call();

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

it('bulk action handles empty records gracefully', function () {
    $records = new Collection([
        ['id' => 1, 'qr' => null],
    ]);

    $action = DownloadQrBulkAction::make()
        ->records($records)
        ->payload(fn ($record) => $record['qr']);

    $response = $action->call();

    expect($response)->toBeNull();
});
