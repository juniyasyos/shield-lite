# Shield Lite - Laravel Authorization Made Simple

🛡️ Super simple Laravel authorization plugin for Filament with **zero configuration**. Install in 3 commands, use with 1 trait!

[![Latest Stable Version](https://poser.pugx.org/juniyasyos/shield-lite/v/stable)](https://packagist.org/packages/juniyasyos/shield-lite)
[![License](https://poser.pugx.org/juniyasyos/shield-lite/license)](https://packagist.org/packages/juniyasyos/shield-lite)

## ✨ Why Shield Lite?

- 🚀 **3-Command Install** - From zero to working authorization in 2 minutes
- 🎯 **1 Trait per Model/Resource** - No complex trait inheritance
- 🛡️ **Auto Super Admin** - `Super-Admin` role bypasses everything automatically
- 📦 **Auto Permissions** - Generated from your `defineGates()` method
- 🔄 **Spatie Compatible** - Built on proven Spatie Permission (100% compatible)
- ⚡ **Zero Config** - Works out of the box, customize if needed

## 🚀 Ultra-Quick Install

### Step 1: Install Packages
```bash
composer require spatie/laravel-permission juniyasyos/shield-lite
```

### Step 2: Auto-Setup Everything
```bash
php artisan shield-lite:install
```

### Step 3: Create Admin User
```bash
php artisan shield-lite:user
```

**That's it! 🎉 Ready to use in 3 commands!**

---

## 📖 Usage Guide

### 1. User Model (Auto-configured during install)

```php
<?php

namespace App\Models;

use Illuminate\Foundation\Auth\User as Authenticatable;
use juniyasyos\ShieldLite\Concerns\HasShield;

class User extends Authenticatable
{
    use HasShield; // Single trait with all power!
    
    // Your existing model code stays unchanged...
}
```

### 2. Filament Resources

Simply add the trait and define your permissions:

```php
<?php

namespace App\Filament\Resources;

use Filament\Resources\Resource;
use juniyasyos\ShieldLite\Concerns\HasShieldLite;

class PostResource extends Resource
{
    use HasShieldLite; // Single trait for authorization!

    protected static ?string $model = Post::class;

    /**
     * Define permissions for this resource
     * Permissions are auto-created in database!
     */
    public function defineGates(): array
    {
        return [
            'posts.viewAny' => 'View posts list',
            'posts.create' => 'Create new posts',
            'posts.update' => 'Edit posts', 
            'posts.delete' => 'Delete posts',
        ];
    }

    // Your existing resource code...
}
```

**That's it! Permissions are auto-created when resource loads.**

---

## 🔑 Permission Checking

### In Controllers
```php
class PostController extends Controller
{
    public function index()
    {
        // Check permission
        if (auth()->user()->can('posts.viewAny')) {
            return view('posts.index');
        }
        
        abort(403);
    }
    
    public function create()
    {
        $this->authorize('posts.create'); // Laravel way
        return view('posts.create');
    }
}
```

### In Blade Templates
```blade
@can('posts.create')
    <a href="{{ route('posts.create') }}" class="btn btn-primary">
        Create Post
    </a>
@endcan

@can('posts.update', $post)
    <a href="{{ route('posts.edit', $post) }}">Edit</a>
@endcan

@cannot('posts.delete', $post)
    <span class="text-muted">Cannot delete</span>
@endcannot
```

### Role Management
```php
// Assign roles
$user = User::find(1);
$user->assignRole('admin');
$user->assignRole(['editor', 'moderator']);

// Check roles
if ($user->hasRole('admin')) {
    // User is admin
}

if ($user->hasAnyRole(['admin', 'editor'])) {
    // User has at least one of these roles
}

// Get user roles
$roles = $user->getRoleNames(); // Collection
$rolesArray = $user->getRoleNamesArray(); // Array
```

---

## 👑 Super Admin Magic

Users with `Super-Admin` role automatically **bypass ALL permission checks**:

```php
// Create super admin
$user = User::find(1);
$user->assignRole('Super-Admin');

// Now this user can do ANYTHING
$user->can('posts.create');        // ✅ true
$user->can('users.delete');        // ✅ true  
$user->can('any.permission');      // ✅ true
$user->can('nonexistent.perm');    // ✅ true

// Check if user is super admin
if ($user->isSuperAdmin()) {
    // This user has god mode!
}
```

**Perfect for application administrators who need access to everything.**

---

## 📋 Available Commands

### Installation & Setup
```bash
# Install everything automatically
php artisan shield-lite:install

# Force reinstall (overwrites existing files)
php artisan shield-lite:install --force
```

### User Management
```bash
# Create admin user interactively
php artisan shield-lite:user

# Create admin with specific email/password
php artisan shield-lite:user --email=admin@company.com --password=secret123
```

### Role Management
```bash
# Create new role
php artisan shield-lite:role manager

# Create role with description
php artisan shield-lite:role "Content Editor" --description="Can manage content"
```

---

## ⚙️ Configuration (Optional)

Shield Lite works with zero configuration, but you can customize:

```php
// config/shield-lite.php (auto-created during install)
return [
    'driver' => 'spatie',                    // Always spatie
    'guard' => 'web',                        // Default guard
    'super_admin_roles' => ['Super-Admin'],  // Roles with god mode
    'auto_register' => true,                 // Auto-create permissions
];
```

### Environment Variables
```env
SHIELD_LITE_DRIVER=spatie
SHIELD_LITE_GUARD=web
```

---

## 🧪 Testing

### Basic Test Setup
```php
<?php

use App\Models\User;
use App\Filament\Resources\PostResource;

test('user can access posts with permission', function () {
    // Ensure permissions exist (auto-registered by trait)
    PostResource::registerGates();
    
    // Create user with permission
    $user = User::factory()->create();
    $user->givePermissionTo('posts.viewAny');
    
    // Test
    $this->actingAs($user)
         ->get('/admin/posts')
         ->assertStatus(200);
});

test('super admin bypasses all permissions', function () {
    $user = User::factory()->create();
    $user->assignRole('Super-Admin');
    
    // Super admin can access anything
    expect($user->can('posts.viewAny'))->toBeTrue();
    expect($user->can('nonexistent.permission'))->toBeTrue();
    expect($user->isSuperAdmin())->toBeTrue();
});
```

---

## 🔄 Migration Guide

### From Other Laravel Permission Packages

Shield Lite is **100% compatible** with Spatie Permission:

```php
// Your existing code works unchanged!
$user->assignRole('admin');
$user->givePermissionTo('edit posts');
$user->can('edit posts');

// Plus new Shield Lite features
$user->isSuperAdmin();
```

### From Complex Authorization Setups

1. **Replace complex traits** with single `HasShield` trait
2. **Replace manual permission setup** with `defineGates()` in resources
3. **Remove custom authorization logic** - let Shield Lite handle it
4. **Assign `Super-Admin` role** to admin users for bypass

---

## 🆚 Before vs After

### ❌ Before (Complex)
```php
// User Model - Multiple traits with conflicts
use HasRoles, HasPermissions, HasShieldRoles;
use HasRoles {
    HasRoles::assignRole insteadof HasShieldRoles;
    // ... complex trait precedence
}

// Resource - Manual permission checks
public static function canAccess(): bool
{
    return Gate::allows('viewAny', static::class);
}

// Setup - Manual commands
composer require spatie/laravel-permission
php artisan vendor:publish --provider="Spatie\Permission\PermissionServiceProvider"
php artisan migrate
// ... manual permission creation
```

### ✅ After (Simple)
```php
// User Model - Single trait
use HasShield;

// Resource - Auto authorization
use HasShieldLite;

public function defineGates(): array
{
    return ['posts.viewAny' => 'View posts'];
}

// Setup - Single command
php artisan shield-lite:install
```

---

## ❓ FAQ

### Q: Do I need to create permissions manually?
**A:** No! Permissions are auto-created from your `defineGates()` method.

### Q: Can I use this with existing Spatie Permission code?
**A:** Yes! 100% compatible. Your existing code works unchanged.

### Q: What happens if I don't define any gates?
**A:** Resources will allow access by default (for authenticated users).

### Q: How do I disable super admin bypass?
**A:** Remove `Super-Admin` from `super_admin_roles` config array.

### Q: Can I use custom permission names?
**A:** Yes! Use any naming convention in your `defineGates()` method.

---

## 🚨 Troubleshooting

### Permission not found errors
```bash
# Clear caches
php artisan optimize:clear
php artisan permission:cache-reset

# Re-register permissions
YourResource::registerGates();
```

### Installation issues
```bash
# Make sure Spatie Permission is installed first
composer require spatie/laravel-permission

# Then install Shield Lite
php artisan shield-lite:install --force
```

### User model conflicts
Make sure you only use the `HasShield` trait:
```php
class User extends Authenticatable
{
    use HasShield; // Only this trait needed!
    // Remove other authorization traits
}
```

---

## 📈 Performance

Shield Lite is built on Spatie Permission's proven architecture:

- ✅ **Database-optimized** queries
- ✅ **Smart caching** system  
- ✅ **Minimal memory** footprint
- ✅ **Laravel-native** performance

---

## 🤝 Contributing

We welcome contributions! 

1. Fork the repository
2. Create feature branch (`git checkout -b feature/amazing-feature`)
3. Commit changes (`git commit -m 'Add amazing feature'`)
4. Push to branch (`git push origin feature/amazing-feature`)
5. Open Pull Request

---

## 📄 License

Shield Lite is open-sourced software licensed under the [MIT license](LICENSE.md).

---

## 💪 Support

- 🐛 **Issues**: [GitHub Issues](https://github.com/juniyasyos/shield-lite/issues)
- 💬 **Discussions**: [GitHub Discussions](https://github.com/juniyasyos/shield-lite/discussions)
- 📧 **Email**: [support@shield-lite.com]

---

**Made with ❤️ for developers who value simplicity**

*"Why make it complicated when you can make it simple?"*