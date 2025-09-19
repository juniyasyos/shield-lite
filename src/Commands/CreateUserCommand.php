<?php

namespace juniyasyos\ShieldLite\Commands;

use Illuminate\Console\Command;
use App\Models\User;
use Spatie\Permission\Models\Role;

class CreateUserCommand extends Command
{
    protected $signature = 'shield-lite:user {--email= : Admin email} {--password= : Admin password}';
    protected $description = 'Create admin user with Super-Admin role';

    public function handle()
    {
        $email = $this->option('email') ?: $this->ask('Admin email');
        $password = $this->option('password') ?: $this->secret('Admin password');

        // Ensure Super-Admin role exists
        $role = Role::firstOrCreate(['name' => 'Super-Admin']);

        // Create user
        $user = User::create([
            'name' => 'Super Admin',
            'email' => $email,
            'password' => bcrypt($password),
            'email_verified_at' => now(),
        ]);

        // Assign Super-Admin role
        $user->assignRole('Super-Admin');

        $this->info("✅ Super Admin created successfully!");
        $this->info("Email: {$email}");
        $this->info("Role: Super-Admin");
    }
}
