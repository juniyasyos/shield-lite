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
        $panel->resources([
            RoleResource::class
        ]);
    }

    public function boot(Panel $panel): void
    {
        $this->registerGates($panel);
        $this->registerGateList($panel);

        $navLabel = __(config('shield.navigation.label', 'Role & Permissions'));
        $navGroup = __(config('shield.navigation.group', 'Settings'));

        $panel->navigationItems([
            NavigationItem::make($navLabel)
                ->visible(fn() => shield()->can('role.index'))
                ->url(RoleResource::getUrl())
                ->isActiveWhen(fn() => request()->fullUrlIs(RoleResource::getUrl() . '*'))
                ->icon(Heroicon::OutlinedLockClosed)
                ->group($navGroup),
        ]);
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
