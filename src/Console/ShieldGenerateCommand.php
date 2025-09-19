<?php

namespace juniyasyos\ShieldLite\Console;

use Filament\Facades\Filament;
use Illuminate\Console\Command;
use Illuminate\Support\Arr;
use Illuminate\Support\Str;
use Spatie\Permission\Models\Role;
use Spatie\Permission\Models\Permission;
use juniyasyos\ShieldLite\ShieldLite;

class ShieldGenerateCommand extends Command
{
    protected $signature = 'shield:generate
        {--dump= : Save discovered permissions to a JSON file}
        {--super-admin : Create/Update a Super Admin role with all permissions}';

    protected $description = 'Scan Filament Resources/Pages/Widgets and generate default permissions (similar to Filament Shield)';

    public function handle(): int
    {
        $this->info('Scanning Filament panels for permissions...');

        $panels = Filament::getPanels();
        if (empty($panels)) {
            $this->warn('No Filament panels detected. Is Filament booted?');
        }

        $summary = [
            'by_panel' => [],
            'all_gates' => [],
        ];

        foreach ($panels as $panel) {
            try {
                $panelId = method_exists($panel, 'getId') ? $panel->getId() : 'default';
                $grouped = [
                    'resources' => [],
                    'pages' => [],
                    'widgets' => [],
                    'custom' => (array) config('shield.custom_permissions', []),
                ];

                // Use the plugin API if available; otherwise manually inspect
                $plugin = null;
                try { $plugin = $panel->getPlugin('filament-shield-lite'); } catch (\Throwable $e) { /* noop */ }
                if (! $plugin) {
                    try { $plugin = $panel->getPlugin('filament-shield'); } catch (\Throwable $e) { /* noop */ }
                }

                if ($plugin instanceof ShieldLite) {
                    // Build grouped data using the trait logic
                    foreach ($plugin->callGates($panel) as $component) {
                        $instance = app($component['class']);
                        $grouped[$component['type']][] = [
                            'key' => Str::slug($instance->roleName(), '_'),
                            'name' => $instance->roleName(),
                            'names' => $instance->defineGates(),
                        ];
                    }
                } else {
                    // Fallback: manually inspect registered items
                    $resources = method_exists($panel, 'getResources') ? array_values($panel->getResources()) : [];
                    $pages = method_exists($panel, 'getPages') ? array_values($panel->getPages()) : [];
                    $widgets = method_exists($panel, 'getWidgets') ? array_values($panel->getWidgets()) : [];

                    foreach ($resources as $class) {
                        $this->extractComponent($class, 'resources', $grouped);
                    }
                    foreach ($pages as $class) {
                        $this->extractComponent($class, 'pages', $grouped);
                    }
                    foreach ($widgets as $class) {
                        $this->extractComponent($class, 'widgets', $grouped);
                    }
                }

                // Flatten gates for this panel
                $flat = [];
                foreach (['resources', 'pages', 'widgets'] as $type) {
                    foreach ($grouped[$type] as $item) {
                        $flat = array_merge($flat, array_keys((array) ($item['names'] ?? [])));
                    }
                }
                foreach (array_keys($grouped['custom']) as $customKey) {
                    $flat[] = $customKey;
                }
                $flat = array_values(array_unique($flat));

                $summary['by_panel'][$panelId] = [
                    'grouped' => $grouped,
                    'gates' => $flat,
                ];
                $summary['all_gates'] = array_values(array_unique(array_merge($summary['all_gates'], $flat)));

                $this->line(" - Panel '{$panelId}': " . count($flat) . ' permissions found');
            } catch (\Throwable $e) {
                $this->warn('Failed scanning a panel: ' . $e->getMessage());
            }
        }

        // Print a short preview
        $this->newLine();
        $this->line('Discovered permissions (first 20):');
        foreach (array_slice($summary['all_gates'], 0, 20) as $gate) {
            $this->line(" • {$gate}");
        }
        if (count($summary['all_gates']) > 20) {
            $this->line(' ...');
        }

        // Optionally dump to file
        if ($path = $this->option('dump')) {
            $this->dumpToFile($summary, $path);
        }

        // Optionally sync Super Admin role with all permissions
        if ($this->option('super-admin')) {
            $this->syncSuperAdmin($summary['all_gates']);
        }

        $this->info('Done.');
        return self::SUCCESS;
    }

    protected function extractComponent(string $class, string $type, array &$grouped): void
    {
        try {
            if (! class_exists($class)) return;
            $instance = app($class);
            if (! method_exists($instance, 'roleName') || ! method_exists($instance, 'defineGates')) return;

            $grouped[$type][] = [
                'key' => Str::slug($instance->roleName(), '_'),
                'name' => $instance->roleName(),
                'names' => (array) $instance->defineGates(),
            ];
        } catch (\Throwable $e) {
            // ignore invalid components
        }
    }

    protected function dumpToFile(array $summary, string $path): void
    {
        $data = [
            'generated_at' => now()->toDateTimeString(),
            'panels' => array_keys($summary['by_panel'] ?? []),
            'all_gates' => $summary['all_gates'],
            'by_panel' => $summary['by_panel'],
        ];

        // Resolve path: if relative, put under storage/app/shield
        if (! Str::startsWith($path, ['/'])) {
            $dir = storage_path('app/shield');
            if (! is_dir($dir)) mkdir($dir, 0777, true);
            $path = rtrim($dir, '/').'/'.ltrim($path, '/');
        }

        file_put_contents($path, json_encode($data, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES));
        $this->info("Permissions dumped to: {$path}");
    }

    protected function syncSuperAdmin(array $gates): void
    {
        $name = config('shield.superadmin.name', 'Super Admin');
        $guard = config('shield.superadmin.guard', 'web');

        $gates = array_values(array_unique($gates));

        // Create or update role
        $role = Role::query()->updateOrCreate(
            ['name' => $name, 'guard_name' => $guard]
        );

        // Create all permissions if they don't exist
        foreach ($gates as $gate) {
            Permission::firstOrCreate([
                'name' => $gate,
                'guard_name' => $guard
            ]);
        }

        // Sync all permissions to the role
        $role->syncPermissions($gates);

        $this->info("Super Admin role synced with ".count($gates)." permissions.");
    }
}

