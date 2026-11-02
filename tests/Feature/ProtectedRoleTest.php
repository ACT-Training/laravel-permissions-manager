<?php

namespace ACTTraining\PermissionsManager\Tests\Feature;

use ACTTraining\PermissionsManager\Livewire\RolesTable;
use ACTTraining\PermissionsManager\Models\Role;
use ACTTraining\PermissionsManager\Tests\TestCase;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Livewire;
use PHPUnit\Framework\Attributes\Test;

class ProtectedRoleTest extends TestCase
{
    use RefreshDatabase;

    #[Test]
    public function a_protected_role_cannot_be_renamed(): void
    {
        $role = Role::create(['name' => 'Admin', 'guard_name' => 'web', 'is_protected' => true]);

        Livewire::test(RolesTable::class)
            ->call('editRole', $role->uuid)
            ->assertSet('editingRoleIsProtected', true)
            ->set('form.name', 'Super Admin')
            ->call('saveRole')
            ->assertHasErrors(['form.name']);

        $this->assertSame('Admin', $role->fresh()->name);
    }

    #[Test]
    public function a_protected_role_can_still_have_its_description_changed(): void
    {
        $role = Role::create(['name' => 'Admin', 'guard_name' => 'web', 'is_protected' => true]);

        Livewire::test(RolesTable::class)
            ->call('editRole', $role->uuid)
            ->set('form.description', 'Full access')
            ->call('saveRole')
            ->assertHasNoErrors();

        $role->refresh();
        $this->assertSame('Admin', $role->name);
        $this->assertSame('Full access', $role->description);
    }

    #[Test]
    public function an_unprotected_role_can_be_renamed(): void
    {
        $role = Role::create(['name' => 'Editor', 'guard_name' => 'web']);

        Livewire::test(RolesTable::class)
            ->call('editRole', $role->uuid)
            ->assertSet('editingRoleIsProtected', false)
            ->set('form.name', 'Content Editor')
            ->call('saveRole')
            ->assertHasNoErrors();

        $this->assertSame('Content Editor', $role->fresh()->name);
    }

    #[Test]
    public function a_protected_role_cannot_be_deleted(): void
    {
        $role = Role::create(['name' => 'Admin', 'guard_name' => 'web', 'is_protected' => true]);

        Livewire::test(RolesTable::class)
            ->call('deleteRole', $role->uuid)
            ->call('confirmDelete');

        $this->assertModelExists($role);
    }
}
