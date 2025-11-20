<?php

namespace ACTTraining\PermissionsManager\Models;

use ACTTraining\PermissionsManager\Models\Concerns\HasUuidPrimaryKey;
use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Spatie\Permission\Models\Permission as SpatiePermission;
use Spatie\Permission\PermissionRegistrar;

class Permission extends SpatiePermission
{
    use HasFactory;
    use HasUuids;
    use HasUuidPrimaryKey;

    /**
     * The attributes that are mass assignable.
     *
     * @var array<string>
     */
    protected $fillable = [
        'name',
        'guard_name',
        'description',
        'category',
    ];

    /**
     * Boot the model.
     */
    protected static function booted(): void
    {
        // Clear Spatie's permission cache when permissions are saved or deleted
        static::saved(function () {
            app()[PermissionRegistrar::class]->forgetCachedPermissions();
        });

        static::deleted(function () {
            app()[PermissionRegistrar::class]->forgetCachedPermissions();
        });
    }
}
