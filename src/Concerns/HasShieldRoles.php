<?php

namespace juniyasyos\ShieldLite\Concerns;

use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Collection;
use juniyasyos\ShieldLite\Models\ShieldRole;

/**
 * Trait HasShieldRoles
 *
 * Provides role management functionality for User models.
 * This trait integrates with both Spatie Permission and Shield Lite's own role system.
 */
trait HasShieldRoles
{
    /**
     * Get the roles associated with the user.
     */
    public function roles(): BelongsToMany
    {
        return $this->belongsToMany(
            config('shield.models.role', ShieldRole::class),
            'shield_role_user',
            'user_id',
            'role_id'
        );
    }

    /**
     * Check if the user has a specific role.
     */
    public function hasRole(string $roleName, ?string $guard = null): bool
    {
        $guard = $guard ?? config('shield-lite.guard', 'web');

        return $this->roles()
            ->where('name', $roleName)
            ->where('guard', $guard)
            ->exists();
    }

    /**
     * Check if the user has any of the given roles.
     */
    public function hasAnyRole(array $roles, ?string $guard = null): bool
    {
        $guard = $guard ?? config('shield-lite.guard', 'web');

        return $this->roles()
            ->whereIn('name', $roles)
            ->where('guard', $guard)
            ->exists();
    }

    /**
     * Check if the user has all of the given roles.
     */
    public function hasAllRoles(array $roles, ?string $guard = null): bool
    {
        $guard = $guard ?? config('shield-lite.guard', 'web');

        $userRoles = $this->roles()
            ->where('guard', $guard)
            ->pluck('name')
            ->toArray();

        return empty(array_diff($roles, $userRoles));
    }

    /**
     * Assign a role to the user.
     */
    public function assignRole(string $roleName, ?string $guard = null): self
    {
        $guard = $guard ?? config('shield-lite.guard', 'web');

        $role = ShieldRole::where('name', $roleName)
            ->where('guard', $guard)
            ->first();

        if ($role && !$this->hasRole($roleName, $guard)) {
            $this->roles()->attach($role->id);
        }

        return $this;
    }

    /**
     * Remove a role from the user.
     */
    public function removeRole(string $roleName, ?string $guard = null): self
    {
        $guard = $guard ?? config('shield-lite.guard', 'web');

        $role = ShieldRole::where('name', $roleName)
            ->where('guard', $guard)
            ->first();

        if ($role) {
            $this->roles()->detach($role->id);
        }

        return $this;
    }

    /**
     * Sync roles for the user.
     */
    public function syncRoles(array $roleNames, ?string $guard = null): self
    {
        $guard = $guard ?? config('shield-lite.guard', 'web');

        $roleIds = ShieldRole::whereIn('name', $roleNames)
            ->where('guard', $guard)
            ->pluck('id')
            ->toArray();

        $this->roles()->sync($roleIds);

        return $this;
    }

    /**
     * Get all role names for the user.
     */
    public function getRoleNames(?string $guard = null): array
    {
        $guard = $guard ?? config('shield-lite.guard', 'web');

        return $this->roles()
            ->where('guard', $guard)
            ->pluck('name')
            ->toArray();
    }

    /**
     * Get the user's default role.
     */
    public function getDefaultRole(): ?ShieldRole
    {
        if (property_exists($this, 'default_role_id') && $this->default_role_id) {
            return ShieldRole::find($this->default_role_id);
        }

        return $this->roles()->first();
    }

    /**
     * Check if user is a super admin (no role restrictions).
     */
    public function isSuperAdmin(): bool
    {
        if (config('shield.superuser_if_no_role', false) && $this->roles()->count() === 0) {
            return true;
        }

        $superAdminName = config('shield.superadmin.name', 'Super Admin');
        return $this->hasRole($superAdminName);
    }
}
