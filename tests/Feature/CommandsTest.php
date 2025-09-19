<?php

use App\Models\User;

describe('Shield Lite Commands', function () {

    it('can run install command', function () {
        $this->artisan('shield-lite:install')
            ->assertSuccessful();
    });

    it('can create user with command', function () {
        $this->artisan('shield-lite:user', [
            '--email' => 'test@example.com',
            '--password' => 'password123',
        ])->assertSuccessful();

        // Check if user was created
        $user = User::where('email', 'test@example.com')->first();
        expect($user)->not->toBeNull();
        expect($user->hasRole('Super-Admin'))->toBeTrue();
    });

    it('can create role with command', function () {
        $this->artisan('shield-lite:role', [
            'name' => 'Manager',
            '--description' => 'Can manage resources',
        ])->assertSuccessful();

        // Check if role was created
        $guard = config('shield-lite.guard', 'web');
        $role = \Spatie\Permission\Models\Role::where('name', 'Manager')
            ->where('guard_name', $guard)
            ->first();

        expect($role)->not->toBeNull();
    });

    it('install command handles existing setup', function () {
        // Run install twice to test idempotency
        $this->artisan('shield-lite:install')->assertSuccessful();
        $this->artisan('shield-lite:install')->assertSuccessful();
    });

    it('user command validates email uniqueness', function () {
        // Create a user first
        User::factory()->create(['email' => 'existing@example.com']);

        // Try to create another with same email
        $this->artisan('shield-lite:user', [
            '--email' => 'existing@example.com',
            '--password' => 'password123',
        ])->assertFailed();
    });

});
