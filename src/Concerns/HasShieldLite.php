<?php

namespace juniyasyos\ShieldLite\Concerns;

use Illuminate\Support\Facades\Auth;
use Spatie\Permission\Models\Permission;

/**
 * Simple trait for Filament Resources
 */
trait HasShieldLite
{
    /**
     * Define permissions for this resource
     * Override this in your Resource
     */
    public function defineGates(): array
    {
        return [];
    }

    /**
     * Auto register permissions when resource is loaded
     */
    public static function bootHasShieldLite()
    {
        if (config('shield-lite.auto_register', true)) {
            static::registerGates();
        }
    }

    /**
     * Register all permissions defined in defineGates()
     */
    public static function registerGates(): void
    {
        $instance = new static();
        $gates = $instance->defineGates();

        foreach ($gates as $permission => $description) {
            Permission::findOrCreate($permission, config('shield-lite.guard', 'web'));
        }
    }

    /**
     * Check if user can access this resource
     */
    public static function canAccess(): bool
    {
        return static::checkPermission(['viewAny', 'index']);
    }

    /**
     * Check if user can create records
     */
    public static function canCreate(): bool
    {
        return static::checkPermission(['create']);
    }

    /**
     * Check if user can edit records
     */
    public static function canEdit($record): bool
    {
        return static::checkPermission(['update', 'edit']);
    }

    /**
     * Check if user can delete records
     */
    public static function canDelete($record): bool
    {
        return static::checkPermission(['delete']);
    }

    /**
     * Helper method to check permissions
     */
    protected static function checkPermission(array $keywords): bool
    {
        $user = Auth::user();
        if (!$user) return false;

        // Super admin bypass
        if (method_exists($user, 'isSuperAdmin') && $user->isSuperAdmin()) {
            return true;
        }

        // Get permissions from defineGates
        $instance = new static();
        $gates = $instance->defineGates();

        // Find matching permissions
        foreach ($gates as $permission => $description) {
            foreach ($keywords as $keyword) {
                if (str_contains(strtolower($permission), strtolower($keyword))) {
                    if ($user->can($permission)) {
                        return true;
                    }
                }
            }
        }

        return true; // Default allow if no specific permission found
    }
}
