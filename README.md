# Shield Lite# Filament V4 & Shield Lite V3



A flexible and lightweight Laravel permission and role management system designed for Filament applications.[![Latest Stable Version](https://poser.pugx.org/juniyasyos/shield-lite/v/stable)](https://packagist.org/packages/juniyasyos/shield-lite)

[![Total Downloads](https://poser.pugx.org/juniyasyos/shield-lite/downloads)](https://packagist.org/packages/juniyasyos/shield-lite)

## Features[![License](https://poser.pugx.org/juniyasyos/shield-lite/license)](https://packagist.org/packages/juniyasyos/shield-lite)



- **Trait-based Integration**: Easy setup with User model traits

- **Multiple Permission Backends**: Support for Spatie Permission package or built-in array-based permissions**Filament Shield Lite** is a free and developer-friendly **role and permission management plugin** for [FilamentPHP V4](https://filamentphp.com/).  

- **Automatic Policy Resolution**: Generate CRUD policies automatically without writing boilerplate codeIt helps you manage user roles and access permissions across Resources, Pages, and Widgets — with support for multi-panel apps via custom guards.

- **Configurable Ability Naming**: Flexible ability format configuration (dot notation, colon notation, etc.)

- **Generic Policies**: Use magic methods for automatic permission checkingCurrently in version 3, Shield Lite is more intuitive, customizable, and production-ready.

- **Filament Panel Integration**: Seamless integration with Filament admin panels

- **Laravel Gate Integration**: Works with Laravel's built-in authorization system![Banner](https://github.com/juniyasyos/assets/blob/main/hexa/v2/banner.png?raw=true)



## Installation

## Version Docs.

Install via Composer:

|Version|Filament|Doc.|

```bash|:-:|:-:|-|

composer require juniyasyos/shield-lite|V1|V3|[Read Doc.](https://github.com/juniyasyos/shield-lite/blob/main/docs/README.V1.md)|

```|V2|V3|[Read Doc.](https://github.com/juniyasyos/shield-lite/blob/main/docs/README.V2.md)|

|V3|V4|[Read Doc.](https://github.com/juniyasyos/shield-lite/blob/main/README.md)|

Publish the configuration file:

## Index

```bash

php artisan vendor:publish --tag=shield-lite-config- [Installation](#installation)

```- [Adding Role Selection](#adding-role-selection)

- [Multi Panel Support](#multi-panel-support)

## Quick Start- [Defining Permissions](#defining-permissions)

- [Access Control](#access-control)

### 1. Configure Your User Model  - [Check Permissions in Code](#check-permissions-in-code)

- [Visible Access](#visible-access)

Add the Shield Lite traits to your User model:  - [Laravel Integration](#laravel-integration)

- [Publishing & Config](#publishing--config)

```php- [Publish Command](#publish-command)

<?php- [Permission Generator](#permission-generator)

- [Navigation Visibility](#navigation-visibility)

namespace App\Models;- [Seeder: Super Admin](#seeder-super-admin)

- [Seeder: Users + Roles](#seeder-users--roles)

use Illuminate\Foundation\Auth\User as Authenticatable;- [Default Resource Permissions](#default-resource-permissions)

use juniyasyos\ShieldLite\Concerns\HasShieldRoles;- [Role Form UI/UX](#role-form-uiux)

use juniyasyos\ShieldLite\Concerns\HasShieldPermissions;- [Available Traits](#available-traits)

use juniyasyos\ShieldLite\Concerns\AuthorizesShield;- [Features in Pro Version](#features-in-pro-version)

- [License](#license)

class User extends Authenticatable- [Issues & Feedback](#issues--feedback)

{

    use HasShieldRoles, HasShieldPermissions, AuthorizesShield;    

    ## Installation

    // Your existing model code...

}Install the package via Composer:

```

```bash

### 2. Configure Permission Drivercomposer require juniyasyos/shield-lite

````

Update `config/shield-lite.php`:

Run the database migration:

```php

return [```bash

    'driver' => 'spatie', // or 'array' for built-in permissionsphp artisan migrate

    'ability_format' => '{resource}.{action}', // users.view, users.create```

    'auto_resolve_policies' => true, // Automatic policy registration

    // ... other configurationRegister the plugin in your Filament panel:

];

``````php

use Filament\Panel;

### 3. Usage Examplesuse juniyasyos\ShieldLite\ShieldLite;



#### Check Permissionspublic function panel(Panel $panel): Panel

{

```php    return $panel

// Using Laravel Gates        ->plugins([

if ($user->can('users.view')) {            ShieldLite::make(),

    // User can view users        ]);

}}

```

// Using traits directly

if ($user->hasPermissionTo('users.create')) {Apply the trait to your `User` model:

    // User can create users

}```php

use juniyasyos\ShieldLite\ShieldLiteRolePermission;

// Check roles

if ($user->hasRole('admin')) {class User extends Authenticatable

    // User has admin role{

}    use HasFactory, Notifiable;

```    use ShieldLiteRolePermission;

}

#### Assign Roles and Permissions```



```php

// Assign role## Quick Usage (V4)

$user->assignRole('admin');

- Plugin otomatis mendaftarkan Resources: Roles dan Users.

// Give permission- Users Resource sudah memiliki aksi “Set Roles” (row & bulk) untuk mengatur role dan default role per user.

$user->givePermissionTo('users.create');- Jika Anda sudah punya Users Resource sendiri, sembunyikan resource Anda (mis. `protected static bool $shouldRegisterNavigation = false;`) agar menu tidak dobel.

- Paket ini memuat migrasi termasuk kolom `users.default_role_id` untuk menyimpan default role.

// Sync roles- Seeder:

$user->syncRoles(['admin', 'moderator']);  - Super Admin (semua permission): `php artisan db:seed --class=Database\\Seeders\\ShieldSuperAdminSeeder`

```  - Admin contoh (khusus permission User): lihat contoh di bagian Seeder di bawah.

  - Users + Roles contoh: `php artisan db:seed --class=Database\\Seeders\\UserSeeder`

#### Automatic Policy Resolution



Shield Lite automatically generates policies for your models:## Built-in Resources



```phpShield Lite otomatis menyediakan Resources berikut:

// This automatically works with your User model

Gate::authorize('view', $user); // Checks users.view permission- Roles: pengelolaan peran dan daftar permission.

Gate::authorize('update', $user); // Checks users.update permission- Users: pengelolaan akun pengguna lengkap dengan aksi “Set Roles” (row & bulk) untuk menetapkan role dan default role.

Gate::authorize('delete', $user); // Checks users.delete permission

```Jika Anda sudah memiliki Users Resource sendiri, sembunyikan atau nonaktifkan navigasinya agar tidak dobel di menu.



## Configuration

## Adding Role Selection

### Permission Drivers

To allow role assignment via the admin panel, add a select input to your `UserForm` class:

#### Spatie Permission Driver

```php

When using `'driver' => 'spatie'`, Shield Lite integrates with the Spatie Permission package:use Filament\Schemas\Components\Select;

use Filament\Schemas\Components\TextInput;

```bash

composer require spatie/laravel-permissionpublic static function form(Form $form): Form

php artisan vendor:publish --provider="Spatie\Permission\PermissionServiceProvider"{

php artisan migrate    return $form

```        ->schema([

            TextInput::make('email')

#### Array Driver                ->unique(ignoreRecord: true)

                ->required(),

When using `'driver' => 'array'`, permissions are defined in the configuration:

            Select::make('roles')

```php                ->label(__('Role Name'))

'permissions' => [                ->relationship('roles', 'name')

    'roles' => [                ->placeholder(__('Superuser')),

        'super_admin' => ['*'], // All permissions        ]);

        'admin' => [}

            'users.*', // All user permissions```

            'posts.*', // All post permissions

        ],

        'user' => [## Multi Panel Support

            'users.view', // Can view own profile

            'users.update', // Can update own profileShield Lite supports multiple panels, each with its own `auth guard`.

        ],

    ],```php

    'users' => [public function panel(Panel $panel): Panel

        1 => ['special.permission'], // User-specific permissions{

    ],    return $panel->authGuard('reseller');

],}

``````



### Ability Formats```php

public function panel(Panel $panel): Panel

Configure how abilities are named:{

    return $panel->authGuard('customer');

```php}

'ability_format' => '{resource}.{action}', // users.view```

// or

'ability_format' => '{action}:{resource}', // view:usersConfigure guards in `config/auth.php`.

// or  

'ability_format' => '{resource}_{action}', // users_view

```## Defining Permissions



### Super Admin ConfigurationDefine permissions using the `defineGates()` method on Resources, Pages, or Widgets:



Configure how super admins are identified:```php

use juniyasyos\ShieldLite\HasShieldLite;

```php

'super_admin' => [class UserResource extends Resource

    'role' => 'super_admin', // Check by role name{

    // OR    use HasShieldLite;

    'attribute' => 'is_super_admin', // Check by user attribute

    // OR    public function defineGates(): array

    'user_ids' => [1], // Check by user ID    {

    // OR        return [

    'callback' => fn($user) => $user->email === 'admin@example.com',            'user.index' => __('Allows viewing the user list'),

],            'user.create' => __('Allows creating a new user'),

```            'user.update' => __('Allows updating users'),

            'user.delete' => __('Allows deleting users'),

## Filament Integration        ];

    }

### Using with Filament Resources}

```

Shield Lite automatically generates policies for your Filament resources:



```php## Access Control

<?php

Users with no assigned role can optionally be treated as **Superusers** (full access) when enabled via config. By default this is disabled for security.

namespace App\Filament\Resources;

To restrict access to a resource:

use Filament\Resources\Resource;

```php

class UserResource extends Resourcepublic static function canAccess(): bool

{{

    protected static ?string $model = User::class;    return shield()->can('user.index');

    }

    // Shield Lite automatically handles authorization for:```

    // - viewAny() -> users.view_any

    // - view() -> users.view  

    // - create() -> users.create### Check Permissions in Code

    // - update() -> users.update

    // - delete() -> users.deleteUseful in queued jobs, commands, or background services:

    // - restore() -> users.restore

    // - forceDelete() -> users.force_delete```php

}return shield()->user(User::first())->can('user.index');

``````



### Custom Policies

### Visible Access

If you need custom logic, create your own policy and Shield Lite will detect it:

Use `visible()` to conditionally display UI elements:

```php

<?php```php

Actions\CreateAction::make('create')

namespace App\Policies;    ->visible(fn() => shield()->can(['user.index', 'user.create']));

```

use App\Models\User;



class UserPolicy### Laravel Integration

{

    public function viewAny(User $user): boolYou can still use Laravel’s native authorization:

    {

        return $user->can('users.view_any');```php

    }Auth::user()->can('user.create');

    

    public function view(User $user, User $model): boolGate::allows('user.create');

    {

        return $user->can('users.view') || $user->id === $model->id;Gate::forUser(User::first())->allows('user.create');

    }

    @can('user.create')

    // ... other methods    // Blade directive

}@endcan

``````



## Advanced Usage## Publishing & Config



### Custom AbilitiesPublish configuration, migrations, and example seeders:



Define custom abilities for specific resources:```bash

php artisan vendor:publish --tag=shield-config

```phpphp artisan vendor:publish --tag=shield-migrations

'abilities' => [php artisan vendor:publish --tag=shield-seeders

    'custom' => [```

        'users' => ['ban', 'activate', 'impersonate'],

        'posts' => ['publish', 'feature', 'pin'],Catatan: paket ini otomatis memuat migrasi (termasuk kolom `users.default_role_id`). Publish hanya diperlukan jika Anda ingin mengubah migrasi bawaan.

    ],

],Key configuration in `config/shield.php`:

```

- `navigation.label` and `navigation.role_group`: Customize the plugin menu label & group.

### Excluding Models from Auto-Policy- `navigation.roles_nav` / `navigation.users_nav` (bool): Control whether Roles/Users resources appear in the sidebar. Defaults to visible on `local` only.

- `navigation.visible_in` (array): Limit visibility to certain environments, e.g. `['local', 'staging']`.

Prevent certain models from having automatic policies:- `custom_permissions`: Define custom permission keys shown under the “Custom” tab.

- `superuser_if_no_role` (bool): When true, users without roles have full access.

```php- `cache.enabled` (bool), `cache.ttl` (int), `cache.store` (string|null): Cache gate discovery and UI groupings per panel for performance.

'excluded_models' => [- `superadmin.name`, `superadmin.guard`: Defaults used by the example Super Admin seeder.

    App\Models\SystemLog::class,

    App\Models\AuditTrail::class,## Publish Command

],

```Publish everything for Shield Lite (config, migrations, seeders, views, translations) and rebuild Filament assets in one go:



### Manual Policy Registration```bash

php artisan shield:publish

Disable auto-discovery and register policies manually:```



```phpOptions:

'auto_resolve_policies' => false,

```- `--force` Overwrite any existing files.

- `--resources` Publish minimal Resource stubs (that extend the package Resources) into `app/Filament/Resources` and disable package resources in `config/shield.php` to avoid conflicts. This avoids code duplication; you only override what you need.

Then register in your `AuthServiceProvider`:  - Perintah ini juga memetakan config `shield.resources.roles/users` agar Panel mendaftarkan Resource milik App, bukan Resource paket.



```php## Permission Generator

use juniyasyos\ShieldLite\Policies\GenericPolicy;

Generate permission otomatis dari Resources, Pages, dan Widgets yang terdaftar di setiap Filament Panel.

protected $policies = [

    User::class => GenericPolicy::class,- Command: `php artisan shield:generate`

];- Fungsi: Memindai semua komponen yang memiliki `defineGates()`/`roleName()` lalu mengumpulkan daftar permission dan ringkasannya per panel (mirip Filament Shield).

```

Opsi:

## Middleware

- `--dump=path`: Simpan output ke file JSON.

Shield Lite provides middleware for protecting routes:  - Jika path relatif, file akan disimpan ke `storage/app/shield/{path}`.

- `--super-admin`: Buat/Update role “Super Admin” dengan semua permission yang ditemukan.

```php  - Nama dan guard dapat diubah via `config/shield.php` (`superadmin.name`, `superadmin.guard`).

// In your routes

Route::middleware(['auth', 'shield.permission:users.view'])->group(function () {Contoh:

    // Protected routes

});```bash

# Lihat daftar permission yang terdeteksi (preview di terminal)

Route::middleware(['auth', 'shield.role:admin'])->group(function () {php artisan shield:generate

    // Admin only routes  

});# Simpan hasil lengkap ke file JSON

```php artisan shield:generate --dump=permissions.json



## API Reference# Sinkronkan role Super Admin dengan semua permission

php artisan shield:generate --super-admin

### HasShieldRoles Trait```



```phpCara Kerja Singkat:

$user->assignRole(string|array $roles): self

$user->removeRole(string|array $roles): self  - Untuk Resource, trait `HasShieldLite` otomatis mengisi default permission CRUD:

$user->syncRoles(array $roles): self  - `*.view`, `*.create`, `*.update`, `*.delete` (label mengikuti `getModelLabel`).

$user->hasRole(string $role): bool- Anda dapat menimpa/menambah permission custom pada masing-masing Resource/Page/Widget melalui `defineGates()`.

$user->hasAnyRole(array $roles): bool- Permission custom global juga bisa ditambahkan melalui `config('shield.custom_permissions')` dan akan ikut terdeteksi.

$user->hasAllRoles(array $roles): bool- Mendukung multi-panel; output dikelompokkan per panel dan disatukan ke daftar `all_gates`.

$user->getRoleNames(): Collection

```## Navigation Visibility



### HasShieldPermissions TraitTerkadang Anda hanya butuh pengaturan permission tanpa menampilkan menu Resources (Roles/Users) di produksi. Anda bisa mengendalikan visibilitas navigasi lewat konfigurasi berikut (default: tampil di `local` saja):



```php```php

$user->givePermissionTo(string|array $permissions): self// config/shield.php

$user->revokePermissionTo(string|array $permissions): selfreturn [

$user->syncPermissions(array $permissions): self    'navigation' => [

$user->hasPermissionTo(string $permission): bool        'roles_nav' => env('SHIELD_ROLES_NAV', env('APP_ENV') === 'local'),

$user->hasAnyPermission(array $permissions): bool        'users_nav' => env('SHIELD_USERS_NAV', env('APP_ENV') === 'local'),

$user->hasAllPermissions(array $permissions): bool        // Opsional: batasi ke environment tertentu

$user->getAllPermissions(): Collection        'visible_in' => [], // contoh: ['local', 'staging']

```    ],

];

### AuthorizesShield Trait```



```phpAtau kendalikan via ENV:

$user->can(string $ability, $arguments = []): bool

$user->cannot(string $ability, $arguments = []): bool```env

```SHIELD_ROLES_NAV=false

SHIELD_USERS_NAV=false

### Ability Class```



```phpCatatan:

Ability::normalize(string $resource, string $action): string

Ability::parse(string $ability): array- Resource tetap terdaftar (tergantung `register_resources`), namun item navigasi bisa disembunyikan. Akses langsung via URL tetap tunduk pada `canAccess()` dan permission terkait.

Ability::generateCrudAbilities(string $resource): array- Jika ingin menonaktifkan pendaftaran Resource sama sekali, atur:

Ability::matches(string $ability, string $pattern): bool  - `config('shield.register_resources.roles')` atau `users` ke `false`.

```

## Seeder: Super Admin

## Testing

A publishable example seeder creates/updates a “Super Admin” role and grants all discovered permissions across panels.

Shield Lite includes comprehensive tests. Run them with:

1) Publish the seeder:

```bash

vendor/bin/pest packages/juniyasyos/shield-lite/tests/```bash

```php artisan vendor:publish --tag=shield-seeders

```

## Contributing

2) Register in `DatabaseSeeder`:

Contributions are welcome! Please see [CONTRIBUTING.md](CONTRIBUTING.md) for details.

```php

## Licensepublic function run(): void

{

The MIT License (MIT). Please see [License File](LICENSE) for more information.    $this->call(\\Database\\Seeders\\ShieldSuperAdminSeeder::class);

}

## Changelog```



See [CHANGELOG.md](CHANGELOG.md) for recent changes.3) Seed:



## Upgrade Guide```bash

php artisan db:seed --class=Database\\Seeders\\ShieldSuperAdminSeeder

See [UPGRADE.md](UPGRADE.md) for migration instructions from previous versions.```

Configure the role name/guard via `config('shield.superadmin.*')`.

Contoh Admin (hanya permission User):

```php
// database/seeders/ShieldAdminSeeder.php
class ShieldAdminSeeder extends Seeder
{
    public function run(): void
    {
        $gates = [];
        foreach (Filament::getPanels() as $panel) {
            $gates = array_merge($gates, array_filter(
                shield()->panelGates($panel),
                fn ($g) => str_starts_with($g, 'user.')
            ));
        }
        $gates = array_values(array_unique($gates));
        ShieldRole::updateOrCreate(['name' => 'Admin', 'guard' => 'web'], [
            'created_by_name' => 'system',
            'access' => [$gates],
        ]);
    }
}
```

Jalankan:

```bash
php artisan db:seed --class=Database\\Seeders\\ShieldAdminSeeder
```

## Seeder: Users + Roles

Seeder contoh ini menambahkan 3 user sekaligus role-nya, serta menyetel `default_role_id` pada masing‑masing user.

- Role yang dibuat: `Admin`, `Manager`, `Staff` (guard: `web`).
- User yang dibuat:
  - Email: `admin@gmail.com` — Role: `Admin`
  - Email: `manager@gmail.com` — Role: `Manager`
  - Email: `staff@gmail.com` — Role: `Staff`
- Password default: `password`

Langkah pemakaian:

1) Publish seeder (opsional, jika ingin mengubah):

```bash
php artisan vendor:publish --tag=shield-seeders
```

2) Jalankan seeder:

```bash
php artisan db:seed --class=Database\\Seeders\\UserSeeder
```

Catatan:

- Pastikan model `App\\Models\\User` menggunakan trait `juniyasyos\\ShieldLite\\ShieldLiteRolePermission` agar relasi `roles()` tersedia.
- Paket ini sudah memuat migrasi pivot `shield_role_user` dan kolom `users.default_role_id`.

## Default Resource Permissions

Deklarasikan permission secara eksplisit via `defineGates()` pada setiap Resource/Page/Widget yang ingin dikendalikan.

Contoh umum untuk CRUD:

- `user.index`
- `user.create`
- `user.update`
- `user.delete`

Anda bebas menentukan skema permission sendiri, selama konsisten digunakan saat pengecekan akses (`hexa()->can(...)`).

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
