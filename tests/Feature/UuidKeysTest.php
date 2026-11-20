<?php

namespace ACTTraining\PermissionsManager\Tests\Feature;

use ACTTraining\PermissionsManager\Livewire\PermissionsTable;
use ACTTraining\PermissionsManager\Livewire\RolesTable;
use ACTTraining\PermissionsManager\Models\Permission;
use ACTTraining\PermissionsManager\Models\Role;
use ACTTraining\PermissionsManager\Tests\Fixtures\User;
use ACTTraining\PermissionsManager\Tests\TestCase;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Livewire;
use PHPUnit\Framework\Attributes\Test;

/**
 * The package's own UUID models keep working with the key-agnostic code.
 */
class UuidKeysTest extends TestCase
{
    use RefreshDatabase;

    #[Test]
    public function the_permissions_table_counts_users_by_uuid(): void
    {
        $permission = Permission::create(['name' => 'edit posts', 'guard_name' => 'web']);
        $role = Role::create(['name' => 'Editor', 'guard_name' => 'web']);
        $role->givePermissionTo($permission);
        User::create(['name' => 'Ann', 'email' => 'ann@example.com'])->assignRole($role);
        User::create(['name' => 'Bob', 'email' => 'bob@example.com'])->givePermissionTo($permission);

        $component = Livewire::test(PermissionsTable::class)
            ->assertSeeHtml("editPermission('{$permission->uuid}')");

        $this->assertSame(2, (int) $component->instance()->rowsQuery->first()->users_count);
    }

    #[Test]
    public function a_role_can_be_edited_and_saved_with_permissions_by_uuid(): void
    {
        $role = Role::create(['name' => 'Editor', 'guard_name' => 'web']);
        $edit = Permission::create(['name' => 'edit posts', 'guard_name' => 'web']);
        $publish = Permission::create(['name' => 'publish posts', 'guard_name' => 'web']);
        $role->givePermissionTo($edit);

        Livewire::test(RolesTable::class)
            ->assertSeeHtml("editRole('{$role->uuid}')")
            ->call('editRole', $role->uuid)
            ->assertSet('editingRole', $role->uuid)
            ->assertSet('form.permissions', [$edit->uuid])
            ->set('form.permissions', [$publish->uuid])
            ->call('saveRole')
            ->assertHasNoErrors();

        $this->assertSame(['publish posts'], $role->fresh()->permissions->pluck('name')->all());
    }

    #[Test]
    public function a_permission_can_be_saved_with_roles_and_deleted_by_uuid(): void
    {
        $permission = Permission::create(['name' => 'edit posts', 'guard_name' => 'web']);
        $role = Role::create(['name' => 'Editor', 'guard_name' => 'web']);

        Livewire::test(PermissionsTable::class)
            ->call('editPermission', $permission->uuid)
            ->set('form.roles', [$role->uuid])
            ->call('savePermission')
            ->assertHasNoErrors();

        $this->assertTrue($role->fresh()->hasPermissionTo('edit posts'));

        Livewire::test(PermissionsTable::class)
            ->call('deletePermission', $permission->uuid)
            ->call('confirmDelete');

        $this->assertModelMissing($permission);
    }
}
