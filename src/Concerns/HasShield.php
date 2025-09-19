<?php

namespace juniyasyos\ShieldLite\Concerns;

use Spatie\Permission\Traits\HasRoles;
use Spatie\Permission\Traits\HasPermissions;

/**
 * Single trait for User model - includes everything needed
 */
trait HasShield
{
    use HasRoles, HasPermissions;

    /**
     * Check if user is super admin
     */
    public function isSuperAdmin(): bool
    {
        $superAdminRoles = config('shield-lite.super_admin_roles', ['Super-Admin']);
        return $this->hasAnyRole($superAdminRoles);
    }

    /**
     * Override Spatie's can method to add super admin bypass
     */
    public function can($abilities, $arguments = []): bool
    {
        // Super admin bypasses all checks
        if ($this->isSuperAdmin()) {
            return true;
        }

        return parent::can($abilities, $arguments);
    }

    /**
     * Get user's role names as array (for backward compatibility)
     */
    public function getRoleNamesArray(): array
    {
        return $this->getRoleNames()->toArray();
    }
}
