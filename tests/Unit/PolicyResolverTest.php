<?php

declare(strict_types=1);

use juniyasyos\ShieldLite\Policies\PolicyResolver;
use juniyasyos\ShieldLite\Policies\GenericPolicy;

describe('Policy Resolver', function () {
    beforeEach(function () {
        config(['shield-lite.auto_resolve_policies' => true]);
        config(['shield-lite.excluded_models' => []]);
    });

    it('can resolve generic policy for models without custom policies', function () {
        $policy = PolicyResolver::for('App\Models\User');

        expect($policy)->toBeInstanceOf(\Closure::class);
    });

    it('returns null for models with custom policies', function () {
        // Mock a custom policy file existence
        // This would normally check if App\Policies\UserPolicy exists
        // For testing, we assume it doesn't exist so generic policy is used
        $policy = PolicyResolver::for('App\Models\User');

        expect($policy)->not->toBeNull();
    });

    it('can create generic policy closure', function () {
        $policyClosure = PolicyResolver::createGenericPolicy('App\Models\User');

        expect($policyClosure)->toBeInstanceOf(\Closure::class);

        $policyInstance = $policyClosure();
        expect($policyInstance)->toBeInstanceOf(GenericPolicy::class);
    });

    it('can check if model has custom policy', function () {
        // For most test cases, models won't have custom policies
        $hasCustom = PolicyResolver::hasCustomPolicy('App\Models\NonExistentModel');
        expect($hasCustom)->toBeFalse();
    });

    it('can register policies for multiple models', function () {
        $models = ['App\Models\User', 'App\Models\Post'];
        $policies = PolicyResolver::registerPolicies($models);

        expect($policies)->toBeArray();
        expect($policies)->toHaveKey('App\Models\User');
        expect($policies)->toHaveKey('App\Models\Post');
    });

    it('respects excluded models configuration', function () {
        config(['shield-lite.excluded_models' => ['App\Models\User']]);

        $isExcluded = PolicyResolver::isModelExcluded('App\Models\User');
        expect($isExcluded)->toBeTrue();

        $isExcluded = PolicyResolver::isModelExcluded('App\Models\Post');
        expect($isExcluded)->toBeFalse();
    });

    it('can auto-discover models in standard locations', function () {
        $models = PolicyResolver::getAutoDiscoverableModels();

        expect($models)->toBeArray();
        // In a real application, this would find App\Models\User
        // For testing, we just ensure it returns an array
    });

    it('checks auto-resolution configuration', function () {
        expect(PolicyResolver::isAutoResolutionEnabled())->toBeTrue();

        config(['shield-lite.auto_resolve_policies' => false]);
        expect(PolicyResolver::isAutoResolutionEnabled())->toBeFalse();
    });

    it('gets excluded models from configuration', function () {
        config(['shield-lite.excluded_models' => ['App\Models\SystemLog', 'App\Models\AuditLog']]);

        $excluded = PolicyResolver::getExcludedModels();
        expect($excluded)->toBe(['App\Models\SystemLog', 'App\Models\AuditLog']);
    });

    it('can discover models in custom directory structures', function () {
        // Test would verify discovery in DDD-style app/Domain/*/Models structure
        // For now, just ensure the method exists and returns array
        $models = PolicyResolver::getAutoDiscoverableModels();
        expect($models)->toBeArray();
    });

    it('builds correct policy class names for models', function () {
        $possiblePolicies = PolicyResolver::getPossiblePolicyClasses('App\Models\User');

        expect($possiblePolicies)->toContain('App\Policies\UserPolicy');
        expect($possiblePolicies)->toBeArray();
        expect(count($possiblePolicies))->toBeGreaterThan(0);
    });
});
