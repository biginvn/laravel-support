<?php

namespace Bigin\Support;

use Bigin\Support\Utils\MailVariable;
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
        $this->loadViewsFrom(__DIR__ . '/../resources/views', 'bigin/support');
        $this->loadTranslationsFrom(__DIR__ . '/../resources/lang', 'bigin/support');

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
            __DIR__ . '/../config/support.php' => config_path('bigin/support/support.php'),
        ], 'bigin');

        // Publishing the view files.
        $this->publishes([
            __DIR__ . '/../resources/views' => base_path('resources/views/vendor/bigin/support'),
        ], 'bigin');

        // Publishing the translation files.
        $this->publishes([
            __DIR__ . '/../resources/lang' => resource_path('lang/vendor/bigin/support'),
        ], 'bigin');
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

        $this->mergeConfigFrom(__DIR__ . '/../config/support.php', 'bigin.support.support');

        // Register the service the package provides.
        $this->app->singleton('MailVariable', function () {
            return new MailVariable;
        });

        $this->app->singleton('IOCService', function ($app) {
            $service = config('bigin.support.support.ioc_service_provider');
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
