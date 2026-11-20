<?php

namespace ACTTraining\PermissionsManager\Tests\Fixtures\IntegerKeys;

use Spatie\Permission\Models\Role as SpatieRole;

/**
 * An app-side role model on spatie's standard integer-id tables.
 */
class Role extends SpatieRole
{
    protected $fillable = [
        'name',
        'guard_name',
        'description',
        'is_protected',
    ];

    protected function casts(): array
    {
        return [
            'is_protected' => 'boolean',
        ];
    }
}
