# Laravel Permissions Manager

A comprehensive roles and permissions management UI for Laravel applications, built on **Spatie Laravel Permission** with **Livewire 3 or 4** and **FluxUI Pro** components.

Perfect for ACT Training internal applications and any Laravel project requiring a robust permissions management interface, whether its role tables use UUIDs or integer ids.

## Features

✅ **Complete CRUD** for permissions and roles

✅ **UUID or integer** primary keys with Spatie Permission integration

✅ **Optional authorisation** of every action through Laravel policies

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

### Authorisation

By default the package does no authorisation of its own: protect the page with route middleware, as in Step 6. To check each action as well, switch on the Gate checks:

```php
'authorization' => [
    'enabled' => env('PERMISSIONS_MANAGER_AUTHORIZE', false),
],
```

or set `PERMISSIONS_MANAGER_AUTHORIZE=true` in `.env`. Each action then asks Laravel's Gate, using the standard policy abilities, against the configured role or permission model:

| Action | Component method | Ability |
| --- | --- | --- |
| See the roles or permissions table (every request) | `booted()` | `viewAny` (model class) |
| Open the create form | `createRole()`, `createPermission()` | `create` (model class) |
| Save a new record | `saveRole()`, `savePermission()` with nothing being edited | `create` (model class) |
| Duplicate a role | `duplicateRole($key)` | `create` (role class) |
| Open the edit form | `editRole($key)`, `editPermission($key)` | `update` (the record) |
| Save an existing record | `saveRole()`, `savePermission()` while editing | `update` (the record) |
| Delete | `deleteRole($key)`, `deletePermission($key)`, `confirmDelete()` | `delete` (the record) |

A denied action throws `Illuminate\Auth\Access\AuthorizationException`, so Livewire returns a 403. The checks run on the server inside each action, because Livewire actions can be called directly; the matching buttons and menu items are also hidden. The page shows only the tables the user may view, and returns a 403 if they may view neither.

Register policies for your role and permission models, for example:

```php
Gate::policy(App\Models\Role::class, App\Policies\RolePolicy::class);
Gate::policy(App\Models\Permission::class, App\Policies\PermissionPolicy::class);
```

> **Note:** with authorisation enabled, a model with no registered policy is denied by Laravel's Gate (and so are guests), unless a `Gate::before` callback allows it. This is intended: switching the option on should never open anything up by accident.

When the option is off (the default), no checks run and nothing is hidden.

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

### Integer ids (non-UUID apps)

The package's own `Role` and `Permission` models use UUID primary keys, but nothing else in the package assumes UUIDs. If your app uses Spatie's standard tables (with `bigint` `id` columns), point the package at your own models instead. Every lookup, validation rule, view and the user-count query then uses each model's own key, table and Spatie's configured column names.

1. Create app-side models that extend Spatie's models and add the package's extra columns:

```php
<?php

namespace App\Models;

use Spatie\Permission\Models\Role as SpatieRole;

class Role extends SpatieRole
{
    protected $fillable = ['name', 'guard_name', 'description', 'is_protected'];

    protected function casts(): array
    {
        return ['is_protected' => 'boolean'];
    }
}
```

```php
<?php

namespace App\Models;

use Spatie\Permission\Models\Permission as SpatiePermission;

class Permission extends SpatiePermission
{
    protected $fillable = ['name', 'guard_name', 'description', 'category'];
}
```

2. Use them in both configs. Spatie's defaults for the morph key and pivot columns already match its standard tables:

```php
// config/permission.php
'models' => [
    'permission' => App\Models\Permission::class,
    'role' => App\Models\Role::class,
],

'column_names' => [
    'role_pivot_key' => null,       // role_id
    'permission_pivot_key' => null, // permission_id
    'model_morph_key' => 'model_id',
],

// config/permissions-manager.php
'models' => [
    'permission' => App\Models\Permission::class,
    'role' => App\Models\Role::class,
    'user' => App\Models\User::class,
],
```

3. Publish and run the package's migrations as usual (Step 2 and Step 3 above). They only add `description`, `category` and `is_protected` columns, so they work on integer-id tables.

The Livewire actions take the record's key, whichever type it is: `editRole(int|string $key)`, `duplicateRole(…)`, `deleteRole(…)`, `editPermission(…)` and `deletePermission(…)`. The selected ids in `form.permissions` and `form.roles` are held as strings, matching what the browser's checkboxes send.

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

**Solution:** If you use the package's UUID models, ensure Spatie Permission is configured for UUID (apps with integer ids should see [Integer ids (non-UUID apps)](#integer-ids-non-uuid-apps) instead):
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
