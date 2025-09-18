<?php

namespace juniyasyos\ShieldLite\Contracts;

use Illuminate\Foundation\Auth\User;

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
     * @param User $user The user to check permissions for
     * @param string $ability The normalized ability/permission name
     * @return bool True if the user has the permission, false otherwise
     */
    public function check(User $user, string $ability): bool;

    /**
     * Get all permissions available in the system.
     *
     * @return array Array of available permission names
     */
    public function getAvailablePermissions(): array;

    /**
     * Get all permissions for a specific user.
     *
     * @param User $user The user to get permissions for
     * @return array Array of permission names the user has
     */
    public function getUserPermissions(User $user): array;

    /**
     * Check if the permission system is available and configured.
     *
     * @return bool True if the driver is ready to use
     */
    public function isAvailable(): bool;

    /**
     * Get the driver name/identifier.
     *
     * @return string Driver identifier
     */
    public function getName(): string;
}
