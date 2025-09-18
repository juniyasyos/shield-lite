# Shield Lite v4 Stubs

This directory contains example files for implementing Resources in your Filament panel with Shield Lite v4's Spatie-based authorization.

## Prerequisites

Before using these stubs, ensure you have:

1. **Installed Spatie Permission** in your host application:
   ```bash
   composer require spatie/laravel-permission:^6
   php artisan vendor:publish --provider="Spatie\Permission\PermissionServiceProvider"
   php artisan migrate
   ```

2. **Added HasRoles trait** to your User model:
   ```php
   use Spatie\Permission\Traits\HasRoles;
   
   class User extends Authenticatable {
       use HasRoles;
   }
   ```

3. **Published and configured** Shield Lite:
   ```bash
   php artisan vendor:publish --tag=shield-lite-config
   ```

## How to Use

1. **Copy the files** to your application's Filament Resources directory:
   ```bash
   cp -r vendor/juniyasyos/shield-lite/stubs/UserResource app/Filament/Resources/Users
   ```

2. **Update the namespace** in each file to match your application structure:
   ```php
   namespace App\Filament\Resources\Users;
   ```

3. **Register the resource** in your Panel provider:
   ```php
   use App\Filament\Resources\Users\UserResource;
   
   public function panel(Panel $panel): Panel
   {
       return $panel
           ->resources([
               UserResource::class,
               // ... other resources
           ]);
   }
   ```

## Permission Structure

The stubs use Shield Lite v4's automatic permission mapping:

- `users.viewAny` - View user list
- `users.view` - View individual user
- `users.create` - Create new user  
- `users.update` - Edit existing user
- `users.delete` - Delete user
- `users.restore` - Restore soft-deleted user
- `users.forceDelete` - Permanently delete user

These permissions are automatically checked via Gate::before logic and Spatie Permission integration.

## Seeding Permissions

Run the Shield Lite seeder to create permissions:

```bash
php artisan db:seed --class="juniyasyos\ShieldLite\Database\Seeders\ShieldLiteSeeder"
```

Or configure resources in `config/shield-lite.php` and use the seeder.

4. **Customize as needed** - these are just example files. You can:
   - Add/remove form fields
   - Modify table columns
   - Change permission checks
   - Add custom actions
   - Implement your own authorization logic

## Integration with Shield Lite v2.0

The example UserResource includes integration with Shield Lite's new trait-based approach:

- Uses `HasShieldPermissions` trait in your User model
- Leverages automatic policy resolution
- Works with both Spatie Permission and array-based permission drivers

## Migration from v1.x

If you were using Shield Lite v1.x, your existing User Resource will continue to work, but you should:

1. Move your User Resource to your application directory
2. Add the new traits to your User model
3. Update to use the new permission system

See UPGRADE.md for detailed migration instructions.
