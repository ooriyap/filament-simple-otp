<?php

namespace OoriyaP\FilamentSimpleOtp\Tests;

use Orchestra\Testbench\TestCase as Orchestra;
use Filament\FilamentServiceProvider;
use Livewire\LivewireServiceProvider;
use OoriyaP\FilamentSimpleOtp\SimpleOtpServiceProvider;
use OoriyaP\FilamentSimpleOtp\SimpleOtpPlugin;

class TestCase extends Orchestra
{
    protected function getPackageProviders($app): array
    {
        return [
            LivewireServiceProvider::class,
            FilamentServiceProvider::class,
            SimpleOtpServiceProvider::class,
        ];
    }

    public function getEnvironmentSetUp($app): void
    {
        config()->set('database.default', 'testing');
        config()->set('database.connections.testing', [
            'driver' => 'sqlite',
            'database' => ':memory:',
            'prefix' => '',
        ]);
    }
}
