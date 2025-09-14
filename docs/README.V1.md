# Filament Shield Lite

Filament Shield Lite is an effortless role & permission plugin for Filament, inspired by the concept of [juniyasyos/ladmin](https://github.com/juniyasyos/ladmin). This concept facilitates managing each role and permission inline with code and provides an easy-to-understand interface.

This plugin is intended only for Administrators, as it has a separate admin table from the user table provided by Laravel. Additionally, this plugin will replace the `auth.php` configuration file.

![](https://github.com/juniyasyos/assets/blob/main/hexa/v1/edit.png?raw=true)

## About Filament

[FilamentPHP](https://filamentphp.com/) is a lightweight and flexible PHP framework designed for building web applications. It aims to simplify application development by providing a clear structure and high modularity. The framework emphasizes speed, efficiency, and comes with many built-in features that facilitate effective web application development.

## Version Docs.

|Version|Filament|Doc.|
|:-:|:-:|-|
|V1|V3|[Read Doc.](https://github.com/juniyasyos/shield-lite/blob/main/docs/README.V1.md)|
|V2|V3|[Read Doc.](https://github.com/juniyasyos/shield-lite/blob/main/docs/README.V2.md)|
|V3|V4|[Read Doc.](https://github.com/juniyasyos/shield-lite/blob/main/README.md)|

## Installation

> **Note** <br>
You need to install the filament package first. You can refer to the official site at [FilamentPHP](https://filamentphp.com)

You can install it by running the command below:

```bash
composer require juniyasyos/shield-lite:"1.3"
```

Then, proceed with the installation of the shield plugin:
```bash
php artisan shield:install
```

Install database migrations:
```bash
php artisan migrate
```

Create a superadmin account for admin login:
```bash
php artisan shield:account --create
```

## Plugin Setup

Add the Filament `ShieldLite` plugin to the created panel. If you haven't created one yet, see how to do it here [Creating a new panel](https://filamentphp.com/docs/3.x/panels/configuration#creating-a-new-panel).

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

## Declaring Access Permissions

### Resource & Page

To declare access permissions for Resources and Pages, for Clusters you need to upgrade to the [juniyasyos/shield](https://github.com/juniyasyos/shield-docs) package.

```php
use juniyasyos\ShieldLite\Traits\HexAccess;

. . .

use HexAccess;

protected static ?string $permissionId = 'access.user';

protected static ?string $descriptionPermission = 'Admin can manage User accounts';

/**
 * Additional permission (optional)
 * You can add it or not depending on the needs of your application.
 */
protected static ?array $subPermissions = [
    'access.user.create' => 'Can Create',
    'access.user.edit' => 'Can Edit',
    'access.user.delete' => 'Can Delete',
];

public static function canAccess(): bool
{
    return shield()->can(static::$permissionId);
}

. . .
```

### Actions, etc.

You can use the `visible()` method on several `Class Components`. For example, let's try it on a button.

```php
Actions\EditAction::make()
    ->visible(shield()->can('access.user.edit')),
```

For giving access to classes extended to `Filament\Resources\Pages\EditRecord`, `Filament\Resources\Pages\CreateRecord`, `Filament\Resources\Pages\ListRecords`, `Filament\Resources\Pages\ViewRecords`, you can use:
```php
/**
 * @param  array<string, mixed>  $parameters
 */
public static function canAccess(array $parameters = []): bool
{
    return shield()->can('access.user.edit');
}
```

## Checking Access Permissions

Access can be granted to Resources, Pages, Widgets, Button Actions, etc. The access can be given as shown below.

Using the shield utility function:
```php
shield()->can('shield.admin')
```

Using Laravel's auth can function:
```php
auth()->user()?->can('shield.admin')
```

Using Laravel's Gate class:
```php
use Illuminate\Support\Facades\Gate;

. . .

Gate::allows('shield.admin')
```

In a blade template, you can use it as shown below.

```html
<div>
    @can('shield.admin')
        // Content here ...
    @endcan
</div>
```

## License
This project is licensed under the MIT License - see the [LICENSE](https://github.com/juniyasyos/shield-lite/blob/main/LICENSE.md) file for details.

## Issue

If you encounter any issues with this plugin, you can submit them to the repository:
[Filament Shield Lite Issue](https://github.com/juniyasyos/shield-lite/issues)

Thank you for using this plugin. We hope it speeds up your process in creating powerful applications.

Happy Coding 🧑‍💻 🧑‍💻 🧑‍💻
