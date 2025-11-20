<?php

namespace ACTTraining\PermissionsManager\Livewire;

use ACTTraining\PermissionsManager\Models\Permission;
use ACTTraining\PermissionsManager\Models\Role;
use ACTTraining\QueryBuilder\Support\Columns\Column;
use ACTTraining\QueryBuilder\TableBuilder;
use Exception;
use Flux\Flux;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\Rule;
use Livewire\Attributes\Computed;
use Livewire\Attributes\On;
use Spatie\Permission\PermissionRegistrar;

class RolesTable extends TableBuilder
{
    public bool $showRoleModal = false;

    public bool $showDeleteModal = false;

    public ?string $editingRole = null;

    public ?string $deletingRole = null;

    public ?string $categoryFilter = null;

    public array $form = [
        'name' => '',
        'description' => '',
        'guard_name' => 'web',
        'permissions' => [],
    ];

    protected string $model = Role::class;

    protected $listeners = ['create-role' => 'createRole'];

    /**
     * Build the query for roles with eager loading.
     */
    public function query(): Builder
    {
        $roleModel = config('permissions-manager.models.role', Role::class);

        return $roleModel::query()
            ->with(['permissions'])
            ->withCount([
                'users as users_count',
                'permissions as permissions_count',
            ])
            ->when(! config('permissions-manager.guard.show_selection'), function ($query) {
                $query->where('guard_name', config('permissions-manager.guard.default', 'web'));
            })
            ->orderByRaw('LOWER(name)');
    }

    /**
     * Configure the table builder settings.
     */
    public function config(): void
    {
        $perPage = config('permissions-manager.pagination.roles_per_page');

        // If perPage is null, disable pagination (show all)
        if ($perPage === null) {
            $this->usePagination(false);
        } else {
            $this->perPage = $perPage;
        }

        $this
            ->displayFilters()
            ->rowClickable(false)
            ->loadingIndicator()
            ->setSpinnerColor('text-orange-500');
    }

    /**
     * Get all available permissions for the permission assignment checkboxes.
     */
    #[Computed]
    public function availablePermissions(): Collection
    {
        $permissionModel = config('permissions-manager.models.permission', Permission::class);
        $guardName = config('permissions-manager.guard.show_selection')
            ? $this->form['guard_name']
            : config('permissions-manager.guard.default', 'web');

        return $permissionModel::where('guard_name', $guardName)
            ->orderBy('name')
            ->get();
    }

    /**
     * Get filtered permissions based on category filter.
     */
    #[Computed]
    public function filteredPermissions(): Collection
    {
        $permissions = $this->availablePermissions;

        if ($this->categoryFilter && config('permissions-manager.features.category_filtering', true)) {
            return $permissions->filter(function ($permission) {
                return $permission->category === $this->categoryFilter;
            });
        }

        return $permissions;
    }

    /**
     * Get available permission categories.
     */
    #[Computed]
    public function availableCategories(): array
    {
        $categoryEnum = config('permissions-manager.category_enum');

        return collect($categoryEnum::toArray())
            ->map(fn ($value, $key) => [
                'value' => $key,
                'label' => $categoryEnum::make($key)->label,
            ])
            ->values()
            ->toArray();
    }

    /**
     * Open modal to create a new role.
     */
    #[On('create-role')]
    public function createRole(): void
    {
        $this->resetForm();
        $this->editingRole = null;
        $this->categoryFilter = null;
        $this->showRoleModal = true;
    }

    /**
     * Open modal to edit an existing role.
     */
    public function editRole(string $uuid): void
    {
        try {
            $roleModel = config('permissions-manager.models.role', Role::class);
            $role = $roleModel::with('permissions')->findOrFail($uuid);

            $this->editingRole = $role->uuid;
            $this->form = [
                'name' => $role->name,
                'description' => $role->description ?? '',
                'guard_name' => $role->guard_name,
                'permissions' => $role->permissions->pluck('uuid')->toArray(),
            ];
            $this->categoryFilter = null;

            $this->showRoleModal = true;
        } catch (Exception $e) {
            Flux::toast(
                text: 'Failed to load role: ' . $e->getMessage(),
                heading: 'Error',
                variant: 'danger'
            );
        }
    }

    /**
     * Duplicate an existing role with all its permissions.
     */
    public function duplicateRole(string $uuid): void
    {
        try {
            $roleModel = config('permissions-manager.models.role', Role::class);
            $originalRole = $roleModel::with('permissions')->findOrFail($uuid);

            DB::transaction(function () use ($originalRole, $roleModel) {
                // Generate a unique name for the duplicated role
                $baseName = $originalRole->name . ' (Copy)';
                $newName = $baseName;
                $counter = 1;

                while ($roleModel::where('name', $newName)->where('guard_name', $originalRole->guard_name)->exists()) {
                    $counter++;
                    $newName = $originalRole->name . ' (Copy ' . $counter . ')';
                }

                // Create the new role
                $newRole = $roleModel::create([
                    'name' => $newName,
                    'description' => $originalRole->description,
                    'guard_name' => $originalRole->guard_name,
                    'is_protected' => false, // Duplicated roles are never protected
                ]);

                // Copy all permissions
                if ($originalRole->permissions->isNotEmpty()) {
                    $newRole->syncPermissions($originalRole->permissions);
                }

                // Clear Spatie cache
                app()[PermissionRegistrar::class]->forgetCachedPermissions();
            });

            $this->resetPage();

            Flux::toast(
                text: "Role '{$originalRole->name}' successfully duplicated.",
                heading: 'Success',
                variant: 'success'
            );
        } catch (Exception $e) {
            Flux::toast(
                text: 'Failed to duplicate role: ' . $e->getMessage(),
                heading: 'Error',
                variant: 'danger'
            );
        }
    }

    /**
     * Save role (create or update).
     */
    public function saveRole(): void
    {
        $this->validate($this->getValidationRules());

        try {
            DB::transaction(function () {
                $roleModel = config('permissions-manager.models.role', Role::class);
                $permissionModel = config('permissions-manager.models.permission', Permission::class);

                if ($this->editingRole) {
                    // Update existing role
                    $role = $roleModel::findOrFail($this->editingRole);
                    $role->update([
                        'name' => $this->form['name'],
                        'description' => $this->form['description'],
                    ]);
                } else {
                    // Create new role
                    $role = $roleModel::create([
                        'name' => $this->form['name'],
                        'description' => $this->form['description'],
                        'guard_name' => $this->form['guard_name'],
                        'is_protected' => false,
                    ]);
                }

                // Sync permissions
                if (! empty($this->form['permissions'])) {
                    $permissions = $permissionModel::whereIn('uuid', $this->form['permissions'])->get();
                    $role->syncPermissions($permissions);
                } else {
                    $role->syncPermissions([]);
                }

                // Clear Spatie cache
                app()[PermissionRegistrar::class]->forgetCachedPermissions();
            });

            $this->closeRoleModal();
            $this->resetPage();

            Flux::toast(
                text: $this->editingRole
                    ? 'Role successfully updated.'
                    : 'Role successfully created.',
                heading: 'Success',
                variant: 'success'
            );
        } catch (Exception $e) {
            $errorMessage = $this->handleDatabaseException($e);

            Flux::toast(
                text: $errorMessage,
                heading: 'Error',
                variant: 'danger'
            );
        }
    }

    /**
     * Open delete confirmation modal.
     */
    public function deleteRole(string $uuid): void
    {
        $this->deletingRole = $uuid;
        $this->showDeleteModal = true;
    }

    /**
     * Confirm and execute role deletion.
     */
    public function confirmDelete(): void
    {
        if (! $this->deletingRole) {
            return;
        }

        try {
            $roleModel = config('permissions-manager.models.role', Role::class);
            $role = $roleModel::findOrFail($this->deletingRole);

            // Check if role is protected
            if ($role->is_protected) {
                $this->showDeleteModal = false;
                $this->deletingRole = null;

                Flux::toast(
                    text: "Cannot delete protected role: {$role->name}",
                    heading: 'Cannot Delete Role',
                    variant: 'danger'
                );

                return;
            }

            // Check if role is assigned to users
            $userCount = DB::table('model_has_roles')
                ->where('role_id', $role->uuid)
                ->count();

            if ($userCount > 0) {
                $this->showDeleteModal = false;
                $this->deletingRole = null;

                Flux::toast(
                    text: "Cannot delete '{$role->name}' because it is assigned to {$userCount} user(s). Please remove user assignments first.",
                    heading: 'Cannot Delete Role',
                    variant: 'danger'
                );

                return;
            }

            $role->delete();

            // Clear Spatie cache
            app()[PermissionRegistrar::class]->forgetCachedPermissions();

            $this->showDeleteModal = false;
            $this->deletingRole = null;
            $this->resetPage();

            Flux::toast(
                text: 'Role successfully deleted.',
                heading: 'Success',
                variant: 'success'
            );
        } catch (Exception $e) {
            $this->showDeleteModal = false;
            $this->deletingRole = null;

            Flux::toast(
                text: 'Failed to delete role: ' . $e->getMessage(),
                heading: 'Error',
                variant: 'danger'
            );
        }
    }

    /**
     * Close the role modal and reset form.
     */
    public function closeRoleModal(): void
    {
        $this->showRoleModal = false;
        $this->editingRole = null;
        $this->categoryFilter = null;
        $this->resetForm();
        $this->resetValidation();
    }

    /**
     * Define table columns.
     */
    public function columns(): array
    {
        return [
            Column::make('Role', 'name')
                ->sortable()
                ->component('permissions-manager::role-title'),

            Column::make('Permissions', 'permissions_count')
                ->sortable()
                ->reformatUsing(function ($value) {
                    return $value ?? 0;
                }),

            Column::make('Users', 'users_count')
                ->sortable()
                ->reformatUsing(function ($value) {
                    return $value ?? 0;
                }),

            Column::make('', 'uuid')
                ->component('permissions-manager::role-actions'),
        ];
    }

    /**
     * Define table filters.
     */
    public function filters(): array
    {
        return [];
    }

    /**
     * Get validation rules for role form.
     */
    protected function getValidationRules(): array
    {
        $guardName = $this->form['guard_name'];

        return [
            'form.name' => [
                'required',
                'string',
                'max:255',
                Rule::unique('roles', 'name')
                    ->where('guard_name', $guardName)
                    ->ignore($this->editingRole, 'uuid'),
            ],
            'form.description' => 'nullable|string|max:500',
            'form.guard_name' => 'required|string',
            'form.permissions' => 'array',
            'form.permissions.*' => 'exists:permissions,uuid',
        ];
    }

    /**
     * Handle database exceptions and return user-friendly messages.
     */
    protected function handleDatabaseException(Exception $e): string
    {
        if (str_contains($e->getMessage(), 'Duplicate entry') || str_contains($e->getMessage(), 'UNIQUE constraint')) {
            return 'A role with this name already exists for this guard.';
        }

        return 'An error occurred while saving the role. Please try again.';
    }

    /**
     * Reset the form to default values.
     */
    protected function resetForm(): void
    {
        $this->form = [
            'name' => '',
            'description' => '',
            'guard_name' => config('permissions-manager.guard.default', 'web'),
            'permissions' => [],
        ];
    }

    /**
     * Get the footer view for the table.
     */
    public function footerView(): ?string
    {
        return 'permissions-manager::livewire.partials.roles-footer';
    }
}
