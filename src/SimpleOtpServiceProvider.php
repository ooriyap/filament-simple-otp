<?php

namespace OoriyaP\FilamentSimpleOtp;

use Filament\Support\Assets\Css;
use Filament\Support\Facades\FilamentAsset;
use Illuminate\Support\ServiceProvider;

class SimpleOtpServiceProvider extends ServiceProvider
{
    public function boot(): void
    {
        // Load Views & Translations
        $this->loadViewsFrom(__DIR__.'/../resources/views', 'filament-simple-otp');
        $this->loadTranslationsFrom(__DIR__.'/../resources/lang', 'filament-simple-otp');

        // Load Migrations
        $this->loadMigrationsFrom(__DIR__.'/../database/migrations');

        // Publish Assets and Configs
        if ($this->app->runningInConsole()) {
            $this->publishes([
                __DIR__.'/../resources/css/filament-simple-otp.compiled.css' => public_path('css/ooriyap/filament-simple-otp/filament-simple-otp-styles.css'),
            ], 'filament-simple-otp-assets');

            $this->publishes([
                __DIR__.'/../resources/views' => resource_path('views/vendor/filament-simple-otp'),
            ], 'filament-simple-otp-views');

            $this->publishes([
                __DIR__.'/../resources/lang' => lang_path('vendor/filament-simple-otp'),
            ], 'filament-simple-otp-translations');

            $this->publishes([
                __DIR__.'/../database/migrations' => database_path('migrations'),
            ], 'filament-simple-otp-migrations');

            $this->publishes([
                __DIR__.'/../config/filament-simple-otp.php' => config_path('filament-simple-otp.php'),
            ], 'filament-simple-otp-config');
        }
    }

    public function register(): void
    {
        $this->mergeConfigFrom(
            __DIR__.'/../config/filament-simple-otp.php',
            'filament-simple-otp'
        );
    }
}

