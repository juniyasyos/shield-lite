# Upgrade Guide: Shield Lite v3 → v4

Shield Lite v4 introduces a completely new architecture based on Spatie Permission. This guide will help you migrate from v3 to v4.

## 🚨 Breaking Changes

### Major Architectural Changes

- **Spatie Permission Required**: v4 is built on top of Spatie Laravel Permission
- **No More Array Driver**: The array-based permission system has been removed
- **Different Configuration**: New config structure focused on Spatie integration
- **Different Traits**: User model now uses Spatie's `HasRoles` instead of Shield Lite traits

## 📋 Migration Steps

### 1. Backup Your Data

Before upgrading, backup your existing permission data:

```sql
-- Export existing Shield Lite data
SELECT * FROM shield_roles;
SELECT * FROM shield_role_permissions; 
SELECT * FROM model_has_permissions;
-- etc.
```

### 2. Install Spatie Permission

```bash
composer require spatie/laravel-permission:^6
php artisan vendor:publish --provider="Spatie\Permission\PermissionServiceProvider"
php artisan migrate
```

### 3. Update Composer Dependencies

```bash
composer require juniyasyos/shield-lite:^4.0
composer remove spatie/laravel-permission # if you had old version
composer require spatie/laravel-permission:^6
```

### Step 2: Move UserResource to Your App

If you were using the plugin's UserResource, you need to create your own:

1. **Create the Resource File**: `app/Filament/Resources/UserResource.php`

```php
<?php

namespace App\Filament\Resources;

use App\Filament\Resources\UserResource\Pages;
use App\Models\User;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;

class UserResource extends Resource
{
    protected static ?string $model = User::class;

    protected static ?string $navigationIcon = 'heroicon-o-users';

    protected static ?string $navigationGroup = 'User Management';

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\TextInput::make('name')
                    ->required()
                    ->maxLength(255),
                Forms\Components\TextInput::make('email')
                    ->email()
                    ->required()
                    ->maxLength(255),
                Forms\Components\DateTimePicker::make('email_verified_at'),
                Forms\Components\TextInput::make('password')
                    ->password()
                    ->dehydrated(fn ($state) => filled($state))
                    ->required(fn (string $context): bool => $context === 'create')
                    ->maxLength(255),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('name')
                    ->searchable(),
                Tables\Columns\TextColumn::make('email')
                    ->searchable(),
                Tables\Columns\TextColumn::make('email_verified_at')
                    ->dateTime()
                    ->sortable(),
                Tables\Columns\TextColumn::make('created_at')
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
                Tables\Columns\TextColumn::make('updated_at')
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->filters([
                //
            ])
            ->actions([
                Tables\Actions\EditAction::make(),
                Tables\Actions\DeleteAction::make(),
            ])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    Tables\Actions\DeleteBulkAction::make(),
                ]),
            ]);
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListUsers::route('/'),
            'create' => Pages\CreateUser::route('/create'),
            'edit' => Pages\EditUser::route('/{record}/edit'),
        ];
    }
}
```

2. **Create the Page Files**:

`app/Filament/Resources/UserResource/Pages/ListUsers.php`:
```php
<?php

namespace App\Filament\Resources\UserResource\Pages;

use App\Filament\Resources\UserResource;
use Filament\Actions;
use Filament\Resources\Pages\ListRecords;

class ListUsers extends ListRecords
{
    protected static string $resource = UserResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\CreateAction::make(),
        ];
    }
}
```

`app/Filament/Resources/UserResource/Pages/CreateUser.php`:
```php
<?php

namespace App\Filament\Resources\UserResource\Pages;

use App\Filament\Resources\UserResource;
use Filament\Resources\Pages\CreateRecord;

class CreateUser extends CreateRecord
{
    protected static string $resource = UserResource::class;
}
```

`app/Filament/Resources/UserResource/Pages/EditUser.php`:
```php
<?php

namespace App\Filament\Resources\UserResource\Pages;

use App\Filament\Resources\UserResource;
use Filament\Actions;
use Filament\Resources\Pages\EditRecord;

class EditUser extends EditRecord
{
    protected static string $resource = UserResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\DeleteAction::make(),
        ];
    }
}
```

### Step 3: Update Your User Model

Add the Shield Lite traits to your User model:

```php
<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use juniyasyos\ShieldLite\Concerns\HasShieldRoles;
use juniyasyos\ShieldLite\Concerns\HasShieldPermissions;
use juniyasyos\ShieldLite\Concerns\AuthorizesShield;

class User extends Authenticatable
{
    use HasFactory, Notifiable;
    use HasShieldRoles, HasShieldPermissions, AuthorizesShield;

    // Your existing model code...
}
```

### Step 4: Publish and Update Configuration

1. **Publish the new configuration**:
```bash
php artisan vendor:publish --tag=shield-lite-config --force
```

2. **Update your configuration** in `config/shield-lite.php`:

```php
return [
    // Choose your permission driver
    'driver' => 'spatie', // or 'array'
    
    // Configure ability format
    'ability_format' => '{resource}.{action}',
    
    // Enable automatic policy resolution
    'auto_resolve_policies' => true,
    
    // Configure super admin
    'super_admin' => [
        'role' => 'super_admin',
    ],
    
    // Other configuration...
];
```

### Step 5: Choose Your Permission Backend

#### Option A: Using Spatie Permission (Recommended)

1. **Install Spatie Permission**:
```bash
composer require spatie/laravel-permission
```

2. **Publish and run migrations**:
```bash
php artisan vendor:publish --provider="Spatie\Permission\PermissionServiceProvider"
php artisan migrate
```

3. **Update your config**:
```php
'driver' => 'spatie',
```

#### Option B: Using Array-based Permissions

1. **Update your config**:
```php
'driver' => 'array',

'permissions' => [
    'roles' => [
        'super_admin' => ['*'],
        'admin' => [
            'users.*',
            'posts.*',
        ],
        'user' => [
            'users.view',
            'users.update',
        ],
    ],
],
```

### Step 6: Update Your Code

#### Before (Old Way)
```php
// Permission checking was handled by the plugin's UserResource
// You might have had custom permission logic in various places
```

#### After (New Way)
```php
// Use the trait methods
if ($user->can('users.view')) {
    // User can view users
}

if ($user->hasRole('admin')) {
    // User has admin role
}

// Automatic policy resolution works with Gate::authorize
Gate::authorize('view', $user);
Gate::authorize('update', $user);
```

### Step 7: Update Filament Resources

Your existing Filament resources will automatically work with the new policy system:

```php
class PostResource extends Resource
{
    protected static ?string $model = Post::class;
    
    // These methods automatically check permissions:
    // viewAny() -> posts.view_any
    // view() -> posts.view
    // create() -> posts.create
    // update() -> posts.update
    // delete() -> posts.delete
}
```

### Step 8: Test Your Application

1. **Run your tests**:
```bash
php artisan test
```

2. **Test permissions manually**:
   - Try accessing Filament resources with different user roles
   - Verify that permissions are being checked correctly
   - Test the authorization system with your existing workflows

### Breaking Changes

#### Configuration File Structure
- **Old**: `config/shield.php` with simple structure
- **New**: `config/shield-lite.php` with expanded driver, abilities, and policy configuration

#### UserResource Location
- **Old**: Provided by the plugin at `vendor/juniyasyos/shield-lite/src/Resources/Users/`
- **New**: Must be created in your app at `app/Filament/Resources/`

#### Permission Checking
- **Old**: Limited to Spatie Permission package integration
- **New**: Supports both Spatie Permission and array-based permissions

#### Policy System
- **Old**: Manual policy creation required
- **New**: Automatic policy resolution with GenericPolicy and magic methods

### Common Migration Issues

#### Issue 1: Missing UserResource
**Error**: UserResource not found after upgrade

**Solution**: Create your own UserResource in `app/Filament/Resources/` as shown in Step 2.

#### Issue 2: Configuration Errors
**Error**: Config key not found errors

**Solution**: Re-publish the configuration file:
```bash
php artisan vendor:publish --tag=shield-lite-config --force
```

#### Issue 3: Permission Checks Failing
**Error**: Permissions not working as expected

**Solution**: 
1. Ensure traits are added to your User model
2. Verify your permission driver configuration
3. Check that abilities are formatted correctly

#### Issue 4: Policy Authorization Issues
**Error**: Gate authorization not working

**Solution**: 
1. Ensure `auto_resolve_policies` is set to `true`
2. Clear your application cache: `php artisan cache:clear`
3. Verify your models are being auto-discovered

### Performance Considerations

The new architecture includes several performance improvements:

1. **Caching**: Permission results can be cached
2. **Lazy Loading**: Policies are resolved only when needed
3. **Efficient Queries**: Better integration with Eloquent relationships

Enable caching in your config:
```php
'cache' => [
    'enabled' => true,
    'ttl' => 3600,
],
```

### Getting Help

If you encounter issues during migration:

1. Check this upgrade guide thoroughly
2. Review the updated README.md for usage examples
3. Check the configuration file for all available options
4. Open an issue on GitHub with details about your specific problem

### Post-Migration Checklist

- [ ] User model has Shield Lite traits
- [ ] UserResource created in your app directory
- [ ] Configuration published and updated
- [ ] Permission driver chosen and configured
- [ ] Tests are passing
- [ ] Filament resources work correctly
- [ ] Permission checks work as expected
- [ ] Authorization system functions properly

Congratulations! You have successfully migrated to the new trait-based Shield Lite architecture.
