<?php

namespace juniyasyos\ShieldLite\Console;

use Illuminate\Console\Command;
use Filament\Facades\Filament;
use juniyasyos\ShieldLite\Support\ShieldLogger;

class ShieldDebugCommand extends Command
{
    protected $signature = 'shield:debug
        {--panel= : Specific panel to debug}
        {--permissions : Show all permissions}
        {--roles : Show all roles}
        {--users : Show users with roles}
        {--config : Show configuration}';

    protected $description = 'Debug Shield Lite configuration and data';

    public function handle(): int
    {
        $this->info('Shield Lite Debug Information');
        $this->line('============================');

        if ($this->option('config') || !$this->hasOptions()) {
            $this->showConfiguration();
        }

        if ($this->option('permissions') || !$this->hasOptions()) {
            $this->showPermissions();
        }

        if ($this->option('roles') || !$this->hasOptions()) {
            $this->showRoles();
        }

        if ($this->option('users')) {
            $this->showUsers();
        }

        return self::SUCCESS;
    }

    protected function hasOptions(): bool
    {
        return $this->option('permissions') ||
               $this->option('roles') ||
               $this->option('users') ||
               $this->option('config');
    }

    protected function showConfiguration(): void
    {
        $this->newLine();
        $this->line('<fg=cyan>Configuration:</fg=cyan>');
        $this->line('Shield Guard: ' . config('shield-lite.guard', 'web'));
        $this->line('Auto Register: ' . (config('shield-lite.auto_register', true) ? 'enabled' : 'disabled'));
        $this->line('Cache Enabled: ' . (config('shield.cache.enabled', true) ? 'enabled' : 'disabled'));
        $this->line('Cache TTL: ' . config('shield.cache.ttl', 3600) . ' seconds');
    }

    protected function showPermissions(): void
    {
        $this->newLine();
        $this->line('<fg=cyan>Permissions:</fg=cyan>');

        $permissions = \Spatie\Permission\Models\Permission::all();

        if ($permissions->isEmpty()) {
            $this->line('No permissions found.');
            return;
        }

        $this->table(
            ['Name', 'Guard', 'Created'],
            $permissions->map(fn($perm) => [
                $perm->name,
                $perm->guard_name,
                $perm->created_at->diffForHumans()
            ])->toArray()
        );
    }

    protected function showRoles(): void
    {
        $this->newLine();
        $this->line('<fg=cyan>Roles:</fg=cyan>');

        $roles = \Spatie\Permission\Models\Role::with('permissions')->get();

        if ($roles->isEmpty()) {
            $this->line('No roles found.');
            return;
        }

        foreach ($roles as $role) {
            $this->line("<fg=green>{$role->name}</fg=green> ({$role->guard_name})");
            if ($role->permissions->isNotEmpty()) {
                foreach ($role->permissions as $permission) {
                    $this->line("  - {$permission->name}");
                }
            } else {
                $this->line("  No permissions assigned");
            }
            $this->newLine();
        }
    }

    protected function showUsers(): void
    {
        $this->newLine();
        $this->line('<fg=cyan>Users with Roles:</fg=cyan>');

        $users = \App\Models\User::with('roles')->get();

        if ($users->isEmpty()) {
            $this->line('No users found.');
            return;
        }

        foreach ($users as $user) {
            $this->line("<fg=green>{$user->name}</fg=green> ({$user->email})");
            if ($user->roles->isNotEmpty()) {
                foreach ($user->roles as $role) {
                    $this->line("  - {$role->name}");
                }
            } else {
                $this->line("  No roles assigned");
            }
            $this->newLine();
        }
    }
}
