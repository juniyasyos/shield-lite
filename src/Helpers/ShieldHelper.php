<?php

namespace juniyasyos\ShieldLite\Helpers;

use Exception;
use Filament\Panel;
use Illuminate\Contracts\Auth\Authenticatable;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Gate;

class ShieldHelper
{

    private ?Authenticatable $user;

    public function guard(): string
    {
        return explode('_', Auth::guard()->getName())[1] ?? 'web';
    }

    public function can(array|string $gates): bool
    {
        return empty($this->user) ? Gate::allows($gates) : $this->userCan($this->user, $gates);
    }


    public function panelGates(Panel $panel): array
    {
        try {
            // Prefer this package id, fallback to historical id if present
            $plugin = $panel->getPlugin('filament-shield-lite') ?? $panel->getPlugin('filament-shield');
            return $plugin?->gates($panel) ?? [];
        } catch (Exception $e) {
            return [];
        }
    }

    public function user(Authenticatable $user)
    {
        $this->user = $user;
        return $this;
    }
    

    private function userCan(Authenticatable $user, array|string $permissions)
    {
        if (! method_exists($user, 'roles')) {
            return false;
        }

        $gates = [];

        // Collect and flatten role access arrays: [[gate1, gate2], ...] => [gate1, gate2, ...]
        foreach ($user->roles as $role) {
            $access = $role->access ?? [];
            if (is_array($access)) {
                foreach ($access as $group) {
                    if (is_array($group)) {
                        foreach ($group as $gate) {
                            $gates[] = $gate;
                        }
                    }
                }
            }
        }

        // If the user has no roles/permissions, optionally treat as superuser
        if (empty($gates)) {
            return (bool) config('shield.superuser_if_no_role', false);
        }

        $permissions = is_array($permissions) ? $permissions : [$permissions];

        return ! empty(array_intersect($gates, $permissions));
    }
}
