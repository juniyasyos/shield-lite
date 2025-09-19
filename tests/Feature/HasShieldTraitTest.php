<?php

use App\Models\User;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;

describe('Shield Lite HasShield Trait', function () {

    beforeEach(function () {
        config([
            'shield-lite.driver' => 'spatie',
            'shield-lite.guard' => 'web',
            'shield-lite.super_admin_roles' => ['Super-Admin'],
        ]);
    });

    it('can assign and check roles', function () {
        $user = User::factory()->create();
        $guard = config('shield-lite.guard', 'web');

        Role::findOrCreate('admin', $guard);

        $user->assignRole('admin');

        expect($user->hasRole('admin'))->toBeTrue();
        expect($user->hasRole('editor'))->toBeFalse();
    });

    it('can assign and check permissions', function () {
        $user = User::factory()->create();
        $guard = config('shield-lite.guard', 'web');

        Permission::findOrCreate('posts.create', $guard);

        $user->givePermissionTo('posts.create');

        expect($user->can('posts.create'))->toBeTrue();
        expect($user->can('posts.delete'))->toBeFalse();
    });

    it('can check multiple roles', function () {
        $user = User::factory()->create();
        $guard = config('shield-lite.guard', 'web');

        Role::findOrCreate('admin', $guard);
        Role::findOrCreate('editor', $guard);

        $user->assignRole(['admin', 'editor']);

        expect($user->hasAnyRole(['admin', 'moderator']))->toBeTrue();
        expect($user->hasAllRoles(['admin', 'editor']))->toBeTrue();
        expect($user->hasAllRoles(['admin', 'moderator']))->toBeFalse();
    });

    it('returns role names as array', function () {
        $user = User::factory()->create();
        $guard = config('shield-lite.guard', 'web');

        Role::findOrCreate('admin', $guard);
        Role::findOrCreate('editor', $guard);

        $user->assignRole(['admin', 'editor']);

        $roleNames = $user->getRoleNamesArray();

        expect($roleNames)->toBeArray();
        expect($roleNames)->toContain('admin');
        expect($roleNames)->toContain('editor');
    });

    it('identifies super admin correctly', function () {
        $user = User::factory()->create();
        $guard = config('shield-lite.guard', 'web');

        Role::findOrCreate('Super-Admin', $guard);

        expect($user->isSuperAdmin())->toBeFalse();

        $user->assignRole('Super-Admin');

        expect($user->isSuperAdmin())->toBeTrue();
    });

    it('super admin bypasses permission checks', function () {
        $user = User::factory()->create();
        $guard = config('shield-lite.guard', 'web');

        Role::findOrCreate('Super-Admin', $guard);
        $user->assignRole('Super-Admin');

        // Super admin should pass any permission check
        expect($user->can('posts.create'))->toBeTrue();
        expect($user->can('users.delete'))->toBeTrue();
        expect($user->can('any.random.permission'))->toBeTrue();
    });

    it('works with permissions through roles', function () {
        $user = User::factory()->create();
        $guard = config('shield-lite.guard', 'web');

        $role = Role::findOrCreate('editor', $guard);
        $permission = Permission::findOrCreate('posts.edit', $guard);

        $role->givePermissionTo($permission);
        $user->assignRole($role);

        expect($user->can('posts.edit'))->toBeTrue();
        expect($user->hasPermissionTo('posts.edit'))->toBeTrue();
    });

});
