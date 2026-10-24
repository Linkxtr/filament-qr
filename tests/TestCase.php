<?php

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

        Schema::create('records', function (Blueprint $table) {
            $table->id();
            $table->string('name')->nullable();
            $table->string('qr')->nullable();
            $table->string('url')->nullable();
            $table->string('email')->nullable();
            $table->string('wifi_ssid')->nullable();
            $table->string('wifi_password')->nullable();
            $table->timestamps();
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

    public function getEnvironmentSetUp($app)
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
