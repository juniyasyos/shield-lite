<?php

namespace juniyasyos\ShieldLite;

use Illuminate\Support\ServiceProvider;


class ShieldLiteServiceProvider extends ServiceProvider
{

    /**
     * Register services.
     */
    public function register(): void {}

    /**
     * Bootstrap services.
     */
    public function boot(): void
    {
        $this->registerMigration();
        $this->registerView();
        $this->registerTranslations();
        $this->setupConfig();
    }

    protected function setupConfig()
    {
        $this->mergeConfigFrom(
            __DIR__ . '/../config/shield.php',
            'shield'
        );

        $this->publishes([
            __DIR__ . '/../config/shield.php' => config_path('shield.php'),
        ], 'shield-config');

        // Allow users to publish the package migrations if they need to customize them
        $this->publishes([
            __DIR__ . '/../database/migrations' => database_path('migrations'),
        ], 'shield-migrations');

        // Publish example seeders to bootstrap roles (e.g., Super Admin)
        $this->publishes([
            __DIR__ . '/../database/seeders' => database_path('seeders'),
        ], 'shield-seeders');
    }

    protected function registerView()
    {
        $this->loadViewsFrom(
            __DIR__ . '/../resources/views',
            'shield'
        );
    }

    protected function registerTranslations()
    {
        $this->loadTranslationsFrom(
            __DIR__ . '/../resources/lang',
            'shield'
        );
    }

    protected function registerMigration()
    {
        $this->loadMigrationsFrom(
            __DIR__ . '/../database/migrations',
        );
    }
}
