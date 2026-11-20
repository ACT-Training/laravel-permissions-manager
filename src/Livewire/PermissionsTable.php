<?php

namespace ACTTraining\PermissionsManager\Livewire;

use ACTTraining\PermissionsManager\Livewire\Concerns\AuthorizesActions;
use ACTTraining\PermissionsManager\Livewire\Concerns\ResolvesPermissionModels;
use ACTTraining\PermissionsManager\Models\Permission;
use ACTTraining\PermissionsManager\Models\Role;
use ACTTraining\PermissionsManager\Support\PermissionCategories;
use ACTTraining\PermissionsManager\Support\PermissionTables;
use ACTTraining\QueryBuilder\Support\Columns\Column;
use ACTTraining\QueryBuilder\Support\Filters\SelectFilter;
use ACTTraining\QueryBuilder\Support\Filters\TextFilter;
use ACTTraining\QueryBuilder\TableBuilder;
use Exception;
use Flux\Flux;
use Illuminate\Auth\Access\AuthorizationException;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\Rule;
use Livewire\Attributes\Computed;
use Livewire\Attributes\On;
use Spatie\Permission\PermissionRegistrar;

class PermissionsTable extends TableBuilder
{
    use AuthorizesActions;
    use ResolvesPermissionModels;

    public bool $showPermissionModal = false;

    public bool $showDeleteModal = false;

    public int|string|null $editingPermission = null;

    public int|string|null $deletingPermission = null;

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
     * Use the configured permission model and check viewAny on every request.
     *
     * @throws AuthorizationException
     */
    public function booted(): void
    {
        $this->model = $this->permissionModel();

        parent::booted();

        $this->authorizeAction('viewAny', $this->permissionModel());
    }

    /**
     * Build the query for permissions with eager loading.
     *
     * Table and column names come from the model and spatie's permission
     * config, so the user count works for UUID and integer keys alike.
     */
    public function query(): Builder
    {
        $permissionModel = $this->permissionModel();
        $permission = new $permissionModel;
        $userMorphClass = $this->userMorphClass();

        $grammar = $permission->getConnection()->getQueryGrammar();
        $permissions = $grammar->wrapTable($permission->getTable());
        $permissionKey = $permissions . '.' . $grammar->wrap($permission->getKeyName());
        $modelHasPermissions = $grammar->wrapTable(PermissionTables::modelHasPermissions());
        $modelHasRoles = $grammar->wrapTable(PermissionTables::modelHasRoles());
        $roleHasPermissions = $grammar->wrapTable(PermissionTables::roleHasPermissions());
        $morphKey = $grammar->wrap(PermissionTables::modelMorphKey());
        $rolePivot = $grammar->wrap(PermissionTables::rolePivotKey());
        $permissionPivot = $grammar->wrap(PermissionTables::permissionPivotKey());

        $query = $permissionModel::query()
            ->with(['roles'])
            ->selectRaw("
                {$permissions}.*,
                (
                    SELECT COUNT(DISTINCT user_id) FROM (
                        SELECT {$morphKey} as user_id
                        FROM {$modelHasPermissions}
                        WHERE {$permissionPivot} = {$permissionKey}
                        AND model_type = ?
                        UNION
                        SELECT mhr.{$morphKey} as user_id
                        FROM {$modelHasRoles} mhr
                        INNER JOIN {$roleHasPermissions} rhp ON mhr.{$rolePivot} = rhp.{$rolePivot}
                        WHERE rhp.{$permissionPivot} = {$permissionKey}
                        AND mhr.model_type = ?
                    ) as all_users
                ) as users_count
            ", [$userMorphClass, $userMorphClass])
            ->when(! config('permissions-manager.guard.show_selection'), function ($query) {
                $query->where('guard_name', config('permissions-manager.guard.default', 'web'));
            });

        return $query->orderByRaw("LOWER({$permissions}.name)");
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
        $roleModel = $this->roleModel();
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
        $this->authorizeAction('create', $this->permissionModel());

        $this->resetForm();
        $this->editingPermission = null;
        $this->showPermissionModal = true;
    }

    /**
     * Open modal to edit an existing permission.
     */
    public function editPermission(int|string $key): void
    {
        $this->authorizeAction('update', $this->permissionModel(), $key);

        try {
            $permission = $this->permissionModel()::findOrFail($key);

            $this->editingPermission = $permission->getKey();
            $this->form = [
                'name' => $permission->name,
                'description' => $permission->description ?? '',
                'category' => $permission->category,
                'guard_name' => $permission->guard_name,
                'roles' => $this->keysAsStrings($permission->roles),
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
        if ($this->editingPermission) {
            $this->authorizeAction('update', $this->permissionModel(), $this->editingPermission);
        } else {
            $this->authorizeAction('create', $this->permissionModel());
        }

        $this->validate($this->getValidationRules());

        try {
            DB::transaction(function () {
                $permissionModel = $this->permissionModel();
                $roleModel = $this->roleModel();

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
                    $roles = $roleModel::whereKey($this->form['roles'])->get();
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
    public function deletePermission(int|string $key): void
    {
        $this->authorizeAction('delete', $this->permissionModel(), $key);

        $this->deletingPermission = $key;
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

        $this->authorizeAction('delete', $this->permissionModel(), $this->deletingPermission);

        try {
            $permission = $this->permissionModel()::findOrFail($this->deletingPermission);

            // Check if permission is assigned to users
            $userCount = DB::table(PermissionTables::modelHasPermissions())
                ->where(PermissionTables::permissionPivotKey(), $permission->getKey())
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

            Column::make('', (new ($this->permissionModel()))->getKeyName())
                ->component('permissions-manager::permission-actions'),
        ];
    }

    /**
     * Define table filters.
     */
    public function filters(): array
    {
        return [
            TextFilter::make('Permission', 'name'),

            SelectFilter::make('Category', 'category')
                ->withOptions(PermissionCategories::options()),
        ];
    }

    /**
     * Get validation rules for permission form.
     */
    protected function getValidationRules(): array
    {
        $guardName = $this->form['guard_name'];
        $permission = new ($this->permissionModel());
        $role = new ($this->roleModel());

        return [
            'form.name' => [
                'required',
                'string',
                'max:255',
                Rule::unique($permission->getTable(), 'name')
                    ->where('guard_name', $guardName)
                    ->ignore($this->editingPermission, $permission->getKeyName()),
            ],
            'form.description' => 'nullable|string|max:500',
            'form.category' => 'nullable|string',
            'form.guard_name' => 'required|string',
            'form.roles' => 'array',
            'form.roles.*' => Rule::exists($role->getTable(), $role->getKeyName()),
        ];
    }

    /**
     * The model_type value spatie stores for the configured user model.
     */
    protected function userMorphClass(): ?string
    {
        $userModel = config('permissions-manager.models.user');

        return $userModel && class_exists($userModel)
            ? (new $userModel)->getMorphClass()
            : $userModel;
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
