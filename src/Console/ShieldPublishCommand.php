<?php

namespace juniyasyos\ShieldLite\Console;

use Illuminate\Console\Command;

class ShieldPublishCommand extends Command
{
    protected $signature = 'shield:publish {--force : Overwrite any existing files} {--resources : Publish Filament resources into app and disable package resources}';

    protected $description = 'Publish Shield Lite config, migrations, seeders, views, translations, and build Filament assets';

    public function handle(): int
    {
        $this->info('Publishing Shield Lite assets...');

        // Publish config, migrations, seeders, views, translations
        $tags = [
            'shield-config',
            'shield-migrations',
            'shield-seeders',
            'shield-views',
            'shield-translations',
        ];

        foreach ($tags as $tag) {
            $this->call('vendor:publish', array_filter([
                '--tag' => $tag,
                '--force' => (bool) $this->option('force'),
            ]));
        }

        // Try to publish Filament assets if the command exists (Filament v3/v4)
        try {
            if ($this->getLaravel()->bound('events')) {
                // filament:assets exists on Filament v3/v4
                $this->callSilent('filament:assets', [
                    '--force' => (bool) $this->option('force'),
                ]);
                $this->info('Filament assets published.');
            }
        } catch (\Throwable $e) {
            $this->warn('Skipping Filament assets publish (command not available).');
        }

        if ($this->option('resources')) {
            $this->publishResources();
            $this->disablePackageResourcesInConfig();
        }

        $this->info('Shield Lite publish complete.');
        return self::SUCCESS;
    }

    protected function publishResources(): void
    {
        $this->info('Publishing Filament resources into app...');

        $stubBase = dirname(__DIR__, 2) . '/stubs/filament/Resources';
        $targetBase = app_path('Filament/Resources');

        $files = [
            // Minimal stubs extending package resources
            'Roles/RoleResource.php',
            'Users/UserResource.php',
        ];

        foreach ($files as $relPath) {
            $from = $stubBase . '/' . $relPath;
            $to = $targetBase . '/' . $relPath;
            if (! is_dir(dirname($to))) {
                mkdir(dirname($to), 0777, true);
            }
            copy($from, $to);
            $this->line(" - published: {$to}");
        }

        $this->info('Filament resources published to app/Filament/Resources.');
    }

    protected function disablePackageResourcesInConfig(): void
    {
        $configFile = config_path('shield.php');

        if (! file_exists($configFile)) {
            // Ensure config published first
            $this->call('vendor:publish', [
                '--tag' => 'shield-config',
                '--force' => (bool) $this->option('force'),
            ]);
        }

        if (! file_exists($configFile)) {
            $this->warn('Could not locate config/shield.php to disable package resources.');
            return;
        }

        $content = file_get_contents($configFile) ?: '';

        // Replace register_resources flags to false
        $content = preg_replace("/'roles'\s*=>\s*true/", "'roles' => false", $content);
        $content = preg_replace("/'users'\s*=>\s*true/", "'users' => false", $content);

        file_put_contents($configFile, $content);
        $this->info('Disabled package resource registration in config/shield.php');
    }
}
