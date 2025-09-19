<?php

declare(strict_types=1);

use juniyasyos\ShieldLite\Drivers\ArrayPermissionDriver;
use juniyasyos\ShieldLite\Drivers\SpatiePermissionDriver;

describe('Array Permission Driver', function () {
    beforeEach(function () {
        $this->driver = new ArrayPermissionDriver();
        $this->user = createTestUser();

        // Mock config for array permissions
        config([
            'shield-lite.permissions.roles.user' => ['users.view', 'users.update'],
            'shield-lite.permissions.roles.admin' => ['users.*', 'posts.*'],
            'shield-lite.permissions.users.1' => ['special.permission'],
        ]);
    });

    it('can check role-based permissions', function () {
        // Mock user roles
        $this->user->shouldReceive('getRoleNames')->andReturn(collect(['user']));

        expect($this->driver->check($this->user, 'users.view'))->toBeTrue();
        expect($this->driver->check($this->user, 'users.update'))->toBeTrue();
        expect($this->driver->check($this->user, 'users.delete'))->toBeFalse();
    });

    it('can check wildcard permissions', function () {
        $this->user->shouldReceive('getRoleNames')->andReturn(collect(['admin']));

        expect($this->driver->check($this->user, 'users.view'))->toBeTrue();
        expect($this->driver->check($this->user, 'users.create'))->toBeTrue();
        expect($this->driver->check($this->user, 'users.delete'))->toBeTrue();
        expect($this->driver->check($this->user, 'posts.view'))->toBeTrue();
    });

    it('can check user-specific permissions', function () {
        $this->user->id = 1;
        $this->user->shouldReceive('getRoleNames')->andReturn(collect([]));

        expect($this->driver->check($this->user, 'special.permission'))->toBeTrue();
        expect($this->driver->check($this->user, 'other.permission'))->toBeFalse();
    });

    it('can get available permissions', function () {
        $permissions = $this->driver->getAvailablePermissions();

        expect($permissions)->toBeArray();
        expect($permissions)->toContain('users.view');
        expect($permissions)->toContain('users.update');
    });

    it('can get user permissions', function () {
        $this->user->id = 1;
        $this->user->shouldReceive('getRoleNames')->andReturn(collect(['user']));

        $permissions = $this->driver->getUserPermissions($this->user);

        expect($permissions)->toContain('users.view');
        expect($permissions)->toContain('users.update');
        expect($permissions)->toContain('special.permission');
    });
});

describe('Spatie Permission Driver', function () {
    beforeEach(function () {
        $this->driver = new SpatiePermissionDriver();
        $this->user = createTestUser();
    });

    it('can check permissions using Spatie package', function () {
        $this->user->shouldReceive('can')->with('users.view')->andReturn(true);
        $this->user->shouldReceive('can')->with('users.delete')->andReturn(false);

        expect($this->driver->check($this->user, 'users.view'))->toBeTrue();
        expect($this->driver->check($this->user, 'users.delete'))->toBeFalse();
    });

    it('falls back to array driver when Spatie package unavailable', function () {
        // Mock missing method to simulate package not being available
        $this->user->shouldReceive('can')->andThrow(new \BadMethodCallException());

        config(['shield-lite.permissions.roles.user' => ['users.view']]);
        $this->user->shouldReceive('getRoleNames')->andReturn(collect(['user']));

        expect($this->driver->check($this->user, 'users.view'))->toBeTrue();
    });

    it('can get available permissions from Spatie', function () {
        // Skip if Spatie package not available
        if (!class_exists('Spatie\Permission\Models\Permission')) {
            $this->markTestSkipped('Spatie Permission package not installed');
        }

        $permissions = $this->driver->getAvailablePermissions();
        expect($permissions)->toBeArray();
    });
});
