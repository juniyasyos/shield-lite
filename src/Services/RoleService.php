<?php

namespace juniyasyos\ShieldLite\Services;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\DB;
use juniyasyos\ShieldLite\Support\ShieldLogger;
use juniyasyos\ShieldLite\Services\Contracts\RoleServiceInterface;
use Spatie\Permission\Models\Role;
use Spatie\Permission\Models\Permission;

/**
 * Role Service
 *
 * Handles all role-related operations with proper error handling,
 * validation, and logging.
 */
class RoleService implements RoleServiceInterface
{
    /**
     * Create or update role with permissions
     */
    public function createOrUpdateRole(array $data): Role
    {
        ShieldLogger::setContext(['operation' => 'role_create_or_update']);

        try {
            $startTime = microtime(true);

            // Validate input data
            $this->validateRoleData($data);

            DB::beginTransaction();

            // Create or find role
            $role = $this->findOrCreateRole($data);

            // Handle permissions if provided
            if (isset($data['gates']) && is_array($data['gates'])) {
                $this->syncRolePermissions($role, $data['gates']);
            }

            DB::commit();

            $duration = (microtime(true) - $startTime) * 1000;
            ShieldLogger::performance('role_create_or_update', $duration);
            ShieldLogger::role('created_or_updated', $role->name, ['role_id' => $role->id]);

            return $role;

        } catch (\Throwable $e) {
            DB::rollBack();

            ShieldLogger::error('Failed to create or update role', [
                'data' => $data,
                'error' => $e->getMessage()
            ], $e);

            throw $e;
        } finally {
            ShieldLogger::clearContext();
        }
    }

    /**
     * Sync role permissions safely
     */
    public function syncRolePermissions(Role $role, array $permissions): void
    {
        ShieldLogger::debug('Syncing role permissions', [
            'role' => $role->name,
            'permissions_count' => count($permissions)
        ]);

        try {
            // Validate and sanitize permissions
            $validatedPermissions = $this->validateAndSanitizePermissions($permissions);

            // Create missing permissions
            $this->createMissingPermissions($validatedPermissions, $role->guard_name);

            // Sync permissions
            $role->syncPermissions($validatedPermissions);

            ShieldLogger::permission('synced', "role:{$role->name}", [
                'permissions' => $validatedPermissions,
                'count' => count($validatedPermissions)
            ]);

        } catch (\Throwable $e) {
            ShieldLogger::error('Failed to sync role permissions', [
                'role' => $role->name,
                'permissions' => $permissions
            ], $e);

            throw $e;
        }
    }

    /**
     * Find or create role safely
     */
    private function findOrCreateRole(array $data): Role
    {
        $roleName = $data['name'] ?? $data['role_name'] ?? null;
        $guardName = $data['guard_name'] ?? config('shield-lite.guard', 'web');

        if (empty($roleName)) {
            throw new \InvalidArgumentException('Role name is required');
        }

        ShieldLogger::debug('Finding or creating role', [
            'name' => $roleName,
            'guard' => $guardName
        ]);

        return Role::firstOrCreate([
            'name' => $roleName,
            'guard_name' => $guardName
        ]);
    }

    /**
     * Validate and sanitize permissions array
     */
    private function validateAndSanitizePermissions(array $permissions): array
    {
        $sanitized = [];

        foreach ($permissions as $permission) {
            $permissionName = null;

            // Handle nested arrays or objects
            if (is_array($permission)) {
                if (isset($permission['name'])) {
                    $permissionName = $permission['name'];
                } elseif (isset($permission[0])) {
                    $permissionName = $permission[0];
                } else {
                    ShieldLogger::warning('Invalid permission array structure', [
                        'permission' => $permission
                    ]);
                    continue;
                }
            } elseif (is_object($permission)) {
                if (isset($permission->name)) {
                    $permissionName = $permission->name;
                } else {
                    ShieldLogger::warning('Invalid permission object structure', [
                        'permission' => $permission
                    ]);
                    continue;
                }
            } elseif (is_string($permission) && !empty($permission)) {
                $permissionName = $permission;
            } else {
                ShieldLogger::warning('Invalid permission format', [
                    'permission' => $permission,
                    'type' => gettype($permission)
                ]);
                continue;
            }

            // Validate permission name format
            if ($permissionName && is_string($permissionName) && trim($permissionName) !== '') {
                // Check if it looks like a valid permission name
                if (preg_match('/^[a-zA-Z0-9._-]+$/', $permissionName)) {
                    $sanitized[] = trim($permissionName);
                } else {
                    ShieldLogger::warning('Invalid permission name format', [
                        'permission' => $permissionName
                    ]);
                }
            }
        }

        // Remove duplicates
        $sanitized = array_unique($sanitized);

        ShieldLogger::debug('Sanitized permissions', [
            'original_count' => count($permissions),
            'sanitized_count' => count($sanitized),
            'permissions' => $sanitized
        ]);

        return $sanitized;
    }

    /**
     * Create missing permissions
     */
    private function createMissingPermissions(array $permissions, string $guardName): void
    {
        foreach ($permissions as $permissionName) {
            try {
                Permission::firstOrCreate([
                    'name' => $permissionName,
                    'guard_name' => $guardName
                ]);

                ShieldLogger::debug('Permission ensured', [
                    'permission' => $permissionName,
                    'guard' => $guardName
                ]);

            } catch (\Throwable $e) {
                ShieldLogger::error('Failed to create permission', [
                    'permission' => $permissionName,
                    'guard' => $guardName
                ], $e);

                throw $e;
            }
        }
    }

    /**
     * Validate role data
     */
    private function validateRoleData(array $data): void
    {
        if (empty($data['name']) && empty($data['role_name'])) {
            throw new \InvalidArgumentException('Role name is required');
        }

        if (isset($data['gates']) && !is_array($data['gates'])) {
            throw new \InvalidArgumentException('Gates must be an array');
        }

        // Log validation success
        ShieldLogger::debug('Role data validation passed', [
            'data_keys' => array_keys($data)
        ]);
    }

    /**
     * Delete role safely
     */
    public function deleteRole(int|string $roleId): bool
    {
        try {
            $role = Role::findOrFail($roleId);
            $roleName = $role->name;

            ShieldLogger::role('deleting', $roleName, ['role_id' => $roleId]);

            $result = $role->delete();

            if ($result) {
                ShieldLogger::role('deleted', $roleName, ['role_id' => $roleId]);
            } else {
                ShieldLogger::warning('Role deletion returned false', ['role_id' => $roleId]);
            }

            return $result;

        } catch (\Throwable $e) {
            ShieldLogger::error('Failed to delete role', ['role_id' => $roleId], $e);
            throw $e;
        }
    }

    /**
     * Get role with permissions
     */
    public function getRoleWithPermissions(int|string $roleId): Role
    {
        try {
            $role = Role::with('permissions')->findOrFail($roleId);

            ShieldLogger::debug('Retrieved role with permissions', [
                'role' => $role->name,
                'permissions_count' => $role->permissions->count()
            ]);

            return $role;

        } catch (\Throwable $e) {
            ShieldLogger::error('Failed to retrieve role', ['role_id' => $roleId], $e);
            throw $e;
        }
    }
}
