<?php

namespace juniyasyos\ShieldLite\Database\Seeders;

use Illuminate\Database\Seeder;
use Spatie\Permission\Models\Role;
use Spatie\Permission\Models\Permission;
use juniyasyos\ShieldLite\Support\Ability;

class ShieldLiteSeeder extends Seeder
{
    public function run(): void
    {
        $guard = config('shield-lite.guard', 'web');
        $resourceMap = config('shield-lite.resources', []);
        $superAdminRole = config('shield-lite.super_admin_role', 'Super-Admin');

        // 1) seed permissions
        foreach ($resourceMap as $resource => $actions) {
            foreach ($actions as $action) {
                $permissionName = Ability::format($action, $resource);
                Permission::findOrCreate($permissionName, $guard);
            }
        }

        // 2) seed roles (contoh)
        $superAdmin = Role::findOrCreate($superAdminRole, $guard);
        $superAdmin->givePermissionTo(Permission::where('guard_name', $guard)->pluck('name')->all());

        // 3) reset cache permission (aman)
        app(\Spatie\Permission\PermissionRegistrar::class)->forgetCachedPermissions();
    }
}
