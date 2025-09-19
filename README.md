# Shield Lite - Laravel Authorization Made Easy

🛡️ A lightweight, powerful Laravel authorization package built on top of Spatie Permission with seamless Filament integration.

[![Latest Stable Version](https://poser.pugx.org/juniyasyos/shield-lite/v/stable)](https://packagist.org/packages/juniyasyos/shield-lite)
[![Total Downloads](https://poser.pugx.org/juniyasyos/shield-lite/downloads)](https://packagist.org/packages/juniyasyos/shield-lite)
[![License](https://poser.pugx.org/juniyasyos/shield-lite/license)](https://packagist.org/packages/juniyasyos/shield-lite)

## ✨ Features

- 🔗 **Spatie Permission Compatible** - Built on proven Spatie Laravel Permission
- ⚡ **Zero Configuration** - Works out of the box with sensible defaults
- 🛡️ **Super Admin Support** - Automatic bypass for super administrators
- 🎯 **Filament Ready** - Seamless integration with Filament Resources
- 🔄 **Trait Compatibility** - Full compatibility with existing Spatie Permission traits
- 🚀 **Easy Resource Authorization** - Simple `defineGates()` pattern like Hexa Lite
- 📊 **Flexible Drivers** - Support for database and array drivers

## 📦 Installation

### Step 1: Install Spatie Permission

Shield Lite requires Spatie Permission as the foundation:

```bash
# Install Spatie Permission
composer require spatie/laravel-permission:^6

# Publish and run migrations
php artisan vendor:publish --provider="Spatie\Permission\PermissionServiceProvider"
php artisan migrate

# Clear cache
php artisan optimize:clear
```

### Step 2: Install Shield Lite

```bash
composer require juniyasyos/shield-lite
```

### Step 3: Publish Configuration (Optional)

```bash
php artisan vendor:publish --tag="shield-lite-config"
```

## 🔧 Quick Setup

### 1. Configure User Model

Add Shield Lite traits to your User model:

```php
<?php

namespace App\Models;

use Illuminate\Foundation\Auth\User as Authenticatable;
use Spatie\Permission\Traits\HasRoles;
use juniyasyos\ShieldLite\Concerns\HasShieldRoles;
use juniyasyos\ShieldLite\Concerns\HasShieldPermissions;
use juniyasyos\ShieldLite\Concerns\AuthorizesShield;

class User extends Authenticatable
{
    use HasShieldRoles, HasShieldPermissions, AuthorizesShield;
    use HasRoles {
        // Use Spatie methods as primary, Shield Lite as aliases
        HasRoles::assignRole insteadof HasShieldRoles;
        HasRoles::removeRole insteadof HasShieldRoles;
        HasRoles::hasRole insteadof HasShieldRoles;
        HasRoles::syncRoles insteadof HasShieldRoles;
        HasRoles::getRoleNames insteadof HasShieldRoles;
        
        // Keep Shield Lite specific methods
        HasShieldRoles::isSuperAdmin as isShieldSuperAdmin;
        HasShieldRoles::getDefaultRole as getShieldDefaultRole;
    }

    // Your model code...
}
```

### 2. Configure Filament Resources

Use the `HasShieldLite` trait in your Filament Resources:

```php
<?php

namespace App\Filament\Resources;

use Filament\Resources\Resource;
use juniyasyos\ShieldLite\Concerns\HasShieldLite;

class UserResource extends Resource
{
    use HasShieldLite;

    protected static ?string $model = User::class;

    /**
     * Define permissions for this resource
     */
    public function defineGates(): array
    {
        return [
            'users.viewAny' => __('View users list'),
            'users.create' => __('Create new users'),
            'users.update' => __('Update users'),
            'users.delete' => __('Delete users'),
        ];
    }

    // Your resource code...
}
```

## 📚 Usage Examples

### Basic Role & Permission Management

```php
// Create roles and permissions
$adminRole = \Spatie\Permission\Models\Role::create(['name' => 'admin']);
$permission = \Spatie\Permission\Models\Permission::create(['name' => 'users.viewAny']);

// Assign role to user
$user = User::find(1);
$user->assignRole('admin');

// Check permissions (all of these work)
$user->can('users.viewAny');           // Laravel native
$user->hasRole('admin');               // Spatie method
$user->isShieldSuperAdmin();           // Shield Lite method
```

### Super Admin Setup

```php
// Create super admin role
$superAdminRole = \Spatie\Permission\Models\Role::create(['name' => 'Super-Admin']);

// Assign to user
$user->assignRole('Super-Admin');

// Super admin bypasses ALL permission checks automatically
$user->can('any.permission.here'); // Always true for super admin
```

### Filament Resource Authorization

With `HasShieldLite` trait, your resources automatically get proper authorization:

```php
class PostResource extends Resource
{
    use HasShieldLite;

    public function defineGates(): array
    {
        return [
            'posts.viewAny' => __('View posts'),
            'posts.create' => __('Create posts'), 
            'posts.update' => __('Edit posts'),
            'posts.delete' => __('Delete posts'),
        ];
    }
}
```

The trait automatically provides:
- ✅ `canAccess()` - checks `viewAny` permission
- ✅ `canCreate()` - checks `create` permission  
- ✅ `canEdit($record)` - checks `update` permission
- ✅ `canDelete($record)` - checks `delete` permission
- ✅ Auto permission registration in database

### Manual Permission Checking

```php
// In controllers
if ($user->can('posts.create')) {
    // User can create posts
}

// In Blade templates
@can('posts.update', $post)
    <a href="{{ route('posts.edit', $post) }}">Edit</a>
@endcan

// Using Gates
if (Gate::allows('posts.delete', $post)) {
    // User can delete this post
}
```

## ⚙️ Configuration

### Configuration File

```php
// config/shield-lite.php
return [
    'driver' => env('SHIELD_LITE_DRIVER', 'spatie'), // 'spatie' or 'array'
    'guard' => env('SHIELD_LITE_GUARD', 'web'),
    'super_admin_roles' => ['Super-Admin'],
    'cache_key' => 'shield_lite_permissions',
    'cache_expiration' => 3600, // 1 hour
];
```

### Environment Variables

```env
SHIELD_LITE_DRIVER=spatie
SHIELD_LITE_GUARD=web
```

## 🧪 Testing

### Test Setup

For testing, register gates before running tests:

```php
// In your test
test('user can access dashboard', function () {
    // Register resource gates
    \App\Filament\Resources\UserResource::registerGates();
    
    $user = User::factory()->create();
    $user->assignRole('admin');
    
    $this->actingAs($user)
         ->get('/admin')
         ->assertStatus(200);
});
```

### Test Helpers

```php
// Create test user with permissions
$user = User::factory()->create();
$user->givePermissionTo('users.viewAny');

// Or assign role
$user->assignRole('admin');

// Test permissions
expect($user->can('users.viewAny'))->toBeTrue();
expect($user->hasRole('admin'))->toBeTrue();
```

## 🔄 Migration from Other Packages

### From Laravel Permission

Shield Lite is fully compatible with Laravel Permission:

```php
// Existing Spatie Permission code works as-is
$user->assignRole('admin');
$user->givePermissionTo('edit articles');
$user->can('edit articles');
```

### From Other Authorization Packages

1. **Replace traits** in your User model with Shield Lite traits
2. **Update Resource classes** to use `HasShieldLite` trait
3. **Define permissions** using `defineGates()` method
4. **Test thoroughly** - permissions should work the same

## 🚨 Troubleshooting

### Common Issues

**1. Permission not found error:**
```bash
# Make sure permissions exist
\Spatie\Permission\Models\Permission::create(['name' => 'users.viewAny']);

# Or use resource registration
YourResource::registerGates();
```

**2. Trait conflicts:**
```php
// Use trait precedence to resolve conflicts
use HasRoles {
    HasRoles::assignRole insteadof HasShieldRoles;
}
```

**3. Cache issues:**
```bash
# Clear permission cache
php artisan permission:cache-reset
php artisan optimize:clear
```

### Debug Mode

Enable debug logging in your config:

```php
// config/shield-lite.php
'debug' => env('SHIELD_LITE_DEBUG', false),
```

## 📖 API Reference

### Traits

- **`HasShieldRoles`** - Role management for User models
- **`HasShieldPermissions`** - Permission management for User models  
- **`AuthorizesShield`** - Authorization methods and super admin support
- **`HasShieldLite`** - Resource authorization for Filament Resources

### Methods

**User Model Methods:**
```php
$user->assignRole(...$roles)              // Assign roles
$user->removeRole(...$roles)              // Remove roles
$user->hasRole($role, $guard = null)      // Check role
$user->isSuperAdmin()                     // Check super admin
$user->can($permission)                   // Check permission (Laravel native)
```

**Resource Methods:**
```php
YourResource::canAccess()                 // Check viewAny permission
YourResource::canCreate()                 // Check create permission
YourResource::canEdit($record)            // Check update permission
YourResource::canDelete($record)          // Check delete permission
YourResource::registerGates()             // Register defined permissions
```

## 🤝 Contributing

1. Fork the repository
2. Create your feature branch (`git checkout -b feature/amazing-feature`)
3. Commit your changes (`git commit -m 'Add amazing feature'`)
4. Push to the branch (`git push origin feature/amazing-feature`)
5. Open a Pull Request

## 📄 License

Shield Lite is open-sourced software licensed under the [MIT license](LICENSE.md).

## 💪 Support

- 📧 **Email**: [your-email@domain.com]
- 🐛 **Issues**: [GitHub Issues](https://github.com/juniyasyos/shield-lite/issues)
- 💬 **Discussions**: [GitHub Discussions](https://github.com/juniyasyos/shield-lite/discussions)

---

**Made with ❤️ for the Laravel community**

Add the `HasRoles` trait to your User model:

```php
<?php

namespace App\Models;

use Illuminate\Foundation\Auth\User as Authenticatable;
use Spatie\Permission\Traits\HasRoles;

class User extends Authenticatable 
{
    use HasRoles;
    
    // ... rest of your model
    
    protected $fillable = [
        'name', 'email', 'password',
    ];
}
```

### Step 4: Publish & Configure

```bash
php artisan vendor:publish --tag=shield-lite-config
```

Edit `config/shield-lite.php`:

```php
<?php

return [
    // Guard untuk permissions (harus sama dengan auth guard)
    'guard' => env('SHIELD_LITE_GUARD', 'web'),
    
    // Role super admin yang bypass semua permission
    'super_admin_role' => env('SHIELD_LITE_SUPER_ADMIN_ROLE', 'Super-Admin'),
    
    // Format nama permission: {resource}.{action}
    'ability_format' => '{resource}.{action}',
    
    // Resource dan action yang akan di-seed otomatis
    'resources' => [
        'users'  => ['viewAny','view','create','update','delete','restore','forceDelete'],
        'roles'  => ['viewAny','view','create','update','delete'],
        'posts'  => ['viewAny','view','create','update','delete'],
        'categories' => ['viewAny','view','create','update','delete'],
    ],
    
    // Support untuk Spatie teams (opsional)
    'teams' => false,
];
```

### Step 5: Seed Permissions & Roles

Run the seeder to create permissions and roles:

```bash
php artisan db:seed --class="juniyasyos\ShieldLite\Database\Seeders\ShieldLiteSeeder"
```

### Step 6: Reset Permission Cache

```bash
php artisan permission:cache-reset
```

## 🧠 Core Concepts

### Automatic Permission Mapping

Shield Lite automatically maps Laravel policy methods to Spatie permissions using a predictable pattern:

| Laravel Method | Model | Generated Permission |
|---------------|-------|---------------------|
| `$user->can('viewAny', Post::class)` | Post | `posts.viewAny` |
| `$user->can('view', $post)` | Post instance | `posts.view` |
| `$user->can('create', Post::class)` | Post | `posts.create` |
| `$user->can('update', $post)` | Post instance | `posts.update` |
| `$user->can('delete', $post)` | Post instance | `posts.delete` |

### Gate::before Integration

Shield Lite registers a `Gate::before` callback that:

1. **Super Admin Bypass**: Users with configured super admin role bypass ALL checks
2. **Automatic Mapping**: Maps policy calls to Spatie permissions automatically
3. **Fallback**: Allows other policies to run if no match found

```php
// In ShieldLiteServiceProvider
Gate::before(function ($user, string $ability, ?array $arguments = null) {
    // 1) Super-admin bypass
    $role = config('shield-lite.super_admin_role', 'Super-Admin');
    if (method_exists($user, 'hasRole') && $user->hasRole($role)) {
        return true; // Allow everything
    }

    // 2) Automatic resource-action mapping
    if (!empty($arguments) && isset($arguments[0])) {
        $resource = ResourceName::fromModel($arguments[0]);
        $permission = Ability::format($ability, $resource);
        if ($user->hasPermissionTo($permission, config('shield-lite.guard'))) {
            return true;
        }
    }

    return null; // Let other policies handle it
});
```

## 🛠️ Helper Classes

### ResourceName Helper

Converts model classes to resource names for permissions:

```php
use juniyasyos\ShieldLite\Support\ResourceName;

// Examples:
ResourceName::fromModel(User::class);           // → 'users'
ResourceName::fromModel(Post::class);           // → 'posts'  
ResourceName::fromModel(PostCategory::class);   // → 'post_categories'
ResourceName::fromModel(new Product());         // → 'products'

// Custom implementation:
class CustomResourceName extends ResourceName 
{
    public static function fromModel($modelOrClass): string
    {
        $class = is_string($modelOrClass) ? $modelOrClass : get_class($modelOrClass);
        
        // Custom mapping
        return match($class) {
            'App\\Models\\User' => 'members',
            'App\\Models\\Post' => 'articles',
            default => parent::fromModel($modelOrClass)
        };
    }
}
```

### Ability Helper

Formats actions and resources into permission names:

```php
use juniyasyos\ShieldLite\Support\Ability;

// Examples with default format '{resource}.{action}':
Ability::format('view', 'posts');      // → 'posts.view'
Ability::format('update', 'users');    // → 'users.update'
Ability::format('delete', 'comments'); // → 'comments.delete'

// With custom format '{action}:{resource}':
config(['shield-lite.ability_format' => '{action}:{resource}']);
Ability::format('view', 'posts');      // → 'view:posts'

// Custom implementation:
class CustomAbility extends Ability 
{
    public static function format(string $action, string $resource): string
    {
        // Custom formatting logic
        return "permission_{$action}_on_{$resource}";
        // Results in: permission_view_on_posts
    }
}
```

## 🎭 Traits & Integration

### HasRoles Trait (Spatie)

Your User model **must** use Spatie's `HasRoles` trait:

```php
<?php

namespace App\Models;

use Illuminate\Foundation\Auth\User as Authenticatable;
use Spatie\Permission\Traits\HasRoles;

class User extends Authenticatable 
{
    use HasRoles;
    
    // Now you have access to:
    // $user->assignRole('role-name')
    // $user->hasRole('role-name')
    // $user->givePermissionTo('permission-name')
    // $user->hasPermissionTo('permission-name')
    // $user->can('permission-name')
}
```

### Available Methods

Once you add `HasRoles`, these methods become available:

```php
// Role management
$user->assignRole('Editor');
$user->assignRole(['Editor', 'Author']);
$user->removeRole('Editor');
$user->hasRole('Editor');                    // → boolean
$user->hasAnyRole(['Editor', 'Admin']);      // → boolean
$user->hasAllRoles(['Editor', 'Author']);    // → boolean

// Permission management  
$user->givePermissionTo('posts.edit');
$user->givePermissionTo(['posts.edit', 'posts.delete']);
$user->revokePermissionTo('posts.edit');
$user->hasPermissionTo('posts.edit');        // → boolean
$user->can('posts.edit');                    // → boolean (Laravel native)

// Get collections
$user->getRoleNames();                       // → Collection of role names
$user->getPermissionNames();                 // → Collection of permission names
$user->getAllPermissions();                  // → Collection of Permission models
$user->getPermissionsViaRoles();            // → Permissions through roles
$user->getDirectPermissions();               // → Direct permissions only
```

## 💡 Usage Examples

### Basic Permission Checks

```php
// In Controllers
class PostController extends Controller
{
    public function index()
    {
        $this->authorize('viewAny', Post::class); // Checks 'posts.viewAny'
        return view('posts.index');
    }
    
    public function show(Post $post)
    {
        $this->authorize('view', $post); // Checks 'posts.view'
        return view('posts.show', compact('post'));
    }
    
    public function edit(Post $post)
    {
        $this->authorize('update', $post); // Checks 'posts.update'
        return view('posts.edit', compact('post'));
    }
}
```

### Blade Templates

```blade
{{-- Check permissions in Blade --}}
@can('viewAny', App\Models\Post::class)
    <a href="{{ route('posts.index') }}">View All Posts</a>
@endcan

@can('create', App\Models\Post::class)
    <a href="{{ route('posts.create') }}">Create Post</a>
@endcan

@can('update', $post)
    <a href="{{ route('posts.edit', $post) }}">Edit</a>
@endcan

@can('delete', $post)
    <form method="POST" action="{{ route('posts.destroy', $post) }}">
        @csrf @method('DELETE')
        <button type="submit">Delete</button>
    </form>
@endcan

{{-- Super admin check --}}
@if(auth()->user()->hasRole('Super-Admin'))
    <div class="admin-panel">
        <!-- Super admin controls -->
    </div>
@endif
```

### Middleware

```php
// In routes/web.php
Route::middleware(['auth'])->group(function () {
    // Only users with 'posts.viewAny' permission
    Route::get('/posts', [PostController::class, 'index'])
         ->middleware('can:viewAny,App\Models\Post');
    
    // Only users with 'posts.create' permission
    Route::get('/posts/create', [PostController::class, 'create'])
         ->middleware('can:create,App\Models\Post');
         
    // Only users with 'posts.update' permission on specific post
    Route::get('/posts/{post}/edit', [PostController::class, 'edit'])
         ->middleware('can:update,post');
});
```

### Service Classes

```php
<?php

namespace App\Services;

use App\Models\Post;
use Illuminate\Support\Facades\Gate;

class PostService
{
    public function getUserPosts($user)
    {
        $query = Post::query();
        
        // Users can only see their own posts unless they have viewAny permission
        if (!Gate::forUser($user)->allows('viewAny', Post::class)) {
            $query->where('user_id', $user->id);
        }
        
        return $query->get();
    }
    
    public function canUserEditPost($user, Post $post)
    {
        return Gate::forUser($user)->allows('update', $post);
    }
}
```

## 🔧 Advanced Configuration

### Multiple Guards

```php
// config/shield-lite.php
'guard' => 'admin', // Use 'admin' guard instead of 'web'

// Create permissions for specific guard
Permission::create(['name' => 'posts.edit', 'guard_name' => 'admin']);

// Check permissions with specific guard
$user->hasPermissionTo('posts.edit', 'admin');
```

### Teams Support

Enable Spatie's teams feature:

```php
// 1. In config/permission.php (Spatie config)
'teams' => true,

// 2. In config/shield-lite.php  
'teams' => true,

// 3. Run migration
php artisan migrate

// 4. Usage with teams
$user->assignRole('Manager', $team); // Role within specific team
$user->hasRole('Manager', $team);    // Check role within team
```

### Custom Permission Formats

```php
// config/shield-lite.php
'ability_format' => '{action}:{resource}',
// Results in: 'edit:posts', 'delete:users', etc.

'ability_format' => '{resource}/{action}',
// Results in: 'posts/edit', 'users/delete', etc.

'ability_format' => 'can_{action}_{resource}',
// Results in: 'can_edit_posts', 'can_delete_users', etc.
```

### Environment Variables

```bash
# .env file
SHIELD_LITE_GUARD=web
SHIELD_LITE_SUPER_ADMIN_ROLE=Super-Admin

# For different environments:
# Staging
SHIELD_LITE_SUPER_ADMIN_ROLE=Developer

# Production  
SHIELD_LITE_SUPER_ADMIN_ROLE=Administrator
```

## 🎨 Filament Integration

### Resource Policies

Shield Lite works seamlessly with Filament resources:

```php
<?php

namespace App\Filament\Resources;

use Filament\Resources\Resource;
use App\Models\Post;
use juniyasyos\ShieldLite\Policies\GenericPolicy;

class PostResource extends Resource
{
    protected static ?string $model = Post::class;
    
    // Optional: Register generic policy
    public static function getPolicy(): string
    {
        return GenericPolicy::class;
    }
    
    // Filament automatically checks these permissions:
    // - posts.viewAny (for index page)
    // - posts.view (for view page)  
    // - posts.create (for create page)
    // - posts.update (for edit page)
    // - posts.delete (for delete action)
}
```

### Page Policies

```php
<?php

namespace App\Filament\Pages;

use Filament\Pages\Page;

class Analytics extends Page
{
    protected static ?string $navigationIcon = 'heroicon-o-chart-bar';
    
    // Check permission for page access
    public static function canAccess(): bool
    {
        return auth()->user()->can('analytics.view');
    }
}
```

### Widget Policies

```php
<?php

namespace App\Filament\Widgets;

use Filament\Widgets\Widget;

class StatsOverview extends Widget
{
    // Check permission for widget visibility
    public static function canView(): bool
    {
        return auth()->user()->can('widgets.stats');
    }
}
```

## 🧪 Testing

### Basic Test Setup

```php
<?php

use Illuminate\Foundation\Testing\RefreshDatabase;
use Spatie\Permission\Models\Role;
use Spatie\Permission\Models\Permission;

class PermissionTest extends TestCase
{
    use RefreshDatabase;
    
    protected function setUp(): void
    {
        parent::setUp();
        
        // Reset cached roles and permissions
        app()[\Spatie\Permission\PermissionRegistrar::class]->forgetCachedPermissions();
    }
    
    public function test_user_can_view_posts_with_permission()
    {
        // Create permission
        Permission::create(['name' => 'posts.view', 'guard_name' => 'web']);
        
        // Create role and assign permission
        $role = Role::create(['name' => 'Viewer', 'guard_name' => 'web']);
        $role->givePermissionTo('posts.view');
        
        // Create user and assign role
        $user = User::factory()->create();
        $user->assignRole('Viewer');
        
        // Test authorization
        $post = Post::factory()->create();
        $this->assertTrue($user->can('view', $post));
    }
    
    public function test_super_admin_bypasses_all_permissions()
    {
        // Create super admin role
        Role::create(['name' => 'Super-Admin', 'guard_name' => 'web']);
        
        // Create user and assign super admin role
        $user = User::factory()->create();
        $user->assignRole('Super-Admin');
        
        // Test that super admin can do anything
        $post = Post::factory()->create();
        $this->assertTrue($user->can('view', $post));
        $this->assertTrue($user->can('update', $post));
        $this->assertTrue($user->can('delete', $post));
    }
}
```

### Feature Tests

```php
<?php

class PostControllerTest extends TestCase
{
    use RefreshDatabase;
    
    public function test_authorized_user_can_create_post()
    {
        // Setup permission
        Permission::create(['name' => 'posts.create', 'guard_name' => 'web']);
        $role = Role::create(['name' => 'Author', 'guard_name' => 'web']);
        $role->givePermissionTo('posts.create');
        
        // Create and authenticate user
        $user = User::factory()->create();
        $user->assignRole('Author');
        $this->actingAs($user);
        
        // Test authorized access
        $response = $this->get('/posts/create');
        $response->assertStatus(200);
        
        // Test post creation
        $response = $this->post('/posts', [
            'title' => 'Test Post',
            'content' => 'Test content'
        ]);
        $response->assertRedirect();
        $this->assertDatabaseHas('posts', ['title' => 'Test Post']);
    }
    
    public function test_unauthorized_user_cannot_create_post()
    {
        // Create user without permission
        $user = User::factory()->create();
        $this->actingAs($user);
        
        // Test unauthorized access
        $response = $this->get('/posts/create');
        $response->assertStatus(403);
    }
}
```

## 🔍 Debugging & Troubleshooting

### Check User Permissions

```php
// Debug user permissions
$user = auth()->user();

// All permissions (direct + via roles)
dd($user->getAllPermissions()->pluck('name'));

// Direct permissions only
dd($user->getDirectPermissions()->pluck('name'));

// Permissions via roles
dd($user->getPermissionsViaRoles()->pluck('name'));

// All roles
dd($user->getRoleNames());

// Check specific permission
dd($user->hasPermissionTo('posts.edit'));

// Check with specific guard
dd($user->hasPermissionTo('posts.edit', 'web'));
```

### Debug Permission Cache

```php
// Clear permission cache
php artisan permission:cache-reset

// Check if cache is working
dd(app(\Spatie\Permission\PermissionRegistrar::class)->getCacheKey());

// Disable cache temporarily (config/permission.php)
'cache' => [
    'expiration_time' => 0, // Disable cache
]
```

### Common Issues

1. **Permission not working**: Clear cache with `php artisan permission:cache-reset`
2. **Guard mismatch**: Ensure same guard in config and permission creation
3. **Model not using HasRoles**: Add `use HasRoles;` to User model
4. **Permission not found**: Create permission first or run seeder

## 📚 Additional Resources

- [Spatie Permission Documentation](https://spatie.be/docs/laravel-permission/)
- [Laravel Authorization Documentation](https://laravel.com/docs/authorization)
- [Filament Documentation](https://filamentphp.com/docs)

## 🔄 Migration from Shield Lite v3

See [UPGRADE.md](UPGRADE.md) for detailed migration instructions from Shield Lite v3 to v4.

## 📋 Requirements

- PHP 8.2+
- Laravel 12.0+
- Spatie Laravel Permission 6.0+

## 📄 License

Shield Lite is open-sourced software licensed under the [MIT license](LICENSE.md).

## 🤝 Contributing

Please see [CONTRIBUTING.md](CONTRIBUTING.md) for details.

## 💬 Support

- [Documentation](https://github.com/juniyasyos/shield-lite)
- [Issues](https://github.com/juniyasyos/shield-lite/issues)
- [Discussions](https://github.com/juniyasyos/shield-lite/discussions)
