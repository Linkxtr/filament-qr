<?php

use Illuminate\Support\Collection;
use Linkxtr\FilamentQr\Actions\DownloadQrBulkAction;
use Linkxtr\FilamentQr\Tests\Fixtures\Models\Record;
use Symfony\Component\HttpFoundation\StreamedResponse;

it('generates zip archive for selected records', function () {
    $records = new Collection([
        new Record(['id' => 1, 'qr' => 'https://first.com']),
        new Record(['id' => 2, 'qr' => 'https://second.com']),
    ]);

    $action = DownloadQrBulkAction::make()
        ->records($records);

    $response = $action->downloadZip($records);

    expect($response)
        ->toBeInstanceOf(StreamedResponse::class)
        ->and($response->headers->get('content-type'))->toBe('application/zip')
        ->and($response->headers->get('content-disposition'))->toContain('attachment; filename=qrcodes.zip');
});

it('respects custom archive filename and entry naming callbacks', function () {
    $records = new Collection([
        new Record(['id' => 99, 'name' => 'Test Item', 'qr' => 'https://sample.org']),
    ]);

    $action = DownloadQrBulkAction::make()
        ->zipFilename('custom-export.zip')
        ->recordFilename(fn (Record $record) => "custom-{$record->id}");

    expect($action->getZipFilename())->toBe('custom-export.zip')
        ->and($action->resolveEntryFilename($records->first()))->toBe('custom-99.svg');
});

it('returns null if all records have empty payloads', function () {
    $records = new Collection([
        new Record(['id' => 1, 'qr' => '']),
        new Record(['id' => 2, 'qr' => null]),
    ]);

    $action = DownloadQrBulkAction::make();
    $response = $action->downloadZip($records);

    expect($response)->toBeNull();
});
