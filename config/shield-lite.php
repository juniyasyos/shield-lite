<?php

return [
    'user_model' => App\Models\User::class,
    'guard' => 'web',
    'driver' => 'spatie',
    'auto_resolve_policies' => true,
    'excluded_models' => [],
    'ability_format' => '{resource}.{action}',
    'abilities' => [
        'crud_actions' => [
            'viewAny',
            'view',
            'create',
            'update',
            'delete',
            'restore',
            'forceDelete',
        ],
        'custom' => [],
        'global' => [],
    ],
    'permissions' => [
        'super_admin' => ['*'],
        'admin' => ['users.*', 'roles.*', 'permissions.*'],
        'user' => ['users.view', 'users.update'],
        'roles' => [
            'super_admin' => ['*'],
            'admin' => [
                'users.view_any',
                'users.view',
                'users.create',
                'users.update',
                'users.delete',
            ],
            'user' => ['users.view'],
        ],
        'users' => [],
    ],
    'super_admin' => [
        'role' => 'super_admin',
    ],
    'cache' => [
        'enabled' => true,
        'ttl' => 3600,
        'key' => 'shield_lite_permissions',
    ],
    'panels' => [
        'auto_discover_resources' => true,
        'excluded_resources' => [],
    ],
];
