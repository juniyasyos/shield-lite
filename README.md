# Filament V4 & Shield Lite V3

[![Latest Stable Version](https://poser.pugx.org/juniyasyos/shield-lite/v/stable)](https://packagist.org/packages/juniyasyos/shield-lite)
[![Total Downloads](https://poser.pugx.org/juniyasyos/shield-lite/downloads)](https://packagist.org/packages/juniyasyos/shield-lite)
[![License](https://poser.pugx.org/juniyasyos/shield-lite/license)](https://packagist.org/packages/juniyasyos/shield-lite)


**Filament Shield Lite** is a free and developer-friendly **role and permission management plugin** for [FilamentPHP V4](https://filamentphp.com/).  
It helps you manage user roles and access permissions across Resources, Pages, and Widgets — with support for multi-panel apps via custom guards.

Currently in version 3, Shield Lite is more intuitive, customizable, and production-ready.

![Banner](https://github.com/juniyasyos/assets/blob/main/hexa/v2/banner.png?raw=true)


## Version Docs.

|Version|Filament|Doc.|
|:-:|:-:|-|
|V1|V3|[Read Doc.](https://github.com/juniyasyos/shield-lite/blob/main/docs/README.V1.md)|
|V2|V3|[Read Doc.](https://github.com/juniyasyos/shield-lite/blob/main/docs/README.V2.md)|
|V3|V4|[Read Doc.](https://github.com/juniyasyos/shield-lite/blob/main/README.md)|

## Index

- [Installation](#installation)
- [Adding Role Selection](#adding-role-selection)
- [Multi Panel Support](#multi-panel-support)
- [Defining Permissions](#defining-permissions)
- [Access Control](#access-control)
  - [Check Permissions in Code](#check-permissions-in-code)
- [Visible Access](#visible-access)
  - [Laravel Integration](#laravel-integration)
- [Publishing & Config](#publishing--config)
- [Seeder: Super Admin](#seeder-super-admin)
- [Default Resource Permissions](#default-resource-permissions)
- [Role Form UI/UX](#role-form-uiux)
- [Available Traits](#available-traits)
- [Features in Pro Version](#features-in-pro-version)
- [License](#license)
- [Issues & Feedback](#issues--feedback)

    
## Installation

Install the package via Composer:

```bash
composer require juniyasyos/shield-lite
````

Run the database migration:

```bash
php artisan migrate
```

Register the plugin in your Filament panel:

```php
use Filament\Panel;
use juniyasyos\ShieldLite\ShieldLite;

public function panel(Panel $panel): Panel
{
    return $panel
        ->plugins([
            ShieldLite::make(),
        ]);
}
```

Apply the trait to your `User` model:

```php
use juniyasyos\ShieldLite\ShieldLiteRolePermission;

class User extends Authenticatable
{
    use HasFactory, Notifiable;
    use ShieldLiteRolePermission;
}
```


## Adding Role Selection

To allow role assignment via the admin panel, add a select input to your `UserForm` class:

```php
use Filament\Schemas\Components\Select;
use Filament\Schemas\Components\TextInput;

public static function form(Form $form): Form
{
    return $form
        ->schema([
            TextInput::make('email')
                ->unique(ignoreRecord: true)
                ->required(),

            Select::make('roles')
                ->label(__('Role Name'))
                ->relationship('roles', 'name')
                ->placeholder(__('Superuser')),
        ]);
}
```


## Multi Panel Support

Shield Lite supports multiple panels, each with its own `auth guard`.

```php
public function panel(Panel $panel): Panel
{
    return $panel->authGuard('reseller');
}
```

```php
public function panel(Panel $panel): Panel
{
    return $panel->authGuard('customer');
}
```

Configure guards in `config/auth.php`.


## Defining Permissions

Define permissions using the `defineGates()` method on Resources, Pages, or Widgets:

```php
use juniyasyos\ShieldLite\HasShieldLite;

class UserResource extends Resource
{
    use HasShieldLite;

    public function defineGates(): array
    {
        return [
            'user.index' => __('Allows viewing the user list'),
            'user.create' => __('Allows creating a new user'),
            'user.update' => __('Allows updating users'),
            'user.delete' => __('Allows deleting users'),
        ];
    }
}
```


## Access Control

Users with no assigned role can optionally be treated as **Superusers** (full access) when enabled via config. By default this is disabled for security.

To restrict access to a resource:

```php
public static function canAccess(): bool
{
    return shield()->can('user.index');
}
```


### Check Permissions in Code

Useful in queued jobs, commands, or background services:

```php
return shield()->user(User::first())->can('user.index');
```


### Visible Access

Use `visible()` to conditionally display UI elements:

```php
Actions\CreateAction::make('create')
    ->visible(fn() => shield()->can(['user.index', 'user.create']));
```


### Laravel Integration

You can still use Laravel’s native authorization:

```php
Auth::user()->can('user.create');

Gate::allows('user.create');

Gate::forUser(User::first())->allows('user.create');

@can('user.create')
    // Blade directive
@endcan
```

## Publishing & Config

Publish configuration, migrations, and example seeders:

```bash
php artisan vendor:publish --tag=shield-config
php artisan vendor:publish --tag=shield-migrations
php artisan vendor:publish --tag=shield-seeders
```

Key configuration in `config/shield.php`:

- `navigation.label` and `navigation.group`: Customize the plugin menu label & group.
- `custom_permissions`: Define custom permission keys shown under the “Custom” tab.
- `superuser_if_no_role` (bool): When true, users without roles have full access.
- `cache.enabled` (bool), `cache.ttl` (int), `cache.store` (string|null): Cache gate discovery and UI groupings per panel for performance.
- `superadmin.name`, `superadmin.guard`: Defaults used by the example Super Admin seeder.

## Seeder: Super Admin

A publishable example seeder creates/updates a “Super Admin” role and grants all discovered permissions across panels.

1) Publish the seeder:

```bash
php artisan vendor:publish --tag=shield-seeders
```

2) Register in `DatabaseSeeder`:

```php
public function run(): void
{
    $this->call(\\Database\\Seeders\\ShieldSuperAdminSeeder::class);
}
```

3) Seed:

```bash
php artisan db:seed --class=Database\\Seeders\\ShieldSuperAdminSeeder
```

Configure the role name/guard via `config('shield.superadmin.*')`.

## Default Resource Permissions

When a Filament Resource uses `HasShieldLite` and does not override `defineGates()`, default permissions are auto‑generated using the resource label as slug:

- `<slug>.view`
- `<slug>.create`
- `<slug>.update`
- `<slug>.delete`

Example: a `UserResource` with label “User” yields `user.view`, `user.create`, `user.update`, `user.delete`.

You can override `defineGates()` to fully customize permissions.

## Role Form UI/UX

Shield Lite provides a streamlined Role editor with improved UX:

- Tabs: Permissions are grouped into Resources, Pages, Widgets, and Custom.
- Per‑tab bulk toggle: Quickly select/clear all permissions within the current tab.
- Global toggle: One control in “Pengaturan Peran” to select/clear all permissions across all tabs.
- Searchable lists: Each group supports search and bulk toggle actions.
- Accessibility: ARIA labels and helper texts to improve screen reader and keyboard navigation.


## Available Traits

| Trait                    | Description                                   |
| ------------------------ | --------------------------------------------- |
| `ShieldLiteRolePermission` | Apply to your `Authenticatable` user model    |
| `HasShieldLite`            | Use in Resources, Pages, Widgets, or Clusters |
| `UuidGenerator`          | Use on models with `uuid` fields              |
| `UlidGenerator`          | Use on models with `ulid` fields              |


## Features in Pro Version

Need more flexibility and control?

Filament Shield **Pro v3** unlocks powerful features designed for serious projects:

* Role & permission descriptions
* Custom role sorting
* Gate grouping (with nested access)
* Multi-tenancy support
* Meta option storage

A small investment for a much more capable permission system.

Learn more in the official documentation:  
👉 [Shield Pro Documentation](https://github.com/juniyasyos/shield-docs)


## License

This project is open-source and licensed under the **MIT License**.
You are free to use, modify, and distribute it with attribution.


## Issues & Feedback

Found a bug or want to contribute?

Open an issue at:
[https://github.com/juniyasyos/shield-lite/issues](https://github.com/juniyasyos/shield-lite/issues)

Thank you for using Filament Shield Lite!
