<?php

namespace juniyasyos\ShieldLite\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\File;

class InstallCommand extends Command
{
    protected $signature = 'shield-lite:install {--force : Overwrite existing files}';
    protected $description = 'Install Shield Lite package with all dependencies';

    public function handle()
    {
        $this->info('🛡️  Installing Shield Lite...');

        // Step 1: Install Spatie Permission if not exists
        $this->installSpatiePermission();

        // Step 2: Publish config
        $this->publishConfig();

        // Step 3: Update User model
        $this->updateUserModel();

        // Step 4: Create default super admin
        $this->createSuperAdmin();

        $this->info('✅ Shield Lite installed successfully!');
        $this->info('');
        $this->info('Next steps:');
        $this->info('1. Add HasShieldLite trait to your Filament Resources');
        $this->info('2. Define permissions using defineGates() method');
        $this->info('3. Run: php artisan shield-lite:user to create admin user');
    }

    protected function installSpatiePermission()
    {
        $this->info('📦 Checking Spatie Permission...');

        // Check if already installed
        if (!class_exists('Spatie\\Permission\\Models\\Permission')) {
            $this->error('Please install Spatie Permission first:');
            $this->line('composer require spatie/laravel-permission');
            return false;
        }

        // Publish migrations if not exists
        if (!File::exists(database_path('migrations/create_permission_tables.php'))) {
            $this->call('vendor:publish', [
                '--provider' => 'Spatie\\Permission\\PermissionServiceProvider'
            ]);
        }

        // Run migrations
        $this->call('migrate');
        $this->info('✅ Spatie Permission ready');

        return true;
    }

    protected function publishConfig()
    {
        $this->info('📝 Publishing config...');

        $configPath = config_path('shield-lite.php');
        if (!File::exists($configPath) || $this->option('force')) {
            File::put($configPath, $this->getConfigContent());
            $this->info('✅ Config published');
        } else {
            $this->info('⚠️  Config already exists (use --force to overwrite)');
        }
    }

    protected function updateUserModel()
    {
        $this->info('👤 Updating User model...');

        $userModelPath = app_path('Models/User.php');
        if (File::exists($userModelPath)) {
            $content = File::get($userModelPath);

            // Add use statements if not exists
            if (!str_contains($content, 'use juniyasyos\ShieldLite\Concerns\HasShield;')) {
                $content = str_replace(
                    'use Illuminate\Foundation\Auth\User as Authenticatable;',
                    "use Illuminate\Foundation\Auth\User as Authenticatable;\nuse juniyasyos\ShieldLite\Concerns\HasShield;",
                    $content
                );
            }

            // Add trait if not exists
            if (!str_contains($content, 'use HasShield;')) {
                $content = str_replace(
                    'class User extends Authenticatable',
                    "class User extends Authenticatable\n{\n    use HasShield;",
                    $content
                );
                $content = str_replace('{\n    use HasShield;\n{', '{', $content);
            }

            File::put($userModelPath, $content);
            $this->info('✅ User model updated');
        }
    }

    protected function createSuperAdmin()
    {
        $this->info('👑 Creating Super Admin role...');

        $this->call('shield-lite:role', [
            'name' => 'Super-Admin',
            '--description' => 'Super Administrator with all permissions'
        ]);
    }

    protected function getConfigContent()
    {
        return '<?php

return [
    /*
    |--------------------------------------------------------------------------
    | Shield Lite Driver
    |--------------------------------------------------------------------------
    |
    | Supported: "spatie"
    |
    */
    "driver" => env("SHIELD_LITE_DRIVER", "spatie"),

    /*
    |--------------------------------------------------------------------------
    | Default Guard
    |--------------------------------------------------------------------------
    */
    "guard" => env("SHIELD_LITE_GUARD", "web"),

    /*
    |--------------------------------------------------------------------------
    | Super Admin Roles
    |--------------------------------------------------------------------------
    */
    "super_admin_roles" => ["Super-Admin"],

    /*
    |--------------------------------------------------------------------------
    | Auto Register Permissions
    |--------------------------------------------------------------------------
    */
    "auto_register" => true,
];
';
    }
}
