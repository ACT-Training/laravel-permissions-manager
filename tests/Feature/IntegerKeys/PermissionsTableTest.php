<?php

namespace ACTTraining\PermissionsManager\Tests\Feature\IntegerKeys;

use ACTTraining\PermissionsManager\Livewire\PermissionsAndRoles;
use ACTTraining\PermissionsManager\Livewire\PermissionsTable;
use ACTTraining\PermissionsManager\Tests\Fixtures\IntegerKeys\Permission;
use ACTTraining\PermissionsManager\Tests\Fixtures\IntegerKeys\Role;
use ACTTraining\PermissionsManager\Tests\IntegerKeyTestCase;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Livewire;
use PHPUnit\Framework\Attributes\Test;

class PermissionsTableTest extends IntegerKeyTestCase
{
    use RefreshDatabase;

    #[Test]
    public function the_table_counts_users_holding_a_permission_directly_or_through_a_role(): void
    {
        $permission = Permission::create(['name' => 'edit posts', 'guard_name' => 'web']);
        $role = Role::create(['name' => 'Editor', 'guard_name' => 'web']);
        $role->givePermissionTo($permission);

        $this->createUser()->givePermissionTo($permission);
        $this->createUser()->assignRole($role);
        $both = $this->createUser();
        $both->givePermissionTo($permission);
        $both->assignRole($role);

        $component = Livewire::test(PermissionsTable::class)
            ->assertOk()
            ->assertSee('edit posts')
            ->assertSeeHtml("editPermission('{$permission->id}')");

        $row = $component->instance()->rowsQuery->first();
        $this->assertSame(3, (int) $row->users_count);
    }

    #[Test]
    public function the_page_renders_both_tables(): void
    {
        Role::create(['name' => 'Editor', 'guard_name' => 'web']);
        Permission::create(['name' => 'edit posts', 'guard_name' => 'web']);

        Livewire::test(PermissionsAndRoles::class)
            ->assertOk()
            ->assertSee('Create a Role')
            ->assertSee('Create a Permission');
    }

    #[Test]
    public function a_permission_can_be_edited_and_saved_with_roles(): void
    {
        $permission = Permission::create(['name' => 'edit posts', 'guard_name' => 'web']);
        $editor = Role::create(['name' => 'Editor', 'guard_name' => 'web']);
        $writer = Role::create(['name' => 'Writer', 'guard_name' => 'web']);
        $editor->givePermissionTo($permission);

        Livewire::test(PermissionsTable::class)
            ->call('editPermission', $permission->id)
            ->assertSet('editingPermission', $permission->id)
            ->assertSet('form.roles', [(string) $editor->id])
            ->set('form.name', 'edit all posts')
            ->set('form.category', 'admin')
            ->set('form.roles', [(string) $writer->id])
            ->call('savePermission')
            ->assertHasNoErrors()
            ->assertSet('showPermissionModal', false);

        $permission = $permission->fresh();
        $this->assertSame('edit all posts', $permission->name);
        $this->assertSame('admin', $permission->category);
        $this->assertSame(['Writer'], $permission->roles->pluck('name')->all());
    }

    #[Test]
    public function a_permission_can_be_created_with_roles(): void
    {
        $role = Role::create(['name' => 'Editor', 'guard_name' => 'web']);

        Livewire::test(PermissionsTable::class)
            ->call('createPermission')
            ->set('form.name', 'publish posts')
            ->set('form.roles', [(string) $role->id])
            ->call('savePermission')
            ->assertHasNoErrors();

        $this->assertTrue($role->fresh()->hasPermissionTo('publish posts'));
    }

    #[Test]
    public function saving_rejects_a_role_id_that_does_not_exist(): void
    {
        Livewire::test(PermissionsTable::class)
            ->call('createPermission')
            ->set('form.name', 'publish posts')
            ->set('form.roles', ['999'])
            ->call('savePermission')
            ->assertHasErrors(['form.roles.0']);
    }

    #[Test]
    public function an_unassigned_permission_can_be_deleted(): void
    {
        $permission = Permission::create(['name' => 'edit posts', 'guard_name' => 'web']);

        Livewire::test(PermissionsTable::class)
            ->call('deletePermission', $permission->id)
            ->assertSet('showDeleteModal', true)
            ->call('confirmDelete')
            ->assertSet('showDeleteModal', false);

        $this->assertModelMissing($permission);
    }

    #[Test]
    public function a_permission_assigned_to_a_user_cannot_be_deleted(): void
    {
        $permission = Permission::create(['name' => 'edit posts', 'guard_name' => 'web']);
        $this->createUser()->givePermissionTo($permission);

        Livewire::test(PermissionsTable::class)
            ->call('deletePermission', $permission->id)
            ->call('confirmDelete');

        $this->assertModelExists($permission);
    }
}
