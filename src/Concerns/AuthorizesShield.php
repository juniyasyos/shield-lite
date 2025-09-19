<?php

namespace juniyasyos\ShieldLite\Concerns;

use Illuminate\Support\Facades\Gate;

/**
 * Trait AuthorizesShield
 *
 * Provides authorization helper methods that integrate with Laravel's Gate system.
 * This trait bridges Shield Lite permissions with Laravel's authorization system.
 */
trait AuthorizesShield
{
    /**
     * Check if the user is authorized to perform an action.
     *
     * This method integrates with Laravel's Gate system while falling back
     * to Shield Lite permission checks.
     */
    public function authorize(string $ability, $arguments = null): bool
    {
        // First, try Laravel's Gate system
        if (Gate::allows($ability, $arguments)) {
            return true;
        }

        // If no Gate is defined or it returns false, try Shield Lite permissions
        if (method_exists($this, 'hasShieldPermission')) {
            return $this->hasShieldPermission($ability);
        }

        return false;
    }

    /**
     * Check authorization for a resource action.
     */
    public function authorizeResource(string $action, string $resource): bool
    {
        if (method_exists($this, 'hasShieldPermission')) {
            return $this->hasShieldPermission($action, $resource);
        }

        return false;
    }

    /**
     * Check if user can access a Filament resource.
     */
    public function canAccessResource(string $resourceClass): bool
    {
        $resourceName = $this->getResourceNameFromClass($resourceClass);
        return $this->authorize('viewAny', $resourceName) ||
               $this->authorizeResource('viewAny', $resourceName);
    }

    /**
     * Check if user can access a Filament page.
     */
    public function canAccessPage(string $pageClass): bool
    {
        $pageName = $this->getPageNameFromClass($pageClass);
        return $this->authorize('access', $pageName) ||
               $this->authorizeResource('access', $pageName);
    }

    /**
     * Check if user can access a Filament widget.
     */
    public function canAccessWidget(string $widgetClass): bool
    {
        $widgetName = $this->getWidgetNameFromClass($widgetClass);
        return $this->authorize('view', $widgetName) ||
               $this->authorizeResource('view', $widgetName);
    }

    /**
     * Authorize multiple abilities at once.
     */
    public function authorizeAny(array $abilities, $arguments = null): bool
    {
        foreach ($abilities as $ability) {
            if ($this->authorize($ability, $arguments)) {
                return true;
            }
        }

        return false;
    }

    /**
     * Authorize all abilities.
     */
    public function authorizeAll(array $abilities, $arguments = null): bool
    {
        foreach ($abilities as $ability) {
            if (!$this->authorize($ability, $arguments)) {
                return false;
            }
        }

        return true;
    }

    /**
     * Helper method to extract resource name from class.
     */
    protected function getResourceNameFromClass(string $className): string
    {
        $baseName = class_basename($className);
        $name = str_replace('Resource', '', $baseName);
        return strtolower(str()->snake($name));
    }

    /**
     * Helper method to extract page name from class.
     */
    protected function getPageNameFromClass(string $className): string
    {
        $baseName = class_basename($className);
        $name = str_replace('Page', '', $baseName);
        return strtolower(str()->snake($name));
    }

    /**
     * Helper method to extract widget name from class.
     */
    protected function getWidgetNameFromClass(string $className): string
    {
        $baseName = class_basename($className);
        $name = str_replace('Widget', '', $baseName);
        return strtolower(str()->snake($name));
    }

    /**
     * Check if user has super admin privileges.
     */
    public function isSuperUser(): bool
    {
        if (method_exists($this, 'isSuperAdmin')) {
            return $this->isSuperAdmin();
        }

        // Fallback check for super admin role
        if (method_exists($this, 'hasRole')) {
            $superAdminName = config('shield.superadmin.name', 'Super Admin');
            return $this->hasRole($superAdminName);
        }

        return false;
    }

    /**
     * Check authorization with super user bypass.
     */
    public function authorizeWithBypass(string $ability, $arguments = null): bool
    {
        // Super users bypass all authorization checks
        if ($this->isSuperUser()) {
            return true;
        }

        return $this->authorize($ability, $arguments);
    }
}
