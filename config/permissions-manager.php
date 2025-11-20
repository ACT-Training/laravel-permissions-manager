<?php

return [

    /*
    |--------------------------------------------------------------------------
    | Permissions Manager Configuration
    |--------------------------------------------------------------------------
    |
    | This configuration file controls the behaviour of the Laravel Permissions
    | Manager package, including models, categories, guards, and UI settings.
    |
    */

    /*
    |--------------------------------------------------------------------------
    | Category Enum
    |--------------------------------------------------------------------------
    |
    | Specify the fully qualified class name of your Permission Category Enum.
    | This enum must implement ACTTraining\PermissionsManager\Contracts\HasColor.
    |
    | If not specified, the package's default PermissionCategoryEnum will be used.
    |
    */
    'category_enum' => ACTTraining\PermissionsManager\Enums\PermissionCategoryEnum::class,

    /*
    |--------------------------------------------------------------------------
    | Models
    |--------------------------------------------------------------------------
    |
    | Specify the models to use for permissions, roles, and users.
    | By default, the package uses its own models which extend Spatie's models
    | with UUID support. You can override these with your own models.
    |
    */
    'models' => [
        'permission' => ACTTraining\PermissionsManager\Models\Permission::class,
        'role' => ACTTraining\PermissionsManager\Models\Role::class,
        'user' => App\Models\User::class,
    ],

    /*
    |--------------------------------------------------------------------------
    | Guard Configuration
    |--------------------------------------------------------------------------
    |
    | Configure guard selection visibility and default guard for permissions
    | and roles. When show_selection is false (default), the guard field is
    | hidden from the UI and the default guard is used automatically.
    |
    */
    'guard' => [
        'show_selection' => env('PERMISSIONS_SHOW_GUARD', false),
        'default' => 'web',
        'available_guards' => ['web', 'api'],
    ],

    /*
    |--------------------------------------------------------------------------
    | Protected Roles
    |--------------------------------------------------------------------------
    |
    | List of role names that cannot be deleted through the UI or API.
    | These roles are critical to system functionality and should remain
    | protected. The is_protected flag on roles controls this behaviour.
    |
    */
    'protected_roles' => [
        'Admin',
        'Basic',
    ],

    /*
    |--------------------------------------------------------------------------
    | Pagination Settings
    |--------------------------------------------------------------------------
    |
    | Configure the number of items displayed per page for permissions
    | and roles lists. Set to null to disable pagination and show all items.
    |
    */
    'pagination' => [
        'permissions_per_page' => 6,
        'roles_per_page' => null, // null = show all roles on one page
    ],

    /*
    |--------------------------------------------------------------------------
    | Feature Flags
    |--------------------------------------------------------------------------
    |
    | Enable or disable specific features of the permissions manager.
    | category_filtering enables filtering permissions by category in role editor.
    |
    */
    'features' => [
        'category_filtering' => true,
    ],

];
