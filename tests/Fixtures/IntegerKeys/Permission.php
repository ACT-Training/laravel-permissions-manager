<?php

namespace ACTTraining\PermissionsManager\Tests\Fixtures\IntegerKeys;

use Spatie\Permission\Models\Permission as SpatiePermission;

/**
 * An app-side permission model on spatie's standard integer-id tables.
 */
class Permission extends SpatiePermission
{
    protected $fillable = [
        'name',
        'guard_name',
        'description',
        'category',
    ];
}
