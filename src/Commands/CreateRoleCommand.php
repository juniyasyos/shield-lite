<?php

namespace juniyasyos\ShieldLite\Commands;

use Illuminate\Console\Command;
use Spatie\Permission\Models\Role;

class CreateRoleCommand extends Command
{
    protected $signature = 'shield-lite:role {name} {--description=}';
    protected $description = 'Create a new role';

    public function handle()
    {
        $name = $this->argument('name');
        $description = $this->option('description');

        $role = Role::firstOrCreate(['name' => $name]);

        $this->info("✅ Role '{$name}' created successfully!");

        if ($description) {
            $this->info("Description: {$description}");
        }

        return 0;
    }
}
