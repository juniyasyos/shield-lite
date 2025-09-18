<?php

namespace juniyasyos\ShieldLite\Drivers;

use Illuminate\Foundation\Auth\User;
use juniyasyos\ShieldLite\Contracts\PermissionDriver;

/**
 * Spatie Permission Driver
 *
 * Integrates Shield Lite with the Spatie Laravel Permission package.
 * This driver checks if Spatie Permission is available and uses it for permission checks.
 */
class SpatiePermissionDriver implements PermissionDriver
{
    public function check(User $user, string $ability): bool
    {
        if (!$this->isAvailable()) {
            return false;
        }

        // Check if user has the permission directly
        if (method_exists($user, 'can') && $user->can($ability)) {
            return true;
        }

        // Check if user has permission through hasPermissionTo method
        if (method_exists($user, 'hasPermissionTo')) {
            try {
                return $user->hasPermissionTo($ability);
            } catch (\Exception $e) {
                // Permission might not exist in Spatie system
                return false;
            }
        }

        return false;
    }

    public function getAvailablePermissions(): array
    {
        if (!$this->isAvailable()) {
            return [];
        }

        // Try to get permissions from Spatie Permission model
        try {
            $permissionModel = config('permission.models.permission');
            if (class_exists($permissionModel)) {
                return $permissionModel::pluck('name')->toArray();
            }
        } catch (\Exception $e) {
            // Spatie not configured properly
        }

        return [];
    }

    public function getUserPermissions(User $user): array
    {
        if (!$this->isAvailable()) {
            return [];
        }

        $permissions = [];

        // Get direct permissions
        if (method_exists($user, 'getDirectPermissions')) {
            $directPermissions = $user->getDirectPermissions()->pluck('name')->toArray();
            $permissions = array_merge($permissions, $directPermissions);
        }

        // Get permissions via roles
        if (method_exists($user, 'getPermissionsViaRoles')) {
            $rolePermissions = $user->getPermissionsViaRoles()->pluck('name')->toArray();
            $permissions = array_merge($permissions, $rolePermissions);
        }

        // Fallback: get all permissions if user has getAllPermissions method
        if (method_exists($user, 'getAllPermissions')) {
            $allPermissions = $user->getAllPermissions()->pluck('name')->toArray();
            $permissions = array_merge($permissions, $allPermissions);
        }

        return array_unique($permissions);
    }

    public function isAvailable(): bool
    {
        // Check if Spatie Permission package is installed
        if (!trait_exists(\Spatie\Permission\Traits\HasPermissions::class)) {
            return false;
        }

        if (!trait_exists(\Spatie\Permission\Traits\HasRoles::class)) {
            return false;
        }

        // Check if permission config exists
        if (!config('permission')) {
            return false;
        }

        return true;
    }

    public function getName(): string
    {
        return 'spatie';
    }

    /**
     * Create or find a permission in Spatie Permission system.
     */
    public function createPermissionIfNotExists(string $permissionName, ?string $guard = null): bool
    {
        if (!$this->isAvailable()) {
            return false;
        }

        try {
            $permissionModel = config('permission.models.permission');
            $guard = $guard ?? config('auth.defaults.guard');

            if (class_exists($permissionModel)) {
                $permissionModel::firstOrCreate([
                    'name' => $permissionName,
                    'guard_name' => $guard,
                ]);
                return true;
            }
        } catch (\Exception $e) {
            // Could not create permission
        }

        return false;
    }

    /**
     * Sync Shield Lite permissions with Spatie Permission system.
     */
    public function syncPermissions(array $permissions, ?string $guard = null): bool
    {
        if (!$this->isAvailable()) {
            return false;
        }

        $guard = $guard ?? config('auth.defaults.guard');
        $created = 0;

        foreach ($permissions as $permission) {
            if ($this->createPermissionIfNotExists($permission, $guard)) {
                $created++;
            }
        }

        return $created > 0;
    }
}
