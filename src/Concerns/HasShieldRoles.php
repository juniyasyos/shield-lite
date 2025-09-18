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
     * Compatible with Spatie Permission signature: hasRole($roles, ?string $guard = null)
     */
    public function hasRole($roles, ?string $guard = null): bool
    {
        $guard = $guard ?? config('shield-lite.guard', 'web');

        // Handle both string and array inputs for compatibility
        if (is_string($roles)) {
            return $this->roles()
                ->where('name', $roles)
                ->where('guard', $guard)
                ->exists();
        }

        if (is_array($roles)) {
            return $this->roles()
                ->whereIn('name', $roles)
                ->where('guard', $guard)
                ->exists();
        }

        return false;
    }

    /**
     * Check if the user has any of the given roles.
     * Compatible with Spatie Permission signature: hasAnyRole(...$roles)
     */
    public function hasAnyRole(...$roles): bool
    {
        // Extract guard from last parameter if it's a string and looks like a guard
        $guard = null;
        if (count($roles) > 1 && is_string(end($roles)) && !in_array(end($roles), ['Super-Admin', 'admin', 'user'])) {
            $guard = array_pop($roles);
        }

        $guard = $guard ?? config('shield-lite.guard', 'web');

        // Flatten array if first argument is array
        if (count($roles) === 1 && is_array($roles[0])) {
            $roles = $roles[0];
        }

        return $this->roles()
            ->whereIn('name', $roles)
            ->where('guard', $guard)
            ->exists();
    }

    /**
     * Check if the user has all of the given roles.
     * Compatible with Spatie Permission signature: hasAllRoles(...$roles)
     */
    public function hasAllRoles(...$roles): bool
    {
        // Extract guard from last parameter if it's a string and looks like a guard
        $guard = null;
        if (count($roles) > 1 && is_string(end($roles)) && !in_array(end($roles), ['Super-Admin', 'admin', 'user'])) {
            $guard = array_pop($roles);
        }

        $guard = $guard ?? config('shield-lite.guard', 'web');

        // Flatten array if first argument is array
        if (count($roles) === 1 && is_array($roles[0])) {
            $roles = $roles[0];
        }
        $guard = $guard ?? config('shield-lite.guard', 'web');

        $userRoles = $this->roles()
            ->where('guard', $guard)
            ->pluck('name')
            ->toArray();

        return empty(array_diff($roles, $userRoles));
    }

    /**
     * Assign a role to the user.
     * Compatible with Spatie Permission signature: assignRole($roles)
     */
    public function assignRole($roles, ?string $guard = null): self
    {
        $guard = $guard ?? config('shield-lite.guard', 'web');

        // Handle both string and array inputs for compatibility
        if (is_string($roles)) {
            $roles = [$roles];
        }

        if (!is_array($roles)) {
            return $this;
        }

        foreach ($roles as $roleName) {
            $role = ShieldRole::where('name', $roleName)
                ->where('guard', $guard)
                ->first();

            if ($role && !$this->hasRole($roleName, $guard)) {
                $this->roles()->attach($role->id);
            }
        }

        return $this;
    }

    /**
     * Remove a role from the user.
     * Compatible with Spatie Permission signature: removeRole($roles)
     */
    public function removeRole($roles, ?string $guard = null): self
    {
        $guard = $guard ?? config('shield-lite.guard', 'web');

        // Handle both string and array inputs for compatibility
        if (is_string($roles)) {
            $roles = [$roles];
        }

        if (!is_array($roles)) {
            return $this;
        }

        foreach ($roles as $roleName) {
            $role = ShieldRole::where('name', $roleName)
                ->where('guard', $guard)
                ->first();

            if ($role) {
                $this->roles()->detach($role->id);
            }
        }

        return $this;
    }

    /**
     * Sync roles for the user.
     * Compatible with Spatie Permission signature: syncRoles($roles)
     */
    public function syncRoles($roles, ?string $guard = null): self
    {
        $guard = $guard ?? config('shield-lite.guard', 'web');

        // Handle both string and array inputs for compatibility
        if (is_string($roles)) {
            $roles = [$roles];
        }

        if (!is_array($roles)) {
            $roles = [];
        }

        $roleIds = ShieldRole::whereIn('name', $roles)
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
