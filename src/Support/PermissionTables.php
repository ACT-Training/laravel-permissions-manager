<?php

namespace ACTTraining\PermissionsManager\Support;

/**
 * Table and column names of the Spatie Permission schema, read from
 * spatie's own `permission` config so UUID and integer keyed apps both work.
 */
class PermissionTables
{
    public static function modelHasRoles(): string
    {
        return config('permission.table_names.model_has_roles') ?: 'model_has_roles';
    }

    public static function modelHasPermissions(): string
    {
        return config('permission.table_names.model_has_permissions') ?: 'model_has_permissions';
    }

    public static function roleHasPermissions(): string
    {
        return config('permission.table_names.role_has_permissions') ?: 'role_has_permissions';
    }

    /**
     * The column holding the user's key in the model_has_* tables.
     */
    public static function modelMorphKey(): string
    {
        return config('permission.column_names.model_morph_key') ?: 'model_id';
    }

    public static function rolePivotKey(): string
    {
        return config('permission.column_names.role_pivot_key') ?: 'role_id';
    }

    public static function permissionPivotKey(): string
    {
        return config('permission.column_names.permission_pivot_key') ?: 'permission_id';
    }
}
