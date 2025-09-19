<?php

declare(strict_types=1);

use Illuminate\Foundation\Testing\TestCase;

uses(TestCase::class)
    ->beforeEach(function () {
        $this->app['config']->set('shield-lite.driver', 'array');
        $this->app['config']->set('shield-lite.auto_resolve_policies', true);
        $this->app['config']->set('shield-lite.ability_format', '{resource}.{action}');
    })
    ->in('Feature', 'Unit');

// Helper function to create a test user
function createTestUser(array $attributes = []): \App\Models\User
{
    return \App\Models\User::factory()->create($attributes);
}

// Helper function to create a test model
function createTestModel(string $class, array $attributes = []): \Illuminate\Database\Eloquent\Model
{
    return $class::factory()->create($attributes);
}
