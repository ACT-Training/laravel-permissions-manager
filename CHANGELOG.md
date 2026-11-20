# Changelog

All notable changes to `laravel-permissions-manager` will be documented in this file.

## [1.3.0]

Additive: apps using the package's UUID models, with authorisation left off, see no change in behaviour.

### Added
- Integer id support. Apps whose Spatie tables use `bigint` ids can point `permissions-manager.models` (and `permission.models`) at their own models extending Spatie's `Role` and `Permission`. See "Integer ids (non-UUID apps)" in the README.
- Optional authorisation, off by default: `permissions-manager.authorization.enabled` (env `PERMISSIONS_MANAGER_AUTHORIZE`). When on, every action checks Laravel's Gate (`viewAny`, `create`, `update`, `delete`) against the configured model, throws a 403 when denied, and hides the matching buttons. The page shows only the tables the user may view.
- Tests against Spatie's standard integer-id tables, for authorisation on and off, and for the UUID models.

### Changed
- The Livewire components no longer hard-code `uuid`. They use each model's own key, key name and table, and Spatie's configured table names, `model_morph_key`, `role_pivot_key` and `permission_pivot_key` (including in the permissions table's user-count query).
- `editRole`, `duplicateRole`, `deleteRole`, `editPermission` and `deletePermission` take `int|string $key`; `editingRole`, `deletingRole`, `editingPermission` and `deletingPermission` are `int|string|null`.
- The ids in `form.permissions` and `form.roles` are held as strings, so the checkboxes stay ticked for integer ids.
- The user count matches `model_type` against the user model's morph class, so apps with a morph map are counted correctly.
- The package `Role` model's `permissions()` relationship uses the models' key names and Spatie's pivot column names rather than literal `uuid`, `role_id` and `permission_id`.

## [1.2.0] - 2026-11-03

### Added
- Laravel 13 support. Requires `laravel/framework` `^12.0|^13.0`. Laravel 13 itself needs PHP 8.3+.
- The GitHub Actions workflow now also runs on Laravel 13 (PHP 8.4), alongside Laravel 12.

### Changed
- Dev dependencies widened so the tests run on both versions: `orchestra/testbench` `^10.0|^11.0`, `phpunit/phpunit` `^11.0|^12.0`.

## [1.1.0] - 2026-11-02

### Added
- Livewire 4 support. The `permissions-manager::` component names now resolve on Livewire 4 without the app registering a namespace.
- Protected roles (`is_protected`) can no longer be renamed; their description and permissions can still be edited.
- `HasLabel` contract for native category enums.
- Test suite (component resolution, protected roles, categories) and a GitHub Actions workflow covering Livewire 3 and 4 with lowest and latest dependencies.

### Changed
- Requires `act-training/query-builder` `^2.3|^3.0` instead of `dev-main`, so apps no longer need a dev version.
- Requires `livewire/livewire` `^3.0|^4.0`.
- The default `PermissionCategoryEnum` is now a native PHP backed enum (same stored values). Category enums built on spatie/enum or spatie/laravel-enum keep working.

### Removed
- The undeclared dependency on `spatie/laravel-enum`; the package needs no enum package.

## [1.0.0] - 2025-11-20

### Added
- Initial package release
- Complete CRUD operations for permissions and roles
- UUID-based primary key support for Spatie Permission
- Category-based organization with colour-coded badges
- Protected roles functionality
- User assignment tracking
- FluxUI Pro integration
- TableBuilder integration
- Comprehensive Livewire components
- Publishable views for customization
- Configurable category enum system
- Role duplication feature
- Comprehensive tests
- Full documentation

### Features
- PermissionsTable Livewire component
- RolesTable Livewire component
- PermissionsAndRoles unified component
- Permission and Role models with UUID support
- HasUuidPrimaryKey concern for consistent UUID handling
- Basic PermissionCategoryEnum with sensible defaults
- HasColor contract for category enums
- Publishable migrations for permissions and roles tables
- Configurable guard support
- Pagination configuration
- Feature flags for category filtering

### Configuration
- Customizable category enum
- Customizable models (Permission, Role, User)
- Guard configuration (show/hide selection, default guard)
- Protected roles list
- Pagination settings
- Feature toggles
