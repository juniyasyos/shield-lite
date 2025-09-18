<?php

namespace juniyasyos\ShieldLite\Support;

use Illuminate\Support\Str;

/**
 * Ability Normalizer
 *
 * Handles consistent ability naming and normalization for the Shield Lite plugin.
 * Provides methods to format abilities according to configurable patterns.
 */
class Ability
{
    /**
     * Normalize an ability name according to the configured format.
     *
     * @param string $resource The resource name (e.g., 'users', 'posts')
     * @param string $action The action name (e.g., 'view', 'create', 'edit')
     * @return string The normalized ability name
     */
    public static function normalize(string $resource, string $action): string
    {
        $format = config('shield-lite.ability_format', '{resource}.{action}');

        return str_replace(
            ['{resource}', '{action}'],
            [static::normalizeResourceName($resource), static::normalizeActionName($action)],
            $format
        );
    }

    /**
     * Normalize a resource name to a consistent format.
     *
     * @param string $resource The resource name
     * @return string The normalized resource name
     */
    public static function normalizeResourceName(string $resource): string
    {
        // Convert to snake_case and ensure it's plural
        $normalized = Str::snake($resource);

        // Handle pluralization for common cases
        if (!Str::endsWith($normalized, 's') && !Str::endsWith($normalized, 'data')) {
            $normalized = Str::plural($normalized);
        }

        return $normalized;
    }

    /**
     * Normalize an action name to a consistent format.
     *
     * @param string $action The action name
     * @return string The normalized action name
     */
    public static function normalizeActionName(string $action): string
    {
        return Str::snake($action);
    }

    /**
     * Generate standard CRUD abilities for a resource.
     *
     * @param string $resource The resource name
     * @return array Array of normalized ability names
     */
    public static function generateCrudAbilities(string $resource): array
    {
        $actions = config('shield-lite.abilities.crud_actions', [
            'viewAny', 'view', 'create', 'update', 'delete', 'restore', 'forceDelete'
        ]);

        $abilities = [];
        foreach ($actions as $action) {
            $abilities[] = static::normalize($resource, $action);
        }

        return $abilities;
    }

    /**
     * Parse an ability string to extract resource and action.
     *
     * @param string $ability The ability string to parse
     * @return array Array with 'resource' and 'action' keys
     */
    public static function parse(string $ability): array
    {
        $format = config('shield-lite.ability_format', '{resource}.{action}');

        // Create a regex pattern from the format
        $pattern = str_replace(
            ['{resource}', '{action}'],
            ['([^.:\-_]+)', '([^.:\-_]+)'],
            preg_quote($format, '/')
        );

        if (preg_match('/^' . $pattern . '$/', $ability, $matches)) {
            return [
                'resource' => $matches[1] ?? '',
                'action' => $matches[2] ?? ''
            ];
        }

        // Fallback: try to detect format
        if (str_contains($ability, '.')) {
            [$resource, $action] = explode('.', $ability, 2);
        } elseif (str_contains($ability, ':')) {
            [$action, $resource] = explode(':', $ability, 2);
        } elseif (str_contains($ability, '_')) {
            [$resource, $action] = explode('_', $ability, 2);
        } else {
            return ['resource' => '', 'action' => $ability];
        }

        return [
            'resource' => trim($resource),
            'action' => trim($action)
        ];
    }

    /**
     * Check if an ability matches a pattern (supports wildcards).
     *
     * @param string $ability The ability to check
     * @param string $pattern The pattern to match against (can contain *)
     * @return bool True if the ability matches the pattern
     */
    public static function matches(string $ability, string $pattern): bool
    {
        // Convert wildcard pattern to regex
        $regex = '/^' . str_replace('*', '.*', preg_quote($pattern, '/')) . '$/';

        return preg_match($regex, $ability) === 1;
    }

    /**
     * Extract resource name from a model class name.
     *
     * @param string $modelClass The full model class name
     * @return string The resource name
     */
    public static function extractResourceNameFromClass(string $modelClass): string
    {
        $className = class_basename($modelClass);

        // Convert PascalCase to snake_case
        return static::normalizeResourceName($className);
    }

    /**
     * Generate abilities for a Filament resource class.
     *
     * @param string $resourceClass The Filament resource class name
     * @return array Array of normalized ability names
     */
    public static function generateFilamentResourceAbilities(string $resourceClass): array
    {
        $className = class_basename($resourceClass);
        $resourceName = str_replace('Resource', '', $className);
        return static::generateCrudAbilities($resourceName);
    }

    /**
     * Generate a permission map for Filament panel resources.
     *
     * @param array $resourceClasses Array of resource class names
     * @return array Resource name => abilities mapping
     */
    public static function generateFilamentPermissionMap(array $resourceClasses): array
    {
        $map = [];

        foreach ($resourceClasses as $resourceClass) {
            $className = class_basename($resourceClass);
            $resourceName = static::normalizeResourceName(str_replace('Resource', '', $className));
            $map[$resourceName] = static::generateCrudAbilities($resourceName);
        }

        return $map;
    }

    /**
     * Validate ability format configuration.
     *
     * @param string $format The format string to validate
     * @return bool True if the format is valid
     */
    public static function validateFormat(string $format): bool
    {
        // Must contain both resource and action placeholders
        return str_contains($format, '{resource}') && str_contains($format, '{action}');
    }

    /**
     * Get all available ability formats.
     *
     * @return array Array of predefined format options
     */
    public static function getAvailableFormats(): array
    {
        return [
            '{resource}.{action}' => 'Dot notation (users.view)',
            '{action}:{resource}' => 'Colon notation (view:users)',
            '{resource}_{action}' => 'Underscore notation (users_view)',
            '{resource}:{action}' => 'Resource colon notation (users:view)',
            '{action}.{resource}' => 'Action dot notation (view.users)',
        ];
    }
}
