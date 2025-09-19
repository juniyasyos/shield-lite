<?php

declare(strict_types=1);

use juniyasyos\ShieldLite\Support\Ability;

describe('Ability Normalizer', function () {
    it('can normalize ability names with default format', function () {
        $ability = Ability::normalize('user', 'view');
        expect($ability)->toBe('users.view');
    });

    it('can normalize ability names with custom format', function () {
        config(['shield-lite.ability_format' => '{action}:{resource}']);

        $ability = Ability::normalize('user', 'create');
        expect($ability)->toBe('create:users');
    });

    it('can normalize resource names to plural', function () {
        expect(Ability::normalizeResourceName('user'))->toBe('users');
        expect(Ability::normalizeResourceName('post'))->toBe('posts');
        expect(Ability::normalizeResourceName('data'))->toBe('data'); // Already plural-like
    });

    it('can normalize action names to snake_case', function () {
        expect(Ability::normalizeActionName('viewAny'))->toBe('view_any');
        expect(Ability::normalizeActionName('forceDelete'))->toBe('force_delete');
        expect(Ability::normalizeActionName('create'))->toBe('create');
    });

    it('can generate CRUD abilities for a resource', function () {
        $abilities = Ability::generateCrudAbilities('user');

        expect($abilities)->toContain('users.view_any');
        expect($abilities)->toContain('users.view');
        expect($abilities)->toContain('users.create');
        expect($abilities)->toContain('users.update');
        expect($abilities)->toContain('users.delete');
        expect($abilities)->toContain('users.restore');
        expect($abilities)->toContain('users.force_delete');
    });

    it('can parse ability strings with dot notation', function () {
        $parsed = Ability::parse('users.view');

        expect($parsed['resource'])->toBe('users');
        expect($parsed['action'])->toBe('view');
    });

    it('can parse ability strings with colon notation', function () {
        config(['shield-lite.ability_format' => '{action}:{resource}']);

        $parsed = Ability::parse('view:users');

        expect($parsed['resource'])->toBe('users');
        expect($parsed['action'])->toBe('view');
    });

    it('can match abilities with wildcard patterns', function () {
        expect(Ability::matches('users.view', 'users.*'))->toBeTrue();
        expect(Ability::matches('users.create', 'users.*'))->toBeTrue();
        expect(Ability::matches('posts.view', 'users.*'))->toBeFalse();
        expect(Ability::matches('users.view', '*'))->toBeTrue();
    });

    it('can extract resource name from model class', function () {
        expect(Ability::extractResourceNameFromClass('App\Models\User'))->toBe('users');
        expect(Ability::extractResourceNameFromClass('App\Models\BlogPost'))->toBe('blog_posts');
    });

    it('can generate abilities for Filament resource classes', function () {
        $abilities = Ability::generateFilamentResourceAbilities('App\Filament\Resources\UserResource');

        expect($abilities)->toContain('users.view_any');
        expect($abilities)->toContain('users.view');
        expect($abilities)->toContain('users.create');
    });

    it('can validate ability formats', function () {
        expect(Ability::validateFormat('{resource}.{action}'))->toBeTrue();
        expect(Ability::validateFormat('{action}:{resource}'))->toBeTrue();
        expect(Ability::validateFormat('invalid_format'))->toBeFalse();
        expect(Ability::validateFormat('{resource}'))->toBeFalse(); // Missing action
        expect(Ability::validateFormat('{action}'))->toBeFalse(); // Missing resource
    });

    it('provides available format options', function () {
        $formats = Ability::getAvailableFormats();

        expect($formats)->toBeArray();
        expect($formats)->toHaveKey('{resource}.{action}');
        expect($formats)->toHaveKey('{action}:{resource}');
        expect($formats['{resource}.{action}'])->toContain('users.view');
    });
});
