<?php

namespace juniyasyos\ShieldLite\Traits;

use Filament\Facades\Filament;
use Filament\Panel;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\Gate;

trait GateTrait
{
    public function callGates(?Panel  $panel)
    {
        $panel = $panel ?? Filament::getCurrentPanel();

        $pages = method_exists($panel, 'getPages') ? array_values($panel->getPages()) : [];
        $resources = method_exists($panel, 'getResources') ? array_values($panel->getResources()) : [];
        $widgets = method_exists($panel, 'getWidgets') ? array_values($panel->getWidgets()) : [];

        $items = collect([])
            ->merge(collect($resources)->map(fn($c) => ['type' => 'resources', 'class' => $c]))
            ->merge(collect($pages)->map(fn($c) => ['type' => 'pages', 'class' => $c]))
            ->merge(collect($widgets)->map(fn($c) => ['type' => 'widgets', 'class' => $c]));

        return $items->filter(fn($item) => method_exists(app($item['class']), 'roleName'));
    }

    public function registerGateList(Panel $panel)
    {
        $panelId = method_exists($panel, 'getId') ? $panel->getId() : 'default';
        $cacheEnabled = (bool) config('shield.cache.enabled', true);
        $ttl = (int) config('shield.cache.ttl', 3600);
        $store = config('shield.cache.store');
        $cacheKey = "shield-lite:roles:{$panelId}";

        if ($cacheEnabled) {
            $cached = Cache::store($store)->get($cacheKey);
            if ($cached) {
                Config::set(['shield-lite-roles' => $cached]);
                return;
            }
        }

        $grouped = [
            'resources' => [],
            'pages' => [],
            'widgets' => [],
            'custom' => [],
        ];

        $this->callGates($panel)
            ->each(function (array $component) use (&$grouped) {
                $instance = app($component['class']);
                $grouped[$component['type']][] = [
                    'key' => \Illuminate\Support\Str::slug($instance->roleName(), '_'),
                    'name' => $instance->roleName(),
                    'names' => $instance->defineGates(),
                ];
            });

        // Optional: custom permissions from config('shield.custom_permissions')
        $custom = (array) config('shield.custom_permissions', []);
        $grouped['custom'] = $custom;

        if ($cacheEnabled) {
            Cache::store($store)->put($cacheKey, $grouped, $ttl);
        }

        Config::set(['shield-lite-roles' => $grouped]);
    }

    public function gates(Panel $panel)
    {
        $panelId = method_exists($panel, 'getId') ? $panel->getId() : 'default';
        $cacheEnabled = (bool) config('shield.cache.enabled', true);
        $ttl = (int) config('shield.cache.ttl', 3600);
        $store = config('shield.cache.store');
        $cacheKey = "shield-lite:gates:{$panelId}";

        if ($cacheEnabled) {
            $cached = Cache::store($store)->get($cacheKey);
            if ($cached) {
                return $cached;
            }
        }

        $collections = $this->callGates($panel)
            ->map(function (array $item) {
                return collect(array_keys(app($item['class'])->defineGates()));
            })
            ->toArray();
        $gates = [];
        foreach ($collections as $items) {
            foreach ($items as $item) {
                $gates[] = $item;
            }
        }
        foreach ((array) config('shield.custom_permissions', []) as $key => $label) {
            $gates[] = $key;
        }
        $gates = array_values(array_unique($gates));

        if ($cacheEnabled) {
            Cache::store($store)->put($cacheKey, $gates, $ttl);
        }

        return $gates;
    }

    protected function mergeAccess($access): array
    {
        $gates = [];
        foreach ($access as $accesss) {
            foreach ($accesss as $access) {
                $gates[] = $access;
            }
        }
        return $gates;
    }

    protected function registerGates(Panel $panel)
    {
        collect($this->callGates($panel))
            ->map(function (array $item) {
                return collect(array_keys(app($item['class'])->defineGates()));
            })
            ->each(function ($gates) use ($panel) {
                collect($gates)
                    ->each(function ($gate) use ($panel) {
                        if (Gate::has($gate)) return;
                        Gate::define($gate, function ($user) use ($gate, $panel) {

                            if (method_exists($user, 'roles')) {

                                if ($tenant = Filament::getTenant()) {
                                    $roles = $user->roles()->whereBelongsTo($tenant)->get();
                                } else {
                                    $roles = $user->roles;
                                }

                                if (count($roles) > 0) {
                                    $gates = [];
                                    foreach ($this->mergeAccess($roles->pluck('access')) as $accesss) {
                                        foreach ($accesss as $access) {
                                            $gates[] = $access;
                                        }
                                    }
                                    return in_array($gate, $gates, true);
                                }

                                // Superuser access based on config
                                return (bool) config('shield.superuser_if_no_role', false);
                            }

                            return false;
                        });
                    });
            });

        // Register gates for custom permissions as well
        foreach (array_keys((array) config('shield.custom_permissions', [])) as $gate) {
            if (! Gate::has($gate)) {
                Gate::define($gate, function ($user) use ($gate, $panel) {
                    if (method_exists($user, 'roles')) {
                        if ($tenant = Filament::getTenant()) {
                            $roles = $user->roles()->whereBelongsTo($tenant)->get();
                        } else {
                            $roles = $user->roles;
                        }

                        if (count($roles) > 0) {
                            $gates = [];
                            foreach ($this->mergeAccess($roles->pluck('access')) as $accesss) {
                                foreach ($accesss as $access) {
                                    $gates[] = $access;
                                }
                            }
                            return in_array($gate, $gates, true);
                        }

                        return (bool) config('shield.superuser_if_no_role', false);
                    }

                    return false;
                });
            }
        }
    }
}
