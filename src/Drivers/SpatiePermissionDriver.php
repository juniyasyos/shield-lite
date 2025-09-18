<?php

namespace juniyasyos\ShieldLite\Drivers;

use Illuminate\Database\Eloquent\Model;
use juniyasyos\ShieldLite\Contracts\PermissionDriver;
use Spatie\Permission\Models\Role;
use Spatie\Permission\Models\Permission;

/**
 * Spatie Permission Driver
 *
 * Integrates Shield Lite with the Spatie Laravel Permission package.
 * This driver uses Spatie Permission directly for permission checks.
 */
final class SpatiePermissionDriver implements PermissionDriver
{
    public function can(Model $user, string $permission, ?string $guard = null): bool
    {
        // Gunakan Spatie langsung (hindari Gate recursion)
        if (!method_exists($user, 'hasPermissionTo')) {
            return false;
        }

        return $user->hasPermissionTo($permission, $guard ?? config('shield-lite.guard'));
    }

    public function createRole(string $name, ?string $guard = null): mixed
    {
        return Role::create([
            'name' => $name,
            'guard_name' => $guard ?? config('shield-lite.guard', 'web'),
        ]);
    }

    public function createPermission(string $name, ?string $guard = null): mixed
    {
        return Permission::create([
            'name' => $name,
            'guard_name' => $guard ?? config('shield-lite.guard', 'web'),
        ]);
    }

    public function assignRole(Model $user, mixed $role): bool
    {
        if (!method_exists($user, 'assignRole')) {
            return false;
        }

        $user->assignRole($role);
        return true;
    }

    public function assignPermission(Model $user, mixed $permission): bool
    {
        if (!method_exists($user, 'givePermissionTo')) {
            return false;
        }

        $user->givePermissionTo($permission);
        return true;
    }

    public function hasRole(Model $user, string $role): bool
    {
        if (!method_exists($user, 'hasRole')) {
            return false;
        }

        return $user->hasRole($role);
    }

    public function hasPermission(Model $user, string $permission): bool
    {
        return $this->can($user, $permission);
    }

    public function assignPermissionToRole(mixed $role, mixed $permission): bool
    {
        if (!method_exists($role, 'givePermissionTo')) {
            return false;
        }

        $role->givePermissionTo($permission);
        return true;
    }
}
