<?php

namespace ACTTraining\PermissionsManager\Livewire;

use ACTTraining\PermissionsManager\Models\Permission;
use ACTTraining\PermissionsManager\Models\Role;
use ACTTraining\QueryBuilder\Support\Columns\Column;
use ACTTraining\QueryBuilder\Support\Filters\SelectFilter;
use ACTTraining\QueryBuilder\Support\Filters\TextFilter;
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

class PermissionsTable extends TableBuilder
{
    public bool $showPermissionModal = false;

    public bool $showDeleteModal = false;

    public ?string $editingPermission = null;

    public ?string $deletingPermission = null;

    public array $form = [
        'name' => '',
        'description' => '',
        'category' => null,
        'guard_name' => 'web',
        'roles' => [],
    ];

    public int $perPage = 6;

    protected string $model = Permission::class;

    protected $listeners = ['create-permission' => 'createPermission'];

    /**
     * Build the query for permissions with eager loading.
     */
    public function query(): Builder
    {
        $permissionModel = config('permissions-manager.models.permission', Permission::class);
        $userModel = config('permissions-manager.models.user');

        $query = $permissionModel::query()
            ->with(['roles'])
            ->selectRaw('
                permissions.*,
                (
                    SELECT COUNT(DISTINCT user_id) FROM (
                        SELECT model_uuid as user_id
                        FROM model_has_permissions
                        WHERE permission_id = permissions.uuid
                        AND model_type = ?
                        UNION
                        SELECT mhr.model_uuid as user_id
                        FROM model_has_roles mhr
                        INNER JOIN role_has_permissions rhp ON mhr.role_id = rhp.role_id
                        WHERE rhp.permission_id = permissions.uuid
                        AND mhr.model_type = ?
                    ) as all_users
                ) as users_count
            ', [$userModel, $userModel])
            ->when(! config('permissions-manager.guard.show_selection'), function ($query) {
                $query->where('guard_name', config('permissions-manager.guard.default', 'web'));
            });

        return $query->orderByRaw('LOWER(permissions.name)');
    }

    /**
     * Configure the table builder settings.
     */
    public function config(): void
    {
        $perPage = config('permissions-manager.pagination.permissions_per_page', 6);
        $this->perPage = $perPage;

        $this
            ->displayFilters()
            ->rowClickable(false)
            ->loadingIndicator()
            ->setSpinnerColor('text-orange-500');
    }

    /**
     * Get all available roles for the role assignment checkboxes.
     */
    #[Computed]
    public function availableRoles(): Collection
    {
        $roleModel = config('permissions-manager.models.role', Role::class);
        $guardName = config('permissions-manager.guard.show_selection')
            ? $this->form['guard_name']
            : config('permissions-manager.guard.default', 'web');

        return $roleModel::where('guard_name', $guardName)
            ->orderBy('name')
            ->get();
    }

    /**
     * Open modal to create a new permission.
     */
    #[On('create-permission')]
    public function createPermission(): void
    {
        $this->resetForm();
        $this->editingPermission = null;
        $this->showPermissionModal = true;
    }

    /**
     * Open modal to edit an existing permission.
     */
    public function editPermission(string $uuid): void
    {
        try {
            $permissionModel = config('permissions-manager.models.permission', Permission::class);
            $permission = $permissionModel::findOrFail($uuid);

            $this->editingPermission = $permission->uuid;
            $this->form = [
                'name' => $permission->name,
                'description' => $permission->description ?? '',
                'category' => $permission->category,
                'guard_name' => $permission->guard_name,
                'roles' => $permission->roles->pluck('uuid')->toArray(),
            ];

            $this->showPermissionModal = true;
        } catch (Exception $e) {
            Flux::toast(
                text: 'Failed to load permission: ' . $e->getMessage(),
                heading: 'Error',
                variant: 'danger'
            );
        }
    }

    /**
     * Save permission (create or update).
     */
    public function savePermission(): void
    {
        $this->validate($this->getValidationRules());

        try {
            DB::transaction(function () {
                $permissionModel = config('permissions-manager.models.permission', Permission::class);
                $roleModel = config('permissions-manager.models.role', Role::class);

                if ($this->editingPermission) {
                    // Update existing permission
                    $permission = $permissionModel::findOrFail($this->editingPermission);
                    $permission->update([
                        'name' => $this->form['name'],
                        'description' => $this->form['description'],
                        'category' => $this->form['category'],
                    ]);
                } else {
                    // Create new permission
                    $permission = $permissionModel::create([
                        'name' => $this->form['name'],
                        'description' => $this->form['description'],
                        'category' => $this->form['category'],
                        'guard_name' => $this->form['guard_name'],
                    ]);
                }

                // Sync roles
                if (! empty($this->form['roles'])) {
                    $roles = $roleModel::whereIn('uuid', $this->form['roles'])->get();
                    $permission->syncRoles($roles);
                } else {
                    $permission->syncRoles([]);
                }

                // Clear Spatie cache
                app()[PermissionRegistrar::class]->forgetCachedPermissions();
            });

            $this->closePermissionModal();
            $this->resetPage();

            Flux::toast(
                text: $this->editingPermission
                    ? 'Permission successfully updated.'
                    : 'Permission successfully created.',
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
    public function deletePermission(string $uuid): void
    {
        $this->deletingPermission = $uuid;
        $this->showDeleteModal = true;
    }

    /**
     * Confirm and execute permission deletion.
     */
    public function confirmDelete(): void
    {
        if (! $this->deletingPermission) {
            return;
        }

        try {
            $permissionModel = config('permissions-manager.models.permission', Permission::class);
            $permission = $permissionModel::findOrFail($this->deletingPermission);

            // Check if permission is assigned to users
            $userCount = DB::table('model_has_permissions')
                ->where('permission_id', $permission->uuid)
                ->count();

            if ($userCount > 0) {
                $this->showDeleteModal = false;
                $this->deletingPermission = null;

                Flux::toast(
                    text: "Cannot delete '{$permission->name}' because it is assigned to {$userCount} user(s). Please remove user assignments first.",
                    heading: 'Cannot Delete Permission',
                    variant: 'danger'
                );

                return;
            }

            $permission->delete();

            // Clear Spatie cache
            app()[PermissionRegistrar::class]->forgetCachedPermissions();

            $this->showDeleteModal = false;
            $this->deletingPermission = null;
            $this->resetPage();

            Flux::toast(
                text: 'Permission successfully deleted.',
                heading: 'Success',
                variant: 'success'
            );
        } catch (Exception $e) {
            $this->showDeleteModal = false;
            $this->deletingPermission = null;

            Flux::toast(
                text: 'Failed to delete permission: ' . $e->getMessage(),
                heading: 'Error',
                variant: 'danger'
            );
        }
    }

    /**
     * Close the permission modal and reset form.
     */
    public function closePermissionModal(): void
    {
        $this->showPermissionModal = false;
        $this->editingPermission = null;
        $this->resetForm();
        $this->resetValidation();
    }

    /**
     * Define table columns.
     */
    public function columns(): array
    {
        return [
            Column::make('Permission', 'name')
                ->sortable()
                ->component('permissions-manager::permission-title'),

            Column::make('Category', 'category')
                ->sortable()
                ->component('permissions-manager::category-badge'),

            Column::make('Roles', 'roles')
                ->reformatUsing(function ($value, $row) {
                    return $row->roles->pluck('name')->join(', ') ?: '—';
                }),

            Column::make('Users', 'users_count')
                ->sortable()
                ->reformatUsing(function ($value) {
                    return $value ?? 0;
                }),

            Column::make('', 'uuid')
                ->component('permissions-manager::permission-actions'),
        ];
    }

    /**
     * Define table filters.
     */
    public function filters(): array
    {
        $categoryEnum = config('permissions-manager.category_enum');

        return [
            TextFilter::make('Permission', 'name'),

            SelectFilter::make('Category', 'category')
                ->withOptions(
                    collect($categoryEnum::toArray())
                        ->mapWithKeys(fn ($value, $key) => [
                            $key => $categoryEnum::make($key)->label,
                        ])
                        ->toArray()
                ),
        ];
    }

    /**
     * Get validation rules for permission form.
     */
    protected function getValidationRules(): array
    {
        $guardName = $this->form['guard_name'];

        return [
            'form.name' => [
                'required',
                'string',
                'max:255',
                Rule::unique('permissions', 'name')
                    ->where('guard_name', $guardName)
                    ->ignore($this->editingPermission, 'uuid'),
            ],
            'form.description' => 'nullable|string|max:500',
            'form.category' => 'nullable|string',
            'form.guard_name' => 'required|string',
            'form.roles' => 'array',
            'form.roles.*' => 'exists:roles,uuid',
        ];
    }

    /**
     * Handle database exceptions and return user-friendly messages.
     */
    protected function handleDatabaseException(Exception $e): string
    {
        if (str_contains($e->getMessage(), 'Duplicate entry') || str_contains($e->getMessage(), 'UNIQUE constraint')) {
            return 'A permission with this name already exists for this guard.';
        }

        return 'An error occurred while saving the permission. Please try again.';
    }

    /**
     * Reset the form to default values.
     */
    protected function resetForm(): void
    {
        $this->form = [
            'name' => '',
            'description' => '',
            'category' => null,
            'guard_name' => config('permissions-manager.guard.default', 'web'),
            'roles' => [],
        ];
    }

    /**
     * Get the footer view for the table.
     */
    public function footerView(): ?string
    {
        return 'permissions-manager::livewire.partials.permissions-footer';
    }
}
