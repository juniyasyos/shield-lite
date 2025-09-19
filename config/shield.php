<?php

return [
    /*
    |--------------------------------------------------------------------------
    | User Model
    |--------------------------------------------------------------------------
    |
    | The User model that Shield Lite will use. This should be the main
    | User model of your application that uses the Shield Lite traits.
    |
    */
    'user_model' => env('SHIELD_LITE_USER_MODEL', \App\Models\User::class),

    /*
    |--------------------------------------------------------------------------
    | Authentication Guard
    |--------------------------------------------------------------------------
    |
    | The authentication guard that Shield Lite will use for checking
    | user permissions and roles.
    |
    */
    'guard' => env('SHIELD_LITE_GUARD', 'web'),

    /*
    |--------------------------------------------------------------------------
    | Permission Driver
    |--------------------------------------------------------------------------
    |
    | The permission driver to use for checking permissions. Available drivers:
    | - 'spatie': Uses Spatie Laravel Permission package
    | - 'array': Uses configuration arrays for permissions
    |
    */
    'driver' => env('SHIELD_LITE_DRIVER', 'spatie'),

    /*
    |--------------------------------------------------------------------------
    | Ability Configuration
    |--------------------------------------------------------------------------
    |
    | Configure how abilities/permissions are named and structured.
    |
    */
    'abilities' => [
        // Default resource abilities for CRUD operations
        'resource' => ['viewAny', 'view', 'create', 'update', 'delete', 'restore', 'forceDelete'],

        // Custom abilities can be added here
        'custom' => [
            // 'drive.upload' => 'Upload files to drive',
            // 'drive.manage' => 'Manage drive settings',
        ],
    ],

    /*
    |--------------------------------------------------------------------------
    | Ability Format
    |--------------------------------------------------------------------------
    |
    | The format for generating ability names. Available placeholders:
    | - {resource}: The resource name (e.g., 'users', 'posts')
    | - {action}: The action name (e.g., 'view', 'create', 'update')
    |
    | Examples:
    | - '{resource}.{action}' → 'users.view', 'posts.create'
    | - '{action}:{resource}' → 'view:users', 'create:posts'
    |
    */
    'ability_format' => env('SHIELD_LITE_ABILITY_FORMAT', '{resource}.{action}'),

    /*
    |--------------------------------------------------------------------------
    | Models
    |--------------------------------------------------------------------------
    |
    | The models used by Shield Lite.
    |
    */
    'models' => [
        'role' => \juniyasyos\ShieldLite\Models\ShieldRole::class,
        'user' => \App\Models\User::class,
    ],

    /*
    |--------------------------------------------------------------------------
    | Permissions Configuration (Array Driver)
    |--------------------------------------------------------------------------
    |
    | When using the 'array' driver, permissions are defined here.
    | This serves as a fallback or for simple permission setups.
    |
    */
    'permissions' => [
        // User-specific permissions
        // 'user.1' => ['users.viewAny', 'users.create'],

        // Role-based permissions
        // 'role.admin' => ['*'], // Admin has all permissions
        // 'role.manager' => ['users.*', 'posts.viewAny', 'posts.view'],
        // 'role.staff' => ['posts.viewAny', 'posts.view'],

        // Example permissions
        'role.Super Admin' => ['*'],
        'role.Admin' => ['users.*', 'roles.*'],
        'role.Manager' => ['users.viewAny', 'users.view'],
        'role.Staff' => ['users.viewAny'],
    ],

    /*
    |--------------------------------------------------------------------------
    | Super Admin Configuration
    |--------------------------------------------------------------------------
    |
    | Configuration for super admin functionality.
    |
    */
    'superadmin' => [
        'name' => 'Super Admin',
        'guard' => 'web',
    ],

    /*
    |--------------------------------------------------------------------------
    | Superuser Fallback
    |--------------------------------------------------------------------------
    |
    | If true, users without any role are treated as superusers (full access).
    | For security, it's recommended to keep this false in production.
    |
    */
    'superuser_if_no_role' => false,

    /*
    |--------------------------------------------------------------------------
    | Cache Configuration
    |--------------------------------------------------------------------------
    |
    | Cache configuration for gates/role lists to improve performance.
    |
    */
    'cache' => [
        'enabled' => true,
        'ttl' => 3600, // Time to live in seconds
        'store' => null, // Cache store (null = default store)
    ],

    /*
    |--------------------------------------------------------------------------
    | Navigation Settings
    |--------------------------------------------------------------------------
    |
    | Configure how Shield Lite resources appear in Filament navigation.
    |
    */
    'navigation' => [
        'role_label' => 'Role & Permissions',
        'role_group' => 'User Managements',
        'roles_nav' => env('SHIELD_ROLES_NAV', env('APP_ENV') === 'local'),
        'visible_in' => [], // Restrict to specific environments
    ],

    /*
    |--------------------------------------------------------------------------
    | Resource Registration
    |--------------------------------------------------------------------------
    |
    | Toggle auto-registration of Filament resources.
    |
    */
    'register_resources' => [
        'roles' => true,
    ],

    /*
    |--------------------------------------------------------------------------
    | Resource Classes
    |--------------------------------------------------------------------------
    |
    | The Resource classes to register on the Panel.
    |
    */
    'resources' => [
        'roles' => \juniyasyos\ShieldLite\Resources\Roles\RoleResource::class,
    ],

    /*
    |--------------------------------------------------------------------------
    | Custom Permissions (Legacy)
    |--------------------------------------------------------------------------
    |
    | Optional: define custom permissions to appear under the "Custom" tab.
    | This is kept for backward compatibility.
    |
    */
    'custom_permissions' => [
        // Add your custom permission keys here => 'Human readable label'
    ],
];
