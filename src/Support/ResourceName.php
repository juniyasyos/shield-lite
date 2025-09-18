<?php

namespace juniyasyos\ShieldLite\Support;

use Illuminate\Support\Str;

final class ResourceName
{
    /**
     * Convert a model class or instance to a resource name.
     *
     * Example: App\Models\PostCategory -> post_categories
     */
    public static function fromModel($modelOrClass): string
    {
        $class = is_string($modelOrClass) ? $modelOrClass : get_class($modelOrClass);
        $base = class_basename($class);                      // PostCategory
        $snake = Str::snake($base);                         // post_category
        return Str::plural($snake);                         // post_categories
    }
}
