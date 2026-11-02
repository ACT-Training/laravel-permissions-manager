# Changelog

All notable changes to `laravel-permissions-manager` will be documented in this file.

## [Unreleased]

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
