# Laravel Permissions Manager

A comprehensive roles and permissions management UI for Laravel applications, built on **Spatie Laravel Permission** with **Livewire 3 or 4** and **FluxUI Pro** components.

Perfect for ACT Training internal applications and any Laravel project requiring a robust, UUID-based permissions management interface.

## Features

✅ **Complete CRUD** for permissions and roles

✅ **UUID-based** primary keys with Spatie Permission integration

✅ **Category-based** organization with colour-coded badges

✅ **Protected roles** that cannot be deleted or renamed

✅ **User assignment** tracking and prevention of deletions

✅ **FluxUI Pro** components for beautiful UI

✅ **TableBuilder** integration for powerful tables

✅ **Fully customizable** views, models, and categories

✅ **Tested** on Livewire 3 and Livewire 4

---

## Requirements

- PHP 8.2+ (8.3+ for Laravel 13)
- Laravel 12 or 13
- Spatie Laravel Permission 6
- Livewire 3 or 4
- FluxUI Pro
- ACT Training QueryBuilder 2.3+ or 3.x (installed automatically)

No enum package is required: categories are a plain PHP backed enum.

---

## Installation

### Step 1: Install the Package

```bash
composer require act-training/laravel-permissions-manager
```

### Step 2: Publish Configuration and Migrations

```bash
# Publish configuration file
php artisan vendor:publish --tag=permissions-manager-config

# Publish migrations
php artisan vendor:publish --tag=permissions-manager-migrations
```

### Step 3: Run Migrations

```bash
php artisan migrate
```

This will add `description` and `category` columns to your `permissions` table, and `description` and `is_protected` columns to your `roles` table.

### Step 4: Create Your Category Enum (Recommended)

Create a string-backed enum that implements the `HasColor` contract (and, optionally, `HasLabel`):

```bash
php artisan make:enum PermissionCategoryEnum --string
```

```php
<?php

namespace App\Enums;

use ACTTraining\PermissionsManager\Contracts\HasColor;
use ACTTraining\PermissionsManager\Contracts\HasLabel;

enum PermissionCategoryEnum: string implements HasColor, HasLabel
{
    case Admin = 'admin';
    case Forms = 'forms';
    case Users = 'users';
    case Settings = 'settings';
    case Other = 'other';

    public function label(): string
    {
        return match ($this) {
            self::Admin => 'Admin',
            self::Forms => 'Forms',
            self::Users => 'Users',
            self::Settings => 'Settings',
            self::Other => 'Other',
        };
    }

    public function color(): string
    {
        return match ($this) {
            self::Admin => 'pink',
            self::Forms => 'purple',
            self::Users => 'orange',
            self::Settings => 'red',
            self::Other => 'gray',
        };
    }
}
```

The backing value (e.g. `admin`) is what is stored in the `permissions.category` column. If the enum has no `label()` method, the label is made from the case name (`DataKpis` becomes "Data Kpis").

> **Upgrading from 1.0?** Enums built on `spatie/laravel-enum` (or `spatie/enum`) still work unchanged: the package reads their `toArray()`, `make()` and `label` as before. The package no longer needs either enum package itself, so you only need one installed if your own enum uses it.

### Step 5: Update Configuration

Edit `config/permissions-manager.php` to reference your enum:

```php
'category_enum' => App\Enums\PermissionCategoryEnum::class,
```

### Step 6: Add Routes

In your `routes/web.php` or a service provider:

```php
use ACTTraining\PermissionsManager\Livewire\PermissionsAndRoles;

Route::middleware(['auth', 'admin'])->group(function () {
    Route::get('/admin/permissions-and-roles', PermissionsAndRoles::class)
        ->name('admin.permissions-and-roles');
});
```

### Step 7: Add to Navigation

Add a link to your navigation menu:

```blade
<flux:navlist.item icon="shield-check" href="{{ route('admin.permissions-and-roles') }}">
    Roles & Permissions
</flux:navlist.item>
```

---

## Configuration

The `config/permissions-manager.php` file provides extensive customization options:

### Category Enum

```php
'category_enum' => App\Enums\PermissionCategoryEnum::class,
```

Specify your custom enum class. It must implement `ACTTraining\PermissionsManager\Contracts\HasColor`, and should be a string-backed PHP enum (spatie enums from version 1.0 are still supported).

### Models

```php
'models' => [
    'permission' => ACTTraining\PermissionsManager\Models\Permission::class,
    'role' => ACTTraining\PermissionsManager\Models\Role::class,
    'user' => App\Models\User::class,
],
```

Override default models with your own implementations if needed.

### Guard Configuration

```php
'guard' => [
    'show_selection' => env('PERMISSIONS_SHOW_GUARD', false),
    'default' => 'web',
    'available_guards' => ['web', 'api'],
],
```

Control guard visibility in the UI. When `show_selection` is `false`, the guard field is hidden and `default` is used automatically.

### Protected Roles

```php
'protected_roles' => [
    'Admin',
    'Basic',
],
```

A role is protected when its `is_protected` column is `true`; protected roles cannot be deleted or renamed in the UI (their description and permissions can still be edited). Set the flag for the roles your application relies on by name, for example in a seeder:

```php
Role::whereIn('name', ['Admin', 'Basic'])->update(['is_protected' => true]);
```

The `protected_roles` list documents those names; the package itself checks the `is_protected` column.

### Pagination

```php
'pagination' => [
    'permissions_per_page' => 6,
    'roles_per_page' => null, // null = show all roles on one page
],
```

Configure pagination for permissions and roles lists.

### Features

```php
'features' => [
    'category_filtering' => true,
],
```

Enable/disable category filtering in the role editor.

---

## Usage

### Accessing the UI

Visit `/admin/permissions-and-roles` (or your configured route) to access the permissions manager interface.

### Creating Permissions

1. Click "Create a Permission" button
2. Fill in name, description, and category
3. Optionally assign to roles
4. Click "Save"

### Creating Roles

1. Click "Create a Role" button
2. Fill in name and description
3. Select permissions (with optional category filtering)
4. Click "Save"

### Editing & Deleting

Use the action menu (⋮) on each row to edit or delete permissions/roles.

**Note:** Protected roles cannot be deleted or renamed, and roles/permissions assigned to users cannot be deleted until unassigned.

### Embedding the Components

The page and its two tables are Livewire components you can place in your own views:

```blade
<livewire:permissions-manager::permissions-and-roles />
<livewire:permissions-manager::roles-table />
<livewire:permissions-manager::permissions-table />
```

These names work on both Livewire 3 and Livewire 4; on Livewire 4 the package registers `permissions-manager` as a component namespace, so no extra setup is needed in your app.

---

## Customization

### Publishing Views

To customize the UI, publish the views:

```bash
php artisan vendor:publish --tag=permissions-manager-views
```

Views will be published to `resources/views/vendor/permissions-manager/`.

### Using Custom Models

Create your own models that extend the package models:

```php
<?php

namespace App\Models;

use ACTTraining\PermissionsManager\Models\Permission as BasePermission;

class Permission extends BasePermission
{
    // Add custom methods or relationships here
}
```

Then update `config/permissions-manager.php`:

```php
'models' => [
    'permission' => App\Models\Permission::class,
    // ...
],
```

### Overriding TableBuilder Components

The package uses ACT Training's QueryBuilder package for tables. You can override column components by publishing views.

---

## Testing

The package has a PHPUnit test suite, run in CI on Livewire 3 and 4 with both the lowest and latest allowed dependencies.

### Running Tests

```bash
composer test
```

The tests use the free `livewire/flux` package; the two Flux Pro components the views use (the listbox select and its option) are replaced by simple stand-ins in `tests/stubs`.

---

## Package Development

### Local Development

Clone the repository and install dependencies:

```bash
git clone https://github.com/act-training/laravel-permissions-manager.git
cd laravel-permissions-manager
composer install
```

### Running Tests During Development

```bash
vendor/bin/phpunit
```

---

## Troubleshooting

### Missing FluxUI Pro Components

**Error:** Class 'Flux\Flux' not found

**Solution:** Ensure FluxUI Pro is installed:
```bash
composer require livewire/flux-pro
```

### TableBuilder Not Found

**Error:** Class 'ACTTraining\QueryBuilder\TableBuilder' not found

**Solution:** Install the QueryBuilder package:
```bash
composer require act-training/query-builder
```

### UUID Issues

**Error:** Primary key not found

**Solution:** Ensure Spatie Permission is configured for UUID:
```php
// config/permission.php
'column_names' => [
    'model_morph_key' => 'model_uuid',
],
```

---

## Changelog

See [CHANGELOG.md](CHANGELOG.md) for version history.

---

## Contributing

This is an internal ACT Training package. Contributions from team members are welcome!

1. Create a feature branch
2. Make your changes with tests
3. Submit a pull request

---

## License

MIT License - see [LICENSE](LICENSE) file for details.

---

## Credits

- Built by ACT Training Development Team
- Based on [Spatie Laravel Permission](https://github.com/spatie/laravel-permission)
- Uses [Livewire](https://livewire.laravel.com/) and [FluxUI Pro](https://flux.laravel.com/)

---

## Support

For ACT Training team members, reach out in the #development Slack channel.

For issues, please create a GitHub issue in the repository.
