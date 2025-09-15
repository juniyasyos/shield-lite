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
- [Publish Command](#publish-command)
- [Permission Generator](#permission-generator)
- [Seeder: Super Admin](#seeder-super-admin)
- [Seeder: Users + Roles](#seeder-users--roles)
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


## Quick Usage (V4)

- Plugin otomatis mendaftarkan Resources: Roles dan Users.
- Users Resource sudah memiliki aksi “Set Roles” (row & bulk) untuk mengatur role dan default role per user.
- Jika Anda sudah punya Users Resource sendiri, sembunyikan resource Anda (mis. `protected static bool $shouldRegisterNavigation = false;`) agar menu tidak dobel.
- Paket ini memuat migrasi termasuk kolom `users.default_role_id` untuk menyimpan default role.
- Seeder:
  - Super Admin (semua permission): `php artisan db:seed --class=Database\\Seeders\\ShieldSuperAdminSeeder`
  - Admin contoh (khusus permission User): lihat contoh di bagian Seeder di bawah.
  - Users + Roles contoh: `php artisan db:seed --class=Database\\Seeders\\UserSeeder`


## Built-in Resources

Shield Lite otomatis menyediakan Resources berikut:

- Roles: pengelolaan peran dan daftar permission.
- Users: pengelolaan akun pengguna lengkap dengan aksi “Set Roles” (row & bulk) untuk menetapkan role dan default role.

Jika Anda sudah memiliki Users Resource sendiri, sembunyikan atau nonaktifkan navigasinya agar tidak dobel di menu.


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

Catatan: paket ini otomatis memuat migrasi (termasuk kolom `users.default_role_id`). Publish hanya diperlukan jika Anda ingin mengubah migrasi bawaan.

Key configuration in `config/shield.php`:

- `navigation.label` and `navigation.role_group`: Customize the plugin menu label & group.
- `custom_permissions`: Define custom permission keys shown under the “Custom” tab.
- `superuser_if_no_role` (bool): When true, users without roles have full access.
- `cache.enabled` (bool), `cache.ttl` (int), `cache.store` (string|null): Cache gate discovery and UI groupings per panel for performance.
- `superadmin.name`, `superadmin.guard`: Defaults used by the example Super Admin seeder.

## Publish Command

Publish everything for Shield Lite (config, migrations, seeders, views, translations) and rebuild Filament assets in one go:

```bash
php artisan shield:publish
```

Options:

- `--force` Overwrite any existing files.
- `--resources` Publish minimal Resource stubs (that extend the package Resources) into `app/Filament/Resources` and disable package resources in `config/shield.php` to avoid conflicts. This avoids code duplication; you only override what you need.
  - Perintah ini juga memetakan config `shield.resources.roles/users` agar Panel mendaftarkan Resource milik App, bukan Resource paket.

## Permission Generator

Generate permission otomatis dari Resources, Pages, dan Widgets yang terdaftar di setiap Filament Panel.

- Command: `php artisan shield:generate`
- Fungsi: Memindai semua komponen yang memiliki `defineGates()`/`roleName()` lalu mengumpulkan daftar permission dan ringkasannya per panel (mirip Filament Shield).

Opsi:

- `--dump=path`: Simpan output ke file JSON.
  - Jika path relatif, file akan disimpan ke `storage/app/shield/{path}`.
- `--super-admin`: Buat/Update role “Super Admin” dengan semua permission yang ditemukan.
  - Nama dan guard dapat diubah via `config/shield.php` (`superadmin.name`, `superadmin.guard`).

Contoh:

```bash
# Lihat daftar permission yang terdeteksi (preview di terminal)
php artisan shield:generate

# Simpan hasil lengkap ke file JSON
php artisan shield:generate --dump=permissions.json

# Sinkronkan role Super Admin dengan semua permission
php artisan shield:generate --super-admin
```

Cara Kerja Singkat:

- Untuk Resource, trait `HasShieldLite` otomatis mengisi default permission CRUD:
  - `*.view`, `*.create`, `*.update`, `*.delete` (label mengikuti `getModelLabel`).
- Anda dapat menimpa/menambah permission custom pada masing-masing Resource/Page/Widget melalui `defineGates()`.
- Permission custom global juga bisa ditambahkan melalui `config('shield.custom_permissions')` dan akan ikut terdeteksi.
- Mendukung multi-panel; output dikelompokkan per panel dan disatukan ke daftar `all_gates`.

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
