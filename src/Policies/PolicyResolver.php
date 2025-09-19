<?php

namespace juniyasyos\ShieldLite\Policies;

use Illuminate\Support\Str;
use juniyasyos\ShieldLite\Support\Ability;

/**
 * Policy Resolver
 *
 * Handles automatic resolution and registration of policies for models.
 * This class determines when to use the GenericPolicy and when to use app-defined policies.
 */
class PolicyResolver
{
    /**
     * Resolve a policy for a given model class.
     *
     * @param string $modelClass The model class name
     * @return string|\Closure|null The policy class name, closure, or null if should use default Laravel resolution
     */
    public static function for(string $modelClass): string|\Closure|null
    {
        // Check if the app has defined a custom policy for this model
        if (static::hasCustomPolicy($modelClass)) {
            return null; // Let Laravel handle it with the custom policy
        }

        // Use GenericPolicy for models that don't have custom policies
        return static::createGenericPolicy($modelClass);
    }

    /**
     * Create a generic policy closure for a model.
     *
     * @param string $modelClass The model class name
     * @return \Closure A closure that returns a GenericPolicy instance
     */
    public static function createGenericPolicy(string $modelClass): \Closure
    {
        $resourceName = Ability::extractResourceNameFromClass($modelClass);

        return function () use ($resourceName) {
            return new GenericPolicy($resourceName);
        };
    }

    /**
     * Check if a model has a custom policy defined.
     *
     * @param string $modelClass The model class name
     * @return bool True if a custom policy exists
     */
    public static function hasCustomPolicy(string $modelClass): bool
    {
        // Check common policy locations
        $possiblePolicyClasses = static::getPossiblePolicyClasses($modelClass);

        foreach ($possiblePolicyClasses as $policyClass) {
            if (class_exists($policyClass)) {
                return true;
            }
        }

        return false;
    }

    /**
     * Get possible policy class names for a model.
     *
     * @param string $modelClass The model class name
     * @return array Array of possible policy class names
     */
    public static function getPossiblePolicyClasses(string $modelClass): array
    {
        $modelName = class_basename($modelClass);
        $modelNamespace = str_replace('\\' . $modelName, '', $modelClass);

        // Replace Models namespace with Policies
        $policyNamespace = str_replace('\\Models', '\\Policies', $modelNamespace);

        return [
            // App\Policies\UserPolicy
            'App\\Policies\\' . $modelName . 'Policy',

            // Same namespace as model but with Policies
            $policyNamespace . '\\' . $modelName . 'Policy',

            // In a Policies subdirectory of the model's namespace
            $modelNamespace . '\\Policies\\' . $modelName . 'Policy',
        ];
    }

    /**
     * Register generic policies for an array of model classes.
     *
     * @param array $modelClasses Array of model class names
     * @return array Model => Policy mapping
     */
    public static function registerPolicies(array $modelClasses): array
    {
        $policies = [];

        foreach ($modelClasses as $modelClass) {
            $policy = static::for($modelClass);
            if ($policy !== null) {
                $policies[$modelClass] = $policy;
            }
        }

        return $policies;
    }

    /**
     * Get all models that should have automatic policies.
     *
     * @return array Array of model class names
     */
    public static function getAutoDiscoverableModels(): array
    {
        $models = [];

        // Discover models in common locations
        $modelPaths = [
            app_path('Models'),
            app_path('Domain/*/Models'), // DDD structure
        ];

        foreach ($modelPaths as $path) {
            $models = array_merge($models, static::discoverModelsInPath($path));
        }

        return array_unique($models);
    }

    /**
     * Discover model classes in a given path.
     *
     * @param string $path The path to search for models
     * @return array Array of model class names
     */
    protected static function discoverModelsInPath(string $path): array
    {
        if (!is_dir($path)) {
            return [];
        }

        $models = [];
        $files = glob($path . '/*.php');

        foreach ($files as $file) {
            $className = pathinfo($file, PATHINFO_FILENAME);

            // Try to build the full class name
            $fullClassName = static::buildModelClassName($file, $className);

            if ($fullClassName && class_exists($fullClassName)) {
                // Check if it's actually a model (extends Eloquent)
                if (is_subclass_of($fullClassName, \Illuminate\Database\Eloquent\Model::class)) {
                    $models[] = $fullClassName;
                }
            }
        }

        return $models;
    }

    /**
     * Build the full class name for a model file.
     *
     * @param string $filePath The file path
     * @param string $className The base class name
     * @return string|null The full class name or null if not determinable
     */
    protected static function buildModelClassName(string $filePath, string $className): ?string
    {
        // Try to determine namespace from file location
        $relativePath = str_replace(base_path(), '', $filePath);
        $relativePath = ltrim($relativePath, '/\\');

        // Convert path to namespace
        $namespace = str_replace(['/', '.php'], ['\\', ''], $relativePath);
        $namespace = str_replace('app\\', 'App\\', $namespace);

        // Remove the class name from namespace
        $namespace = str_replace('\\' . $className, '', $namespace);

        return $namespace . '\\' . $className;
    }

    /**
     * Check if automatic policy resolution is enabled.
     *
     * @return bool True if auto-resolution is enabled
     */
    public static function isAutoResolutionEnabled(): bool
    {
        return config('shield-lite.auto_resolve_policies', true);
    }

    /**
     * Get excluded models that shouldn't have automatic policies.
     *
     * @return array Array of model class names to exclude
     */
    public static function getExcludedModels(): array
    {
        return config('shield-lite.excluded_models', []);
    }

    /**
     * Check if a model should be excluded from automatic policy resolution.
     *
     * @param string $modelClass The model class name
     * @return bool True if the model should be excluded
     */
    public static function isModelExcluded(string $modelClass): bool
    {
        $excludedModels = static::getExcludedModels();

        return in_array($modelClass, $excludedModels);
    }
}
