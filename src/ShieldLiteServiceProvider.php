<?php

namespace juniyasyos\ShieldLite;

use Illuminate\Support\ServiceProvider;
use Illuminate\Support\Facades\Gate;
use juniyasyos\ShieldLite\Console\ShieldPublishCommand;
use juniyasyos\ShieldLite\Contracts\PermissionDriver;
use juniyasyos\ShieldLite\Drivers\SpatiePermissionDriver;
use juniyasyos\ShieldLite\Drivers\ArrayPermissionDriver;
use juniyasyos\ShieldLite\Policies\PolicyResolver;

class ShieldLiteServiceProvider extends ServiceProvider
{
    /**
     * Register services.
     */
    public function register(): void
    {
        $this->registerPermissionDriver();
    }

    /**
     * Bootstrap services.
     */
    public function boot(): void
    {
        $this->registerMigration();
        $this->registerView();
        $this->registerTranslations();
        $this->setupConfig();
        $this->registerGates();
        $this->registerPolicies();

        // Register package commands
        if ($this->app->runningInConsole()) {
            $this->commands([
                ShieldPublishCommand::class,
                \juniyasyos\ShieldLite\Console\ShieldGenerateCommand::class,
            ]);
        }
    }

    /**
     * Register the permission driver based on configuration.
     */
    protected function registerPermissionDriver(): void
    {
        $this->app->singleton(PermissionDriver::class, function ($app) {
            $driver = config('shield-lite.driver', 'spatie');

            return match ($driver) {
                'spatie' => new SpatiePermissionDriver(),
                'array' => new ArrayPermissionDriver(),
                default => new SpatiePermissionDriver(),
            };
        });
    }

    /**
     * Register Laravel Gates for Shield Lite permissions.
     */
    protected function registerGates(): void
    {
        Gate::before(function ($user, $ability) {
            // Check if user is super admin
            if ($this->isSuperAdmin($user)) {
                return true;
            }

            // Use the configured permission driver
            $driver = app(PermissionDriver::class);

            return $driver->check($user, $ability);
        });
    }

    /**
     * Register automatic policies for models.
     */
    protected function registerPolicies(): void
    {
        if (!config('shield-lite.auto_resolve_policies', true)) {
            return;
        }

        // Auto-discover models and register policies
        $models = PolicyResolver::getAutoDiscoverableModels();
        $excludedModels = PolicyResolver::getExcludedModels();

        foreach ($models as $modelClass) {
            if (PolicyResolver::isModelExcluded($modelClass)) {
                continue;
            }

            $policyClass = PolicyResolver::for($modelClass);
            if ($policyClass) {
                Gate::policy($modelClass, $policyClass);
            }
        }
    }

    /**
     * Check if a user is a super admin.
     */
    protected function isSuperAdmin($user): bool
    {
        $config = config('shield-lite.super_admin', []);

        // Check by role
        if (isset($config['role']) && method_exists($user, 'hasRole')) {
            return $user->hasRole($config['role']);
        }

        // Check by attribute
        if (isset($config['attribute'])) {
            return $user->{$config['attribute']} ?? false;
        }

        // Check by user IDs
        if (isset($config['user_ids'])) {
            return in_array($user->id, $config['user_ids']);
        }

        // Check by callback
        if (isset($config['callback']) && is_callable($config['callback'])) {
            return $config['callback']($user);
        }

        return false;
    }

    protected function setupConfig()
    {
        $this->mergeConfigFrom(
            __DIR__ . '/../config/shield-lite.php',
            'shield-lite'
        );

        $this->publishes([
            __DIR__ . '/../config/shield-lite.php' => config_path('shield-lite.php'),
        ], 'shield-lite-config');

        // Keep backward compatibility with old shield config
        $this->publishes([
            __DIR__ . '/../config/shield-lite.php' => config_path('shield.php'),
        ], 'shield-config');

        // Allow users to publish the package migrations if they need to customize them
        $this->publishes([
            __DIR__ . '/../database/migrations' => database_path('migrations'),
        ], 'shield-migrations');

        // Publish example seeders to bootstrap roles (e.g., Super Admin)
        $this->publishes([
            __DIR__ . '/../database/seeders' => database_path('seeders'),
        ], 'shield-seeders');

        // Publish views for customization
        $this->publishes([
            __DIR__ . '/../resources/views' => resource_path('views/vendor/shield'),
        ], 'shield-views');

        // Publish translations for customization
        $this->publishes([
            __DIR__ . '/../resources/lang' => function_exists('lang_path')
                ? lang_path('vendor/shield')
                : resource_path('lang/vendor/shield'),
        ], 'shield-translations');
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
