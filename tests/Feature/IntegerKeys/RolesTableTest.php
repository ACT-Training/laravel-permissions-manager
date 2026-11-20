<?php

namespace ACTTraining\PermissionsManager\Tests\Feature\IntegerKeys;

use ACTTraining\PermissionsManager\Livewire\RolesTable;
use ACTTraining\PermissionsManager\Tests\Fixtures\IntegerKeys\Permission;
use ACTTraining\PermissionsManager\Tests\Fixtures\IntegerKeys\Role;
use ACTTraining\PermissionsManager\Tests\IntegerKeyTestCase;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Livewire;
use PHPUnit\Framework\Attributes\Test;

class RolesTableTest extends IntegerKeyTestCase
{
    use RefreshDatabase;

    #[Test]
    public function the_table_lists_roles_with_their_user_and_permission_counts(): void
    {
        $role = Role::create(['name' => 'Editor', 'guard_name' => 'web']);
        $role->givePermissionTo(Permission::create(['name' => 'edit posts', 'guard_name' => 'web']));
        $this->createUser()->assignRole($role);
        $this->createUser()->assignRole($role);

        $component = Livewire::test(RolesTable::class)
            ->assertOk()
            ->assertSee('Editor')
            ->assertSeeHtml("editRole('{$role->id}')");

        $row = $component->instance()->rowsQuery->first();
        $this->assertSame(2, (int) $row->users_count);
        $this->assertSame(1, (int) $row->permissions_count);
    }

    #[Test]
    public function a_role_can_be_edited_and_saved_with_permissions(): void
    {
        $role = Role::create(['name' => 'Editor', 'guard_name' => 'web']);
        $edit = Permission::create(['name' => 'edit posts', 'guard_name' => 'web']);
        $publish = Permission::create(['name' => 'publish posts', 'guard_name' => 'web']);
        $role->givePermissionTo($edit);

        Livewire::test(RolesTable::class)
            ->call('editRole', $role->id)
            ->assertSet('editingRole', $role->id)
            ->assertSet('form.permissions', [(string) $edit->id])
            ->set('form.name', 'Content Editor')
            ->set('form.description', 'Writes content')
            ->set('form.permissions', [(string) $edit->id, (string) $publish->id])
            ->call('saveRole')
            ->assertHasNoErrors()
            ->assertSet('showRoleModal', false);

        $role = $role->fresh();
        $this->assertSame('Content Editor', $role->name);
        $this->assertSame('Writes content', $role->description);
        $this->assertEqualsCanonicalizing(['edit posts', 'publish posts'], $role->permissions->pluck('name')->all());
    }

    #[Test]
    public function a_role_can_be_created_with_permissions(): void
    {
        $permission = Permission::create(['name' => 'edit posts', 'guard_name' => 'web']);

        Livewire::test(RolesTable::class)
            ->call('createRole')
            ->set('form.name', 'Writer')
            ->set('form.permissions', [(string) $permission->id])
            ->call('saveRole')
            ->assertHasNoErrors();

        $role = Role::where('name', 'Writer')->firstOrFail();
        $this->assertTrue($role->hasPermissionTo('edit posts'));
    }

    #[Test]
    public function saving_rejects_a_permission_id_that_does_not_exist(): void
    {
        Livewire::test(RolesTable::class)
            ->call('createRole')
            ->set('form.name', 'Writer')
            ->set('form.permissions', ['999'])
            ->call('saveRole')
            ->assertHasErrors(['form.permissions.0']);
    }

    #[Test]
    public function a_role_name_must_be_unique_but_may_be_kept_when_editing(): void
    {
        $role = Role::create(['name' => 'Editor', 'guard_name' => 'web']);
        Role::create(['name' => 'Viewer', 'guard_name' => 'web']);

        Livewire::test(RolesTable::class)
            ->call('editRole', $role->id)
            ->call('saveRole')
            ->assertHasNoErrors()
            ->call('editRole', $role->id)
            ->set('form.name', 'Viewer')
            ->call('saveRole')
            ->assertHasErrors(['form.name']);
    }

    #[Test]
    public function a_role_can_be_duplicated_with_its_permissions(): void
    {
        $role = Role::create(['name' => 'Editor', 'guard_name' => 'web', 'is_protected' => true]);
        $role->givePermissionTo(Permission::create(['name' => 'edit posts', 'guard_name' => 'web']));

        Livewire::test(RolesTable::class)
            ->call('duplicateRole', $role->id);

        $copy = Role::where('name', 'Editor (Copy)')->firstOrFail();
        $this->assertFalse($copy->is_protected);
        $this->assertTrue($copy->hasPermissionTo('edit posts'));
    }

    #[Test]
    public function an_unassigned_role_can_be_deleted(): void
    {
        $role = Role::create(['name' => 'Editor', 'guard_name' => 'web']);

        Livewire::test(RolesTable::class)
            ->call('deleteRole', $role->id)
            ->assertSet('showDeleteModal', true)
            ->call('confirmDelete')
            ->assertSet('showDeleteModal', false);

        $this->assertModelMissing($role);
    }

    #[Test]
    public function a_role_assigned_to_a_user_cannot_be_deleted(): void
    {
        $role = Role::create(['name' => 'Editor', 'guard_name' => 'web']);
        $this->createUser()->assignRole($role);

        Livewire::test(RolesTable::class)
            ->call('deleteRole', $role->id)
            ->call('confirmDelete');

        $this->assertModelExists($role);
    }

    #[Test]
    public function a_protected_role_cannot_be_deleted_or_renamed(): void
    {
        $role = Role::create(['name' => 'Admin', 'guard_name' => 'web', 'is_protected' => true]);

        Livewire::test(RolesTable::class)
            ->call('editRole', $role->id)
            ->assertSet('editingRoleIsProtected', true)
            ->set('form.name', 'Super Admin')
            ->call('saveRole')
            ->assertHasErrors(['form.name'])
            ->call('deleteRole', $role->id)
            ->call('confirmDelete');

        $this->assertModelExists($role);
        $this->assertSame('Admin', $role->fresh()->name);
    }
}
