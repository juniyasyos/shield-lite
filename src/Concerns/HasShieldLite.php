<?php

namespace juniyasyos\ShieldLite\Concerns;

use Illuminate\Support\Facades\Auth;

/**
 * Trait HasShieldLite
 *
 * Provides authorization functionality for Filament Resources using Shield Lite
 */
trait HasShieldLite
{
    /**
     * Define the gates/permissions for this resource.
     * Override this method in your Resource to define custom permissions.
     */
    public function defineGates(): array
    {
        return [];
    }

    /**
     * Check if the current user can access this resource
     */
    public static function canAccess(): bool
    {
        $user = Auth::user();
        if (!$user) return false;

        // Check if user is super admin
        if (method_exists($user, 'isSuperAdmin') && $user->isSuperAdmin()) {
            return true;
        }

        // Get the resource instance to access defineGates
        $instance = new static();
        $gates = $instance->defineGates();

        // Look for viewAny permission
        $viewAnyPermissions = array_keys(array_filter($gates, function($key) {
            return str_contains(strtolower($key), 'viewany') || str_contains(strtolower($key), 'index');
        }, ARRAY_FILTER_USE_KEY));

        if (empty($viewAnyPermissions)) {
            return true; // Default allow if no specific permission defined
        }

        // Check if user has any of the viewAny permissions
        foreach ($viewAnyPermissions as $permission) {
            if (method_exists($user, 'can') && $user->can($permission)) {
                return true;
            }
        }

        return false;
    }

    /**
     * Check if the current user can create records
     */
    public static function canCreate(): bool
    {
        $user = Auth::user();
        if (!$user) return false;

        if (method_exists($user, 'isSuperAdmin') && $user->isSuperAdmin()) {
            return true;
        }

        $instance = new static();
        $gates = $instance->defineGates();

        $createPermissions = array_keys(array_filter($gates, function($key) {
            return str_contains(strtolower($key), 'create');
        }, ARRAY_FILTER_USE_KEY));

        if (empty($createPermissions)) {
            return true;
        }

        foreach ($createPermissions as $permission) {
            if (method_exists($user, 'can') && $user->can($permission)) {
                return true;
            }
        }

        return false;
    }

    /**
     * Check if the current user can edit/update records
     */
    public static function canEdit($record): bool
    {
        $user = Auth::user();
        if (!$user) return false;

        if (method_exists($user, 'isSuperAdmin') && $user->isSuperAdmin()) {
            return true;
        }

        $instance = new static();
        $gates = $instance->defineGates();

        $updatePermissions = array_keys(array_filter($gates, function($key) {
            return str_contains(strtolower($key), 'update') || str_contains(strtolower($key), 'edit');
        }, ARRAY_FILTER_USE_KEY));

        if (empty($updatePermissions)) {
            return true;
        }

        foreach ($updatePermissions as $permission) {
            if (method_exists($user, 'can') && $user->can($permission)) {
                return true;
            }
        }

        return false;
    }

    /**
     * Check if the current user can delete records
     */
    public static function canDelete($record): bool
    {
        $user = Auth::user();
        if (!$user) return false;

        if (method_exists($user, 'isSuperAdmin') && $user->isSuperAdmin()) {
            return true;
        }

        $instance = new static();
        $gates = $instance->defineGates();

        $deletePermissions = array_keys(array_filter($gates, function($key) {
            return str_contains(strtolower($key), 'delete');
        }, ARRAY_FILTER_USE_KEY));

        if (empty($deletePermissions)) {
            return true;
        }

        foreach ($deletePermissions as $permission) {
            if (method_exists($user, 'can') && $user->can($permission)) {
                return true;
            }
        }

        return false;
    }

    /**
     * Register the gates defined in defineGates() method
     * This should be called from a service provider
     */
    public static function registerGates(): void
    {
        $instance = new static();
        $gates = $instance->defineGates();

        foreach ($gates as $permission => $description) {
            // Ensure permission exists in the database
            if (class_exists('Spatie\\Permission\\Models\\Permission')) {
                \Spatie\Permission\Models\Permission::findOrCreate($permission, 'web');
            }
        }
    }
}
