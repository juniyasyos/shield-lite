<?php

namespace juniyasyos\ShieldLite\Contracts;

use Illuminate\Database\Eloquent\Model;

/**
 * Interface PermissionDriver
 *
 * Defines the contract for permission checking drivers.
 * This allows Shield Lite to work with different permission backends
 * like Spatie Permission, custom arrays, or other permission systems.
 */
interface PermissionDriver
{
    /**
     * Check if a user has a specific permission.
     *
     * @param Model $user The user to check permissions for
     * @param string $permission The permission name to check
     * @param string|null $guard The guard name to use for permission check
     * @return bool True if the user has the permission, false otherwise
     */
    public function can(Model $user, string $permission, ?string $guard = null): bool;

    /**
     * Create a new role.
     *
     * @param string $name The role name
     * @param string|null $guard The guard name
     * @return mixed The created role object
     */
    public function createRole(string $name, ?string $guard = null): mixed;

    /**
     * Create a new permission.
     *
     * @param string $name The permission name
     * @param string|null $guard The guard name
     * @return mixed The created permission object
     */
    public function createPermission(string $name, ?string $guard = null): mixed;

    /**
     * Assign a role to a user.
     *
     * @param Model $user The user to assign the role to
     * @param mixed $role The role to assign (name or object)
     * @return bool True if successful
     */
    public function assignRole(Model $user, mixed $role): bool;

    /**
     * Assign a permission to a user.
     *
     * @param Model $user The user to assign the permission to
     * @param mixed $permission The permission to assign (name or object)
     * @return bool True if successful
     */
    public function assignPermission(Model $user, mixed $permission): bool;

    /**
     * Check if a user has a specific role.
     *
     * @param Model $user The user to check
     * @param string $role The role name to check
     * @return bool True if the user has the role
     */
    public function hasRole(Model $user, string $role): bool;

    /**
     * Check if a user has a specific permission.
     *
     * @param Model $user The user to check
     * @param string $permission The permission name to check
     * @return bool True if the user has the permission
     */
    public function hasPermission(Model $user, string $permission): bool;

    /**
     * Assign a permission to a role.
     *
     * @param mixed $role The role to assign the permission to
     * @param mixed $permission The permission to assign
     * @return bool True if successful
     */
    public function assignPermissionToRole(mixed $role, mixed $permission): bool;
}
