<?php

namespace Linkxtr\FilamentQr\Tests;

use Filament\FilamentServiceProvider;
use Illuminate\Database\Eloquent\Factories\Factory;
use Linkxtr\FilamentQr\FilamentQrServiceProvider;
use Linkxtr\QrCode\QrCodeServiceProvider;
use Orchestra\Testbench\TestCase as Orchestra;

class TestCase extends Orchestra
{
    protected function setUp(): void
    {
        parent::setUp();
    }

    protected function getPackageProviders($app)
    {
        return [
            QrCodeServiceProvider::class,
            FilamentServiceProvider::class,
            FilamentQrServiceProvider::class,
        ];
    }

    public function getEnvironmentSetUp($app)
    {
        config()->set('database.default', 'testing');
    }
}
