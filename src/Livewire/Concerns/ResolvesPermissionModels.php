<?php

namespace ACTTraining\PermissionsManager\Livewire\Concerns;

use ACTTraining\PermissionsManager\Models\Permission;
use ACTTraining\PermissionsManager\Models\Role;
use Illuminate\Database\Eloquent\Collection;

trait ResolvesPermissionModels
{
    /**
     * The configured role model class.
     *
     * @return class-string<Role>
     */
    protected function roleModel(): string
    {
        return config('permissions-manager.models.role', Role::class);
    }

    /**
     * The configured permission model class.
     *
     * @return class-string<Permission>
     */
    protected function permissionModel(): string
    {
        return config('permissions-manager.models.permission', Permission::class);
    }

    /**
     * Model keys as strings, matching the values the browser's checkboxes send.
     *
     * @return array<int, string>
     */
    protected function keysAsStrings(Collection $models): array
    {
        return $models->map(fn ($model): string => (string) $model->getKey())->values()->all();
    }
}
