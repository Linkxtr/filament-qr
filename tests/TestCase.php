<?php

declare(strict_types=1);

namespace Linkxtr\FilamentQr\Tests;

use BladeUI\Heroicons\BladeHeroiconsServiceProvider;
use BladeUI\Icons\BladeIconsServiceProvider;
use Filament\Actions\ActionsServiceProvider;
use Filament\FilamentServiceProvider;
use Filament\Infolists\InfolistsServiceProvider;
use Filament\Support\SupportServiceProvider;
use Filament\Tables\TablesServiceProvider;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Linkxtr\FilamentQr\FilamentQrServiceProvider;
use Linkxtr\QrCode\QrCodeServiceProvider;
use Livewire\LivewireServiceProvider;
use Orchestra\Testbench\TestCase as Orchestra;

class TestCase extends Orchestra
{
    protected function setUp(): void
    {
        parent::setUp();

        Schema::create('records', function (Blueprint $blueprint): void {
            $blueprint->id();
            $blueprint->string('name')->nullable();
            $blueprint->string('qr')->nullable();
            $blueprint->string('url')->nullable();
            $blueprint->string('email')->nullable();
            $blueprint->string('wifi_ssid')->nullable();
            $blueprint->string('wifi_password')->nullable();
            $blueprint->timestamps();
        });
    }

    protected function getPackageProviders($app)
    {
        return [
            BladeIconsServiceProvider::class,
            BladeHeroiconsServiceProvider::class,
            LivewireServiceProvider::class,
            SupportServiceProvider::class,
            TablesServiceProvider::class,
            InfolistsServiceProvider::class,
            ActionsServiceProvider::class,
            QrCodeServiceProvider::class,
            FilamentServiceProvider::class,
            FilamentQrServiceProvider::class,
        ];
    }

    protected function getEnvironmentSetUp($app): void
    {
        config()->set('database.default', 'testing');
        config()->set('database.connections.testing', [
            'driver' => 'sqlite',
            'database' => ':memory:',
            'prefix' => '',
        ]);
        config()->set('app.key', 'base64:Hupx3yAyWxxx/z9eQV+IUQJxUibu/LYqSQDYv6ujnfY=');
    }
}
