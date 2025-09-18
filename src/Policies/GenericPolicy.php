<?php

namespace juniyasyos\ShieldLite\Policies;

use Illuminate\Foundation\Auth\User;
use juniyasyos\ShieldLite\Support\Ability;
use juniyasyos\ShieldLite\Contracts\PermissionDriver;

/**
 * Generic Policy
 *
 * A dynamic policy that uses magic methods to handle any CRUD operation
 * without needing to define separate methods for each action.
 * This policy integrates with Shield Lite's permission system.
 */
class GenericPolicy
{
    protected string $resource;

    public function __construct(string $resource)
    {
        $this->resource = $resource;
    }

    /**
     * Magic method to handle all policy method calls.
     *
     * @param string $method The policy method name (e.g., 'viewAny', 'create', 'update')
     * @param array $arguments The method arguments [User $user, ?Model $model]
     * @return bool
     */
    public function __call(string $method, array $arguments): bool
    {
        /** @var User $user */
        $user = $arguments[0] ?? null;
        $model = $arguments[1] ?? null;

        if (!$user) {
            return false;
        }

        // Check if user is super admin (bypass all checks)
        if ($this->isSuperAdmin($user)) {
            return true;
        }

        // Normalize the ability name
        $ability = Ability::normalize($method, $this->resource);

        // Use the permission driver to check permissions
        return app(PermissionDriver::class)->check($user, $ability);
    }

    /**
     * Handle viewAny policy check.
     */
    public function viewAny(User $user): bool
    {
        return $this->__call('viewAny', [$user]);
    }

    /**
     * Handle view policy check.
     */
    public function view(User $user, $model): bool
    {
        return $this->__call('view', [$user, $model]);
    }

    /**
     * Handle create policy check.
     */
    public function create(User $user): bool
    {
        return $this->__call('create', [$user]);
    }

    /**
     * Handle update policy check.
     */
    public function update(User $user, $model): bool
    {
        return $this->__call('update', [$user, $model]);
    }

    /**
     * Handle delete policy check.
     */
    public function delete(User $user, $model): bool
    {
        return $this->__call('delete', [$user, $model]);
    }

    /**
     * Handle restore policy check.
     */
    public function restore(User $user, $model): bool
    {
        return $this->__call('restore', [$user, $model]);
    }

    /**
     * Handle forceDelete policy check.
     */
    public function forceDelete(User $user, $model): bool
    {
        return $this->__call('forceDelete', [$user, $model]);
    }

    /**
     * Check if user is a super admin.
     */
    protected function isSuperAdmin(User $user): bool
    {
        // Check if user has isSuperAdmin method (from HasShieldRoles trait)
        if (method_exists($user, 'isSuperAdmin')) {
            return $user->isSuperAdmin();
        }

        // Check if user has super admin role
        if (method_exists($user, 'hasRole')) {
            $superAdminName = config('shield.superadmin.name', 'Super Admin');
            return $user->hasRole($superAdminName);
        }

        // Check if configured as superuser with no roles
        if (config('shield.superuser_if_no_role', false)) {
            if (method_exists($user, 'getRoleNames')) {
                return empty($user->getRoleNames());
            }
            if (method_exists($user, 'roles')) {
                return $user->roles()->count() === 0;
            }
        }

        return false;
    }

    /**
     * Get the resource name this policy handles.
     */
    public function getResource(): string
    {
        return $this->resource;
    }

    /**
     * Set the resource name for this policy.
     */
    public function setResource(string $resource): self
    {
        $this->resource = $resource;
        return $this;
    }
}
