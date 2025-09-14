# Filament Shield Lite - V2

**Filament Shield Lite** is a free and developer-friendly **role and permission management plugin** for [FilamentPHP](https://filamentphp.com/).  
It helps you manage user roles and access permissions across Resources, Pages, and Widgets — with support for multi-panel apps via custom guards.

Currently in version 2, Shield Lite is more intuitive, customizable, and production-ready.

![Banner](https://github.com/juniyasyos/assets/blob/main/hexa/v2/banner.png?raw=true)

## Version Docs.

|Version|Filament|Doc.|
|:-:|:-:|-|
|V1|V3|[Read Doc.](https://github.com/juniyasyos/shield-lite/blob/main/docs/README.V1.md)|
|V2|V3|[Read Doc.](https://github.com/juniyasyos/shield-lite/blob/main/docs/README.V2.md)|
|V3|V4|[Read Doc.](https://github.com/juniyasyos/shield-lite/blob/main/README.md)|

## Installation

Install the package via Composer:

```bash
composer require juniyasyos/shield-lite:"2.0"
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

To allow role assignment via the admin panel, add a select input to your `UserResource` form:

```php
use Filament\Forms;

public static function form(Form $form): Form
{
    return $form
        ->schema([
            Schemas\Components\TextInput::make('email')
                ->unique(ignoreRecord: true)
                ->required(),

            Schemas\Components\Select::make('roles')
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

Users with no assigned role are treated as **Superusers** and have full access by default.

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


## Available Traits

| Trait                    | Description                                   |
| ------------------------ | --------------------------------------------- |
| `ShieldLiteRolePermission` | Apply to your `Authenticatable` user model    |
| `HasShieldLite`            | Use in Resources, Pages, Widgets, or Clusters |
| `UuidGenerator`          | Use on models with `uuid` fields              |
| `UlidGenerator`          | Use on models with `ulid` fields              |


## Features in Pro Version

Need more flexibility and control?

Filament Shield **Pro v2** unlocks powerful features designed for serious projects:

* Role & permission descriptions
* Custom role sorting
* Gate grouping (with nested access)
* Multi-tenancy support
* Meta option storage

All of this — **starting at just $1 per license**.  
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
