<?php

namespace MuhammadMahediHasan\Df;

use Illuminate\Support\ServiceProvider;

class DynamicFormsServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        $configPath = __DIR__ . '/../config/dynamic-forms.php';
        if (file_exists($configPath)) {
            $this->mergeConfigFrom($configPath, 'dynamic-forms');
        }
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        // Load migrations
        if (config('dynamic-forms.load_migrations', true)) {
            $this->loadMigrationsFrom(__DIR__ . '/../database/migrations');
        }

        // Load routes
        if (config('dynamic-forms.register_routes', true)) {
            $routePath = __DIR__ . '/../routes/api.php';
            if (file_exists($routePath)) {
                $this->loadRoutesFrom($routePath);
            }
        }

        // Publish configuration
        if ($this->app->runningInConsole()) {
            $this->commands([
                Console\InstallCommand::class,
            ]);

            $this->publishes([
                __DIR__ . '/../config/dynamic-forms.php' => config_path('dynamic-forms.php'),
            ], 'dynamic-forms-config');

            $this->publishes([
                __DIR__ . '/../resources/js' => resource_path('js/vendor/dynamic-forms'),
            ], 'dynamic-forms-assets');
        }

        // Ensure 'en' is always present in configured locales and is the first element
        $locales = config('dynamic-forms.locales', ['en']);
        if (!in_array('en', $locales, true)) {
            array_unshift($locales, 'en');
            config(['dynamic-forms.locales' => $locales]);
        }

    }
}
