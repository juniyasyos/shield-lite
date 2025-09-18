<?php

namespace juniyasyos\ShieldLite\Concerns;

use juniyasyos\ShieldLite\Contracts\PermissionDriver;

/**
 * Trait HasShieldPermissions
 *
 * Provides permission checking functionality for User models.
 * This trait integrates with the configurable permission driver system.
 */
trait HasShieldPermissions
{
    /**
     * Check if the user has a specific permission.
     */
    public function hasShieldPermission(string $ability, ?string $resource = null): bool
    {
        // Normalize the ability name using the configured format
        $normalizedAbility = \juniyasyos\ShieldLite\Support\Ability::normalize($ability, $resource);

        // Use the configured permission driver to check permissions
        return app(PermissionDriver::class)->check($this, $normalizedAbility);
    }

    /**
     * Check if the user can perform a specific action on a resource.
     */
    public function canDo(string $action, ?string $resource = null): bool
    {
        return $this->hasShieldPermission($action, $resource);
    }

    /**
     * Check if the user has any of the given permissions.
     */
    public function hasAnyShieldPermission(array $abilities, ?string $resource = null): bool
    {
        foreach ($abilities as $ability) {
            if ($this->hasShieldPermission($ability, $resource)) {
                return true;
            }
        }

        return false;
    }

    /**
     * Check if the user has all of the given permissions.
     */
    public function hasAllShieldPermissions(array $abilities, ?string $resource = null): bool
    {
        foreach ($abilities as $ability) {
            if (!$this->hasShieldPermission($ability, $resource)) {
                return false;
            }
        }

        return true;
    }

    /**
     * Get all permissions for the user based on their roles.
     */
    public function getAllShieldPermissions(): array
    {
        if (!method_exists($this, 'roles')) {
            return [];
        }

        $permissions = [];

        foreach ($this->roles as $role) {
            if (isset($role->access) && is_array($role->access)) {
                foreach ($role->access as $accessGroup) {
                    if (is_array($accessGroup)) {
                        $permissions = array_merge($permissions, $accessGroup);
                    }
                }
            }
        }

        return array_unique($permissions);
    }

    /**
     * Check permissions for CRUD operations on a model.
     */
    public function canViewAny(string $modelClass): bool
    {
        $resource = $this->getResourceNameFromModel($modelClass);
        return $this->hasShieldPermission('viewAny', $resource);
    }

    public function canView(string $modelClass, $model = null): bool
    {
        $resource = $this->getResourceNameFromModel($modelClass);
        return $this->hasShieldPermission('view', $resource);
    }

    public function canCreate(string $modelClass): bool
    {
        $resource = $this->getResourceNameFromModel($modelClass);
        return $this->hasShieldPermission('create', $resource);
    }

    public function canUpdate(string $modelClass, $model = null): bool
    {
        $resource = $this->getResourceNameFromModel($modelClass);
        return $this->hasShieldPermission('update', $resource);
    }

    public function canDelete(string $modelClass, $model = null): bool
    {
        $resource = $this->getResourceNameFromModel($modelClass);
        return $this->hasShieldPermission('delete', $resource);
    }

    public function canRestore(string $modelClass, $model = null): bool
    {
        $resource = $this->getResourceNameFromModel($modelClass);
        return $this->hasShieldPermission('restore', $resource);
    }

    public function canForceDelete(string $modelClass, $model = null): bool
    {
        $resource = $this->getResourceNameFromModel($modelClass);
        return $this->hasShieldPermission('forceDelete', $resource);
    }

    /**
     * Extract resource name from model class name.
     */
    protected function getResourceNameFromModel(string $modelClass): string
    {
        $className = class_basename($modelClass);
        return strtolower(str()->plural($className));
    }
}
