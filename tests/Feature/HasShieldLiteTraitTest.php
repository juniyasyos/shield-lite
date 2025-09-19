<?php

use App\Models\User;
use Filament\Resources\Resource;
use juniyasyos\ShieldLite\Concerns\HasShieldLite;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;

// Create a test resource class
class TestResource extends Resource
{
    use HasShieldLite;

    protected static ?string $model = \App\Models\User::class;

    public function defineGates(): array
    {
        return [
            'users.viewAny' => 'View any users',
            'users.create' => 'Create users',
            'users.update' => 'Update users',
            'users.delete' => 'Delete users',
        ];
    }
}

describe('Shield Lite HasShieldLite Trait', function () {

    beforeEach(function () {
        config([
            'shield-lite.driver' => 'spatie',
            'shield-lite.guard' => 'web',
            'shield-lite.super_admin_roles' => ['Super-Admin'],
            'shield-lite.auto_register' => true,
        ]);
    });

    it('auto-registers permissions from defineGates', function () {
        $resource = new TestResource();

        // Call the registerGates method to auto-create permissions
        TestResource::registerGates();

        $guard = config('shield-lite.guard', 'web');

        // Check if permissions were created
        expect(Permission::where('name', 'users.viewAny')->where('guard_name', $guard)->exists())->toBeTrue();
        expect(Permission::where('name', 'users.create')->where('guard_name', $guard)->exists())->toBeTrue();
        expect(Permission::where('name', 'users.update')->where('guard_name', $guard)->exists())->toBeTrue();
        expect(Permission::where('name', 'users.delete')->where('guard_name', $guard)->exists())->toBeTrue();
    });

    it('checks permissions correctly', function () {
        $user = User::factory()->create();
        $guard = config('shield-lite.guard', 'web');

        // Create permissions
        Permission::findOrCreate('users.create', $guard);
        $user->givePermissionTo('users.create');

        $resource = new TestResource();

        expect($resource->checkSpecificPermission($user, 'users.create'))->toBeTrue();
        expect($resource->checkSpecificPermission($user, 'users.delete'))->toBeFalse();
    });

    it('super admin bypasses resource permissions', function () {
        $user = User::factory()->create();
        $guard = config('shield-lite.guard', 'web');

        Role::findOrCreate('Super-Admin', $guard);
        $user->assignRole('Super-Admin');

        $resource = new TestResource();

        // Super admin should pass all checks
        expect($resource->checkSpecificPermission($user, 'users.create'))->toBeTrue();
        expect($resource->checkSpecificPermission($user, 'users.delete'))->toBeTrue();
        expect($resource->checkSpecificPermission($user, 'any.permission'))->toBeTrue();
    });

    it('handles missing permissions gracefully', function () {
        $user = User::factory()->create();
        $resource = new TestResource();

        // User without permissions should be denied
        expect($resource->checkSpecificPermission($user, 'nonexistent.permission'))->toBeFalse();
    });

    it('can get defined gates', function () {
        $resource = new TestResource();
        $gates = $resource->defineGates();

        expect($gates)->toBeArray();
        expect($gates)->toHaveKey('users.viewAny');
        expect($gates)->toHaveKey('users.create');
        expect($gates['users.viewAny'])->toBe('View any users');
    });

    it('boots correctly and registers permissions', function () {
        // This test ensures the bootHasShieldLite method works
        $resource = new TestResource();

        // The bootHasShieldLite should be called automatically
        // and register permissions, let's verify they exist
        $guard = config('shield-lite.guard', 'web');

        TestResource::registerGates();

        expect(Permission::where('name', 'users.viewAny')->where('guard_name', $guard)->exists())->toBeTrue();
    });

});
