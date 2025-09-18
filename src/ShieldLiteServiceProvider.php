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
        $this->app->singleton(PermissionDriver::class,
            fn () => new SpatiePermissionDriver()
        );
    }

    /**
     * Register Laravel Gates for Shield Lite permissions.
     */
    protected function registerGates(): void
    {
        Gate::before(function ($user, string $ability, ?array $arguments = null) {
            // 1) Super-admin global allow (konfigurable nama rolenya)
            $role = config('shield-lite.super_admin_role', 'Super-Admin');
            // pakai Spatie langsung, JANGAN $user->can() agar tidak rekursif
            if (method_exists($user, 'hasRole') && $user->hasRole($role)) {
                return true;
            }

            // 2) Default mapping: action on Model → permission "{resource}.{action}"
            // ex: update(Post::class) → "posts.update"
            if (!empty($arguments) && isset($arguments[0])) {
                $resource = \juniyasyos\ShieldLite\Support\ResourceName::fromModel($arguments[0]);
                $permission = \juniyasyos\ShieldLite\Support\Ability::format($ability, $resource);
                if (method_exists($user, 'hasPermissionTo') && $user->hasPermissionTo($permission, config('shield-lite.guard'))) {
                    return true; // izinkan
                }
            }

            return null; // biarkan policy lain berjalan jika ada
        });

        // 3) (Opsional) Custom policy discovery kalau mau fallback otomatis
        Gate::guessPolicyNamesUsing(function (string $modelClass) {
            return \juniyasyos\ShieldLite\Policies\GenericPolicy::class;
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

        // Publish test files for development and integration testing
        $this->publishes([
            __DIR__ . '/../tests/Feature' => base_path('tests/Feature/ShieldLite'),
        ], 'shield-tests');
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
