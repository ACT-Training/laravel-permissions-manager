<?php

namespace ACTTraining\PermissionsManager\Models;

use ACTTraining\PermissionsManager\Models\Concerns\HasUuidPrimaryKey;
use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Spatie\Permission\Models\Role as SpatieRole;
use Spatie\Permission\PermissionRegistrar;

class Role extends SpatieRole
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
        'is_protected',
    ];

    /**
     * Boot the model.
     */
    protected static function booted(): void
    {
        // Clear Spatie's permission cache when roles are saved or deleted
        static::saved(function () {
            app()[PermissionRegistrar::class]->forgetCachedPermissions();
        });

        static::deleted(function () {
            app()[PermissionRegistrar::class]->forgetCachedPermissions();
        });
    }

    /**
     * Override the permissions relationship to use uuid as the key.
     */
    public function permissions(): BelongsToMany
    {
        return $this->belongsToMany(
            config('permissions-manager.models.permission', Permission::class),
            config('permission.table_names.role_has_permissions'),
            'role_id',
            'permission_id',
            'uuid',  // Parent key (role's primary key)
            'uuid'   // Related key (permission's primary key)
        );
    }

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'is_protected' => 'boolean',
        ];
    }
}
