<?php

namespace juniyasyos\ShieldLite\Policies;

use juniyasyos\ShieldLite\Support\ResourceName;
use juniyasyos\ShieldLite\Support\Ability;
use juniyasyos\ShieldLite\Contracts\PermissionDriver;
use Illuminate\Support\Facades\Auth;

/**
 * Generic Policy
 *
 * A fallback policy that automatically maps policy methods to Spatie permissions
 * without boilerplate. Uses magic __call method to handle any policy action.
 */
class GenericPolicy
{
    /**
     * Handle dynamic policy method calls.
     *
     * @param string $method The policy method name (e.g., 'viewAny', 'create', 'update')
     * @param array $args The method arguments [User $user, ?Model $model]
     * @return bool
     */
    public function __call(string $method, array $args): bool
    {
        $user = $args[0] ?? Auth::user();
        $model = $args[1] ?? null;

        if (!$user) {
            return false;
        }

        // Check super admin bypass first
        $superAdminRole = config('shield-lite.super_admin_role', 'Super-Admin');
        if (method_exists($user, 'hasRole') && $user->hasRole($superAdminRole)) {
            return true;
        }

        $resource = $model
            ? ResourceName::fromModel($model)
            : 'global';

        $permission = Ability::format($method, $resource);

        return app(PermissionDriver::class)
            ->can($user, $permission, config('shield-lite.guard'));
    }
}
