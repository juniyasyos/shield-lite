<?php

declare(strict_types=1);

use juniyasyos\ShieldLite\Policies\GenericPolicy;
use juniyasyos\ShieldLite\Contracts\PermissionDriver;

describe('Generic Policy', function () {
    beforeEach(function () {
        $this->user = createTestUser();
        $this->policy = new GenericPolicy('users');

        // Mock the permission driver
        $this->driver = \Mockery::mock(PermissionDriver::class);
        $this->app->instance(PermissionDriver::class, $this->driver);
    });

    it('can handle viewAny method', function () {
        $this->driver->shouldReceive('check')
            ->with($this->user, 'users.view_any')
            ->andReturn(true);

        expect($this->policy->viewAny($this->user))->toBeTrue();
    });

    it('can handle view method', function () {
        $model = \Mockery::mock(\Illuminate\Database\Eloquent\Model::class);

        $this->driver->shouldReceive('check')
            ->with($this->user, 'users.view')
            ->andReturn(true);

        expect($this->policy->view($this->user, $model))->toBeTrue();
    });

    it('can handle create method', function () {
        $this->driver->shouldReceive('check')
            ->with($this->user, 'users.create')
            ->andReturn(true);

        expect($this->policy->create($this->user))->toBeTrue();
    });

    it('can handle update method', function () {
        $model = \Mockery::mock(\Illuminate\Database\Eloquent\Model::class);

        $this->driver->shouldReceive('check')
            ->with($this->user, 'users.update')
            ->andReturn(true);

        expect($this->policy->update($this->user, $model))->toBeTrue();
    });

    it('can handle delete method', function () {
        $model = \Mockery::mock(\Illuminate\Database\Eloquent\Model::class);

        $this->driver->shouldReceive('check')
            ->with($this->user, 'users.delete')
            ->andReturn(true);

        expect($this->policy->delete($this->user, $model))->toBeTrue();
    });

    it('can handle restore method', function () {
        $model = \Mockery::mock(\Illuminate\Database\Eloquent\Model::class);

        $this->driver->shouldReceive('check')
            ->with($this->user, 'users.restore')
            ->andReturn(true);

        expect($this->policy->restore($this->user, $model))->toBeTrue();
    });

    it('can handle forceDelete method', function () {
        $model = \Mockery::mock(\Illuminate\Database\Eloquent\Model::class);

        $this->driver->shouldReceive('check')
            ->with($this->user, 'users.force_delete')
            ->andReturn(true);

        expect($this->policy->forceDelete($this->user, $model))->toBeTrue();
    });

    it('handles super admin users', function () {
        // Mock super admin check
        config(['shield-lite.super_admin.role' => 'super_admin']);
        $this->user->shouldReceive('hasRole')->with('super_admin')->andReturn(true);

        // Super admin should bypass permission checks
        expect($this->policy->viewAny($this->user))->toBeTrue();
        expect($this->policy->create($this->user))->toBeTrue();
    });

    it('can handle custom methods via __call', function () {
        $this->driver->shouldReceive('check')
            ->with($this->user, 'users.custom_action')
            ->andReturn(true);

        // Call a custom method that doesn't exist explicitly
        expect($this->policy->customAction($this->user))->toBeTrue();
    });

    it('passes model as second parameter for model-specific methods', function () {
        $model = \Mockery::mock(\Illuminate\Database\Eloquent\Model::class);

        $this->driver->shouldReceive('check')
            ->with($this->user, 'users.special_action')
            ->andReturn(true);

        // Call custom method with model
        expect($this->policy->specialAction($this->user, $model))->toBeTrue();
    });

    it('returns false when permission is denied', function () {
        $this->driver->shouldReceive('check')
            ->with($this->user, 'users.view_any')
            ->andReturn(false);

        expect($this->policy->viewAny($this->user))->toBeFalse();
    });

    it('handles permission driver exceptions gracefully', function () {
        $this->driver->shouldReceive('check')
            ->with($this->user, 'users.view_any')
            ->andThrow(new \Exception('Permission check failed'));

        expect($this->policy->viewAny($this->user))->toBeFalse();
    });
});
