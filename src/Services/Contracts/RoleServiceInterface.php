<?php

namespace juniyasyos\ShieldLite\Services\Contracts;

use Spatie\Permission\Models\Role;

/**
 * Role Service Interface
 *
 * Defines the contract for role management operations
 */
interface RoleServiceInterface
{
    /**
     * Create or update role with permissions
     */
    public function createOrUpdateRole(array $data): Role;

    /**
     * Sync role permissions safely
     */
    public function syncRolePermissions(Role $role, array $permissions): void;

    /**
     * Delete role safely
     */
    public function deleteRole(int|string $roleId): bool;

    /**
     * Get role with permissions
     */
    public function getRoleWithPermissions(int|string $roleId): Role;
}
