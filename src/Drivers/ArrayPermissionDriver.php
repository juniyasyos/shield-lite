<?php

namespace juniyasyos\ShieldLite\Drivers;

use Illuminate\Database\Eloquent\Model;
use juniyasyos\ShieldLite\Contracts\PermissionDriver;

/**
 * Array Permission Driver
 *
 * A simple array-based permission driver for Shield Lite.
 * This driver uses configuration arrays to determine permissions and serves as a fallback
 * when no other permission system is available or for testing purposes.
 */
class ArrayPermissionDriver implements PermissionDriver
{
    protected array $permissions;
    protected array $roles = [];
    protected array $userRoles = [];
    protected array $userPermissions = [];

    public function __construct(?array $permissions = null)
    {
        $this->permissions = $permissions ?? config('shield-lite.permissions', []);
    }

    public function can(Model $user, string $permission, ?string $guard = null): bool
    {
        // Check super admin first
        if ($this->isSuperAdmin($user)) {
            return true;
        }

        // Check user-specific permissions
        if ($this->hasUserPermission($user, $permission)) {
            return true;
        }

        // Check role-based permissions if user has roles
        if ($this->hasRolePermission($user, $permission)) {
            return true;
        }

        // Check wildcard permissions
        if ($this->hasWildcardPermission($user, $permission)) {
            return true;
        }

        return false;
    }

    public function createRole(string $name, ?string $guard = null): mixed
    {
        $role = ['name' => $name, 'guard' => $guard ?? 'web', 'permissions' => []];
        $this->roles[$name] = $role;
        return $role;
    }

    public function createPermission(string $name, ?string $guard = null): mixed
    {
        $permission = ['name' => $name, 'guard' => $guard ?? 'web'];
        return $permission;
    }

    public function assignRole(Model $user, mixed $role): bool
    {
        $roleName = is_array($role) ? $role['name'] : $role;
        $this->userRoles[$user->id][] = $roleName;
        return true;
    }

    public function assignPermission(Model $user, mixed $permission): bool
    {
        $permissionName = is_array($permission) ? $permission['name'] : $permission;
        $this->userPermissions[$user->id][] = $permissionName;
        return true;
    }

    public function hasRole(Model $user, string $role): bool
    {
        return in_array($role, $this->userRoles[$user->id] ?? []);
    }

    public function hasPermission(Model $user, string $permission): bool
    {
        return $this->can($user, $permission);
    }

    public function assignPermissionToRole(mixed $role, mixed $permission): bool
    {
        $roleName = is_array($role) ? $role['name'] : $role;
        $permissionName = is_array($permission) ? $permission['name'] : $permission;

        if (isset($this->roles[$roleName])) {
            $this->roles[$roleName]['permissions'][] = $permissionName;
            return true;
        }

        return false;
    }

    public function getAvailablePermissions(): array
    {
        $permissions = [];

        // Collect all permissions from the configuration
        foreach ($this->permissions as $key => $value) {
            if (is_array($value)) {
                $permissions = array_merge($permissions, array_keys($value));
                $permissions = array_merge($permissions, array_values($value));
            } else {
                $permissions[] = $key;
                $permissions[] = $value;
            }
        }

        // Add default CRUD permissions
        $defaultAbilities = config('shield-lite.abilities.resource', [
            'viewAny', 'view', 'create', 'update', 'delete', 'restore', 'forceDelete'
        ]);

        foreach ($defaultAbilities as $ability) {
            $permissions[] = $ability;
        }

        return array_unique(array_filter($permissions));
    }

    public function getUserPermissions(Model $user): array
    {
        if ($this->isSuperAdmin($user)) {
            return $this->getAvailablePermissions();
        }

        $userPermissions = [];

        // Get user-specific permissions
        $userKey = "user.{$user->id}";
        if (isset($this->permissions[$userKey])) {
            $userPermissions = array_merge($userPermissions, (array) $this->permissions[$userKey]);
        }

        // Get role-based permissions
        if (method_exists($user, 'getRoleNames')) {
            foreach ($user->getRoleNames() as $roleName) {
                $roleKey = "role.{$roleName}";
                if (isset($this->permissions[$roleKey])) {
                    $userPermissions = array_merge($userPermissions, (array) $this->permissions[$roleKey]);
                }
            }
        }

        return array_unique($userPermissions);
    }

    public function isAvailable(): bool
    {
        return true; // Array driver is always available
    }

    public function getName(): string
    {
        return 'array';
    }

    /**
     * Check if user has a specific permission directly assigned.
     */
    protected function hasUserPermission(Model $user, string $ability): bool
    {
        $userKey = "user.{$user->id}";

        if (!isset($this->permissions[$userKey])) {
            return false;
        }

        $userPermissions = (array) $this->permissions[$userKey];
        return in_array($ability, $userPermissions);
    }

    /**
     * Check if user has permission through their roles.
     */
    protected function hasRolePermission(Model $user, string $ability): bool
    {
        if (!method_exists($user, 'getRoleNames')) {
            return false;
        }

        foreach ($user->getRoleNames() as $roleName) {
            $roleKey = "role.{$roleName}";

            if (isset($this->permissions[$roleKey])) {
                $rolePermissions = (array) $this->permissions[$roleKey];
                if (in_array($ability, $rolePermissions)) {
                    return true;
                }
            }
        }

        return false;
    }

    /**
     * Check if user has wildcard permissions that match the ability.
     */
    protected function hasWildcardPermission(Model $user, string $ability): bool
    {
        $userPermissions = $this->getUserPermissions($user);

        foreach ($userPermissions as $permission) {
            if ($this->matchesWildcard($permission, $ability)) {
                return true;
            }
        }

        return false;
    }

    /**
     * Check if a wildcard permission matches the given ability.
     */
    protected function matchesWildcard(string $pattern, string $ability): bool
    {
        // Convert wildcard pattern to regex
        $regex = str_replace(['*', '.'], ['.*', '\.'], $pattern);
        $regex = '/^' . $regex . '$/';

        return preg_match($regex, $ability) === 1;
    }

    /**
     * Check if user is a super admin.
     */
    protected function isSuperAdmin(Model $user): bool
    {
        // Check if user has super admin flag
        if (isset($this->permissions["user.{$user->id}.super_admin"]) &&
            $this->permissions["user.{$user->id}.super_admin"]) {
            return true;
        }

        // Check if user has super admin role
        if (method_exists($user, 'hasRole')) {
            $superAdminName = config('shield.superadmin.name', 'Super Admin');
            return $user->hasRole($superAdminName);
        }

        // Check if configured as superuser with no roles
        if (config('shield.superuser_if_no_role', false)) {
            if (method_exists($user, 'getRoleNames') && empty($user->getRoleNames())) {
                return true;
            }
        }

        return false;
    }

    /**
     * Set permissions for a user.
     */
    public function setUserPermissions(int $userId, array $permissions): void
    {
        $this->permissions["user.{$userId}"] = $permissions;
    }

    /**
     * Set permissions for a role.
     */
    public function setRolePermissions(string $roleName, array $permissions): void
    {
        $this->permissions["role.{$roleName}"] = $permissions;
    }

    /**
     * Add a permission to a user.
     */
    public function addUserPermission(int $userId, string $permission): void
    {
        $userKey = "user.{$userId}";
        $currentPermissions = (array) ($this->permissions[$userKey] ?? []);
        $currentPermissions[] = $permission;
        $this->permissions[$userKey] = array_unique($currentPermissions);
    }

    /**
     * Remove a permission from a user.
     */
    public function removeUserPermission(int $userId, string $permission): void
    {
        $userKey = "user.{$userId}";
        if (isset($this->permissions[$userKey])) {
            $currentPermissions = (array) $this->permissions[$userKey];
            $this->permissions[$userKey] = array_values(array_diff($currentPermissions, [$permission]));
        }
    }
}
