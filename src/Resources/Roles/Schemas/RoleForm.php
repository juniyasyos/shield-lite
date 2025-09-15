<?php

namespace juniyasyos\ShieldLite\Resources\Roles\Schemas;

use Filament\Forms\Components\CheckboxList;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Schemas\Components\Utilities\Get as SchemaGet;
use Filament\Schemas\Components\Utilities\Set as SchemaSet;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Components\Tabs;
use Filament\Schemas\Components\Tabs\Tab;
use Filament\Schemas\Schema;
use Filament\Support\Enums\GridDirection;
// (SchemaGet imported above)

class RoleForm
{
    public static function configure(Schema $schema): Schema
    {
        $grouped = (array) config('shield-lite-roles', []);

        $makeSections = function (array $items, string $prefixKey) {
            return collect($items)->map(function ($role) use ($prefixKey) {
                $key   = $role['key'] ?? \Illuminate\Support\Str::slug($role['name'] ?? 'group', '_');
                $label = $role['name'] ?? 'Permissions';

                return Section::make($label)
                    ->collapsed(false)
                    ->extraAttributes(['class' => 'rounded-2xl'])
                    ->schema([
                        CheckboxList::make("gates.{$key}")
                            ->hiddenLabel()
                            ->extraAttributes(['aria-label' => __('shield::messages.checkbox_aria', ['label' => $label])])
                            ->columns([
                                'default' => 2,
                                'lg' => 3,
                            ])
                            ->gridDirection(GridDirection::Row)
                            // ->searchable()
                            ->options($role['names'] ?? [])
                            ->bulkToggleable()
                            ->hint(__('Centang untuk memberi akses fitur terkait'))
                            ->hintIcon('heroicon-m-shield-check'),
                    ]);
            })->all();
        };

        $resourceSections = ! empty($grouped['resources'] ?? []) ? $makeSections($grouped['resources'], 'resources') : [];
        $pageSections     = ! empty($grouped['pages'] ?? [])     ? $makeSections($grouped['pages'], 'pages')         : [];
        $widgetSections   = ! empty($grouped['widgets'] ?? [])   ? $makeSections($grouped['widgets'], 'widgets')     : [];

        $customOptions  = (isset($grouped['custom']) && is_array($grouped['custom'])) ? $grouped['custom'] : [];
        $customSections = [];
        if (! empty($customOptions)) {
            $customSections[] = Section::make(__('shield::messages.custom_permissions'))
                ->collapsed()
                ->schema([
                    CheckboxList::make('gates.custom')
                        ->hiddenLabel()
                        ->extraAttributes(['aria-label' => __('shield::messages.checkbox_aria', ['label' => __('shield::messages.custom_permissions')])])
                        ->columns([
                            'default' => 2,
                            'lg' => 3,
                        ])
                        ->gridDirection(GridDirection::Row)
                        // ->searchable()
                        ->options($customOptions)
                        ->bulkToggleable()
                        ->hint(__('Izin khusus di luar resource/page/widget'))
                        ->hintIcon('heroicon-m-sparkles'),
                ]);
        }

        $countKeys = function (SchemaGet $get, array $keys): int {
            $total = 0;
            foreach ($keys as $key) {
                $vals = (array) $get("gates.{$key}");
                $total += count(array_filter($vals));
            }
            return $total;
        };

        $resourceKeys = array_map(fn($r) => $r['key'] ?? \Illuminate\Support\Str::slug($r['name'] ?? 'group', '_'), $grouped['resources'] ?? []);
        $pageKeys     = array_map(fn($r) => $r['key'] ?? \Illuminate\Support\Str::slug($r['name'] ?? 'group', '_'), $grouped['pages'] ?? []);
        $widgetKeys   = array_map(fn($r) => $r['key'] ?? \Illuminate\Support\Str::slug($r['name'] ?? 'group', '_'), $grouped['widgets'] ?? []);

        $tabs = [];

        // Build quick toggles per tab
        $buildTabToggleSection = function (array $tabKeys, string $id) use ($grouped) {
            if (empty($tabKeys)) return null;
            return Section::make()
                ->schema([
                    Toggle::make("tab_toggle_{$id}")
                        ->label(__('shield::messages.tab_toggle_label'))
                        ->helperText(__('shield::messages.tab_toggle_hint'))
                        ->dehydrated(false)
                        ->live()
                        ->afterStateUpdated(function (SchemaSet $set, SchemaGet $get, $state) use ($tabKeys, $grouped, $id) {
                            $gates = (array) $get('gates');
                            // Build lookup of key => option keys
                            $keyOptions = [];
                            foreach (['resources', 'pages', 'widgets'] as $group) {
                                foreach (($grouped[$group] ?? []) as $role) {
                                    $k = $role['key'] ?? \Illuminate\Support\Str::slug($role['name'] ?? 'group', '_');
                                    $keyOptions[$k] = array_keys($role['names'] ?? []);
                                }
                            }
                            $keyOptions['custom'] = array_keys($grouped['custom'] ?? []);

                            foreach ($tabKeys as $k) {
                                $gates[$k] = $state ? ($keyOptions[$k] ?? []) : [];
                            }
                            $set('gates', $gates);
                            $set("tab_toggle_{$id}", false);
                        }),
                ]);
        };

        if (! empty($resourceSections)) {
            $resToggle = $buildTabToggleSection($resourceKeys, 'resources');
            $tabs[] = Tab::make(__('Resources'))
                ->badge(fn(SchemaGet $get) => (string) $countKeys($get, $resourceKeys))
                ->schema(array_values(array_filter([$resToggle, ...$resourceSections])));
        }
        if (! empty($pageSections)) {
            $pgToggle = $buildTabToggleSection($pageKeys, 'pages');
            $tabs[] = Tab::make(__('Pages'))
                ->badge(fn(SchemaGet $get) => (string) $countKeys($get, $pageKeys))
                ->schema(array_values(array_filter([$pgToggle, ...$pageSections])));
        }
        if (! empty($widgetSections)) {
            $wdToggle = $buildTabToggleSection($widgetKeys, 'widgets');
            $tabs[] = Tab::make(__('Widgets'))
                ->badge(fn(SchemaGet $get) => (string) $countKeys($get, $widgetKeys))
                ->schema(array_values(array_filter([$wdToggle, ...$widgetSections])));
        }
        if (! empty($customSections)) {
            // Custom tab toggle acts on a single key 'custom'
            $csToggle = $buildTabToggleSection(['custom'], 'custom');
            $tabs[] = Tab::make(__('Custom'))
                ->badge(fn(SchemaGet $get) => (string) count((array) $get('gates.custom')))
                ->schema(array_values(array_filter([$csToggle, ...$customSections])));
        }

        $globalTools = Section::make(__('Pengaturan Peran'))
            ->collapsible()
            ->schema([
                TextInput::make('name')
                    ->label(__('shield::messages.role_name'))
                    ->placeholder(__('Supervisor'))
                    ->maxLength(100)
                    ->required()
                    ->rule('string')
                    ->rule('min:3')
                    ->unique(ignoreRecord: true),

                Select::make('guard_name')
                    ->label(__('shield::messages.guard'))
                    ->options([
                        'web' => 'web',
                        'api' => 'api',
                    ])
                    ->default('web'),

                Toggle::make('all_permissions')
                    ->label(__('shield::messages.global_toggle_label'))
                    ->helperText(__('shield::messages.global_toggle_hint'))
                    ->extraAttributes(['aria-label' => __('shield::messages.global_toggle_label')])
                    ->inline(false)
                    ->dehydrated(false)
                    ->live()
                    ->afterStateUpdated(function (SchemaSet $set, SchemaGet $get, $state) use ($grouped) {
                        $result = [];
                        if ($state) {
                            foreach (['resources', 'pages', 'widgets'] as $group) {
                                foreach (($grouped[$group] ?? []) as $role) {
                                    $key = $role['key'] ?? \Illuminate\Support\Str::slug($role['name'] ?? 'group', '_');
                                    $result[$key] = array_keys($role['names'] ?? []);
                                }
                            }
                            if (! empty($grouped['custom'] ?? [])) {
                                $result['custom'] = array_keys($grouped['custom']);
                            }
                        } else {
                            foreach (['resources', 'pages', 'widgets'] as $group) {
                                foreach (($grouped[$group] ?? []) as $role) {
                                    $key = $role['key'] ?? \Illuminate\Support\Str::slug($role['name'] ?? 'group', '_');
                                    $result[$key] = [];
                                }
                            }
                            if (! empty($grouped['custom'] ?? [])) {
                                $result['custom'] = [];
                            }
                        }
                        $set('gates', $result);
                    }),
            ])
            ->columns([
                'default' => 1,
                'lg' => 3,
            ]);

        $permissionsTabs = Tabs::make('permissions-tabs')
            ->tabs($tabs)
            ->persistTabInQueryString()
            ->extraAttributes(['class' => 'rounded-2xl']);

        return $schema
            ->columns(1)
            ->components([
                $globalTools,
                $permissionsTabs,
            ]);
    }
}
