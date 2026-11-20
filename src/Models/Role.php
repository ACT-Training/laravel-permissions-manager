<?php

namespace ACTTraining\PermissionsManager\Models;

use ACTTraining\PermissionsManager\Models\Concerns\HasUuidPrimaryKey;
use ACTTraining\PermissionsManager\Support\PermissionTables;
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
     * Override the permissions relationship to use the configured permission
     * model, joined on each model's own primary key.
     */
    public function permissions(): BelongsToMany
    {
        $permissionModel = config('permissions-manager.models.permission', Permission::class);

        return $this->belongsToMany(
            $permissionModel,
            PermissionTables::roleHasPermissions(),
            PermissionTables::rolePivotKey(),
            PermissionTables::permissionPivotKey(),
            $this->getKeyName(),
            (new $permissionModel)->getKeyName()
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
