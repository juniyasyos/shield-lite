<?php

namespace juniyasyos\ShieldLite;

use Filament\Panel;
use Filament\Contracts\Plugin;
use Filament\Navigation\NavigationItem;
use Filament\Support\Icons\Heroicon;
use juniyasyos\ShieldLite\Traits\GateTrait;
use juniyasyos\ShieldLite\Resources\Roles\RoleResource;

class ShieldLite implements Plugin
{

    use GateTrait;

    public function getId(): string
    {
        return 'filament-shield-lite';
    }

    public function register(Panel $panel): void
    {
        // Register Role resource
        if (config('shield.register_resources.roles', true)) {
            $roleResource = config('shield.resources.roles', \juniyasyos\ShieldLite\Resources\Roles\RoleResource::class);
            $panel->resources([
                $roleResource
            ]);
        }
    }

    public function boot(Panel $panel): void
    {
        $this->registerGates($panel);
        $this->registerGateList($panel);

        // Optional custom nav item is disabled by default to avoid duplication
        if (config('shield.navigation.use_nav_item', false)) {
            $navLabel = __(config('shield.navigation.role_label', 'Role & Permissions'));
            $navGroup = __(config('shield.navigation.role_group', 'Settings'));

            $panel->navigationItems([
                NavigationItem::make($navLabel)
                    ->visible(fn() => shield()->can('role.index'))
                    ->url(RoleResource::getUrl())
                    ->isActiveWhen(fn() => request()->fullUrlIs(RoleResource::getUrl() . '*'))
                    ->icon(Heroicon::OutlinedLockClosed)
                    ->group($navGroup),
            ]);
        }
    }

    public static function make(): static
    {
        return app(static::class);
    }

    public static function get(): static
    {
        $plugin = filament(app(static::class)->getId());

        return $plugin;
    }
}
