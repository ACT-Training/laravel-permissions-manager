<?php

namespace ACTTraining\PermissionsManager\Livewire;

use ACTTraining\PermissionsManager\Livewire\Concerns\ResolvesPermissionModels;
use ACTTraining\PermissionsManager\Support\Authorization;
use Illuminate\Auth\Access\AuthorizationException;
use Livewire\Component;

class PermissionsAndRoles extends Component
{
    use ResolvesPermissionModels;

    /**
     * Refuse the page when the user may see neither table.
     *
     * @throws AuthorizationException
     */
    public function mount(): void
    {
        if (! Authorization::allows('viewAny', $this->roleModel())
            && ! Authorization::allows('viewAny', $this->permissionModel())) {
            throw new AuthorizationException;
        }
    }

    /**
     * Render the unified permissions and roles page.
     */
    public function render()
    {
        return view('permissions-manager::livewire.permissions-and-roles', [
            'canViewRoles' => Authorization::allows('viewAny', $this->roleModel()),
            'canCreateRoles' => Authorization::allows('create', $this->roleModel()),
            'canViewPermissions' => Authorization::allows('viewAny', $this->permissionModel()),
            'canCreatePermissions' => Authorization::allows('create', $this->permissionModel()),
        ]);
    }
}
