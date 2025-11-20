# Changelog

All notable changes to `laravel-permissions-manager` will be documented in this file.

## [Unreleased]

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

## [1.0.0] - TBD

Initial stable release.
