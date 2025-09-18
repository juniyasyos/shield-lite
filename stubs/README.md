# User Resource Stub

This directory contains example files for implementing a User Resource in your Filament panel. Starting from v2.0, Shield Lite no longer automatically registers a User Resource to avoid tight coupling and give you full control over your user management interface.

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
