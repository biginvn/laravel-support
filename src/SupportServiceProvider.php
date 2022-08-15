<?php

namespace Biginvn\Support;

use Biginvn\Support\Utils\MailVariable;
use Illuminate\Support\Facades\File;
use Illuminate\Support\ServiceProvider;

class SupportServiceProvider extends ServiceProvider
{
    /**
     * Perform post-registration booting of services.
     *
     * @return void
     */
    public function boot(): void
    {
        // Publishing is only necessary when using the CLI.
        $this->loadViewsFrom(__DIR__ . '/../resources/views', 'biginvn/support');
        $this->loadTranslationsFrom(__DIR__ . '/../resources/lang', 'biginvn/support');

        if ($this->app->runningInConsole()) {
            $this->bootForConsole();
        }
    }

    /**
     * Console-specific booting.
     *
     * @return void
     */
    protected function bootForConsole(): void
    {
        // Publishing the configuration file.
        $this->publishes([
            __DIR__ . '/../config/support.php' => config_path('biginvn/support/support.php'),
        ], 'biginvn');

        // Publishing the view files.
        $this->publishes([
            __DIR__ . '/../resources/views' => base_path('resources/views/vendor/biginvn/support'),
        ], 'biginvn');

        // Publishing the translation files.
        $this->publishes([
            __DIR__ . '/../resources/lang' => resource_path('lang/vendor/biginvn/support'),
        ], 'biginvn');
    }

    /**
     * Register any package services.
     *
     * @return void
     */
    public function register(): void
    {
        foreach (File::glob(__DIR__ . '/../helpers/*.php') as $helper) {
            File::requireOnce($helper);
        }

        $this->mergeConfigFrom(__DIR__ . '/../config/support.php', 'biginvn.support.support');

        // Register the service the package provides.
        $this->app->singleton('MailVariable', function () {
            return new MailVariable;
        });

        $this->app->singleton('IOCService', function ($app) {
            $service = config('biginvn.support.support.ioc_service_provider');
            return new $service($app);
        });
    }

    /**
     * Get the services provided by the provider.
     *
     * @return array
     */
    public function provides()
    {
        return ['MailVariable', 'IOCService'];
    }
}
