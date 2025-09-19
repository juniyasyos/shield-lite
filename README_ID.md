# Shield Lite - Authorization Laravel yang Super Simpel

🛡️ Plugin authorization Laravel untuk Filament yang **sangat mudah**. Install dengan 3 command, pakai dengan 1 trait!

## ✨ Kenapa Shield Lite?

- 🚀 **Install 3 Command** - Dari nol sampai jadi dalam 2 menit
- 🎯 **1 Trait untuk Model/Resource** - Tidak ada trait yang kompleks
- 🛡️ **Auto Super Admin** - Role `Super-Admin` otomatis bypass semua permission
- 📦 **Auto Permissions** - Generate otomatis dari method `defineGates()`
- 🔄 **Compatible dengan Spatie** - Dibangun di atas Spatie Permission (100% compatible)
- ⚡ **Zero Config** - Langsung jalan, customize kalau perlu

## 🚀 Install Super Cepat

### Langkah 1: Install Package
```bash
composer require spatie/laravel-permission juniyasyos/shield-lite
```

### Langkah 2: Setup Otomatis Semua
```bash
php artisan shield-lite:install
```

### Langkah 3: Buat User Admin
```bash
php artisan shield-lite:user
```

**Selesai! 🎉 Siap pakai dalam 3 command!**

---

## 📖 Cara Pakai

### 1. User Model (Udah otomatis saat install)

```php
<?php

namespace App\Models;

use Illuminate\Foundation\Auth\User as Authenticatable;
use juniyasyos\ShieldLite\Concerns\HasShield;

class User extends Authenticatable
{
    use HasShield; // Cuma 1 trait ini aja!
    
    // Code model yang udah ada tetap sama...
}
```

### 2. Filament Resources

Tinggal tambah trait dan define permission:

```php
<?php

namespace App\Filament\Resources;

use Filament\Resources\Resource;
use juniyasyos\ShieldLite\Concerns\HasShieldLite;

class PostResource extends Resource
{
    use HasShieldLite; // Cuma 1 trait untuk authorization!

    protected static ?string $model = Post::class;

    /**
     * Define permission untuk resource ini
     * Permission otomatis dibuat di database!
     */
    public function defineGates(): array
    {
        return [
            'posts.viewAny' => 'Lihat daftar posts',
            'posts.create' => 'Buat post baru',
            'posts.update' => 'Edit posts', 
            'posts.delete' => 'Hapus posts',
        ];
    }

    // Code resource yang udah ada...
}
```

**Udah! Permission otomatis dibuat pas resource load.**

---

## 🔑 Cek Permission

### Di Controller
```php
class PostController extends Controller
{
    public function index()
    {
        // Cek permission
        if (auth()->user()->can('posts.viewAny')) {
            return view('posts.index');
        }
        
        abort(403);
    }
    
    public function create()
    {
        $this->authorize('posts.create'); // Cara Laravel
        return view('posts.create');
    }
}
```

### Di Blade Template
```blade
@can('posts.create')
    <a href="{{ route('posts.create') }}" class="btn btn-primary">
        Buat Post
    </a>
@endcan

@can('posts.update', $post)
    <a href="{{ route('posts.edit', $post) }}">Edit</a>
@endcan

@cannot('posts.delete', $post)
    <span class="text-muted">Gak bisa hapus</span>
@endcannot
```

### Kelola Role
```php
// Assign role
$user = User::find(1);
$user->assignRole('admin');
$user->assignRole(['editor', 'moderator']);

// Cek role
if ($user->hasRole('admin')) {
    // User adalah admin
}

if ($user->hasAnyRole(['admin', 'editor'])) {
    // User punya salah satu dari role ini
}

// Ambil role user
$roles = $user->getRoleNames(); // Collection
$rolesArray = $user->getRoleNamesArray(); // Array
```

---

## 👑 Magic Super Admin

User dengan role `Super-Admin` otomatis **bypass SEMUA cek permission**:

```php
// Buat super admin
$user = User::find(1);
$user->assignRole('Super-Admin');

// Sekarang user ini bisa APAPUN
$user->can('posts.create');        // ✅ true
$user->can('users.delete');        // ✅ true  
$user->can('any.permission');      // ✅ true
$user->can('permission.gakada');   // ✅ true

// Cek kalau user super admin
if ($user->isSuperAdmin()) {
    // User ini punya god mode!
}
```

**Perfect untuk admin aplikasi yang butuh akses ke semuanya.**

---

## 📋 Command yang Tersedia

### Install & Setup
```bash
# Install semua otomatis
php artisan shield-lite:install

# Force reinstall (timpa file yang udah ada)
php artisan shield-lite:install --force
```

### Kelola User
```bash
# Buat user admin interaktif
php artisan shield-lite:user

# Buat admin dengan email/password spesifik
php artisan shield-lite:user --email=admin@company.com --password=secret123
```

### Kelola Role
```bash
# Buat role baru
php artisan shield-lite:role manager

# Buat role dengan deskripsi
php artisan shield-lite:role "Content Editor" --description="Bisa kelola konten"
```

---

## ❓ FAQ

### Q: Harus buat permission manual gak?
**A:** Enggak! Permission otomatis dibuat dari method `defineGates()`.

### Q: Bisa dipake sama code Spatie Permission yang udah ada?
**A:** Bisa! 100% compatible. Code yang udah ada tetap jalan.

### Q: Kalau gak define gates apa-apa gimana?
**A:** Resource akan allow akses by default (untuk user yang udah login).

### Q: Gimana cara disable super admin bypass?
**A:** Hapus `Super-Admin` dari array `super_admin_roles` di config.

### Q: Bisa pakai nama permission custom?
**A:** Bisa! Pakai naming convention apa aja di method `defineGates()`.

---

## 🚨 Troubleshooting

### Error permission not found
```bash
# Clear cache
php artisan optimize:clear
php artisan permission:cache-reset

# Re-register permission
YourResource::registerGates();
```

### Masalah instalasi
```bash
# Pastikan Spatie Permission udah diinstall dulu
composer require spatie/laravel-permission

# Baru install Shield Lite
php artisan shield-lite:install --force
```

### Konflik di User model
Pastikan cuma pakai trait `HasShield`:
```php
class User extends Authenticatable
{
    use HasShield; // Cuma trait ini aja yang diperlukan!
    // Hapus trait authorization lainnya
}
```

---

**Dibuat dengan ❤️ untuk developer yang suka yang simpel**

*"Ngapain ribet kalau bisa simpel?"*
