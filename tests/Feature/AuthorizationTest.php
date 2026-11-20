<?php

namespace ACTTraining\PermissionsManager\Tests\Feature;

use ACTTraining\PermissionsManager\Livewire\PermissionsAndRoles;
use ACTTraining\PermissionsManager\Livewire\PermissionsTable;
use ACTTraining\PermissionsManager\Livewire\RolesTable;
use ACTTraining\PermissionsManager\Tests\Fixtures\IntegerKeys\Permission;
use ACTTraining\PermissionsManager\Tests\Fixtures\IntegerKeys\Role;
use ACTTraining\PermissionsManager\Tests\Fixtures\Policies\AbilityListPolicy;
use ACTTraining\PermissionsManager\Tests\Fixtures\Policies\DenyAllPolicy;
use ACTTraining\PermissionsManager\Tests\IntegerKeyTestCase;
use Illuminate\Contracts\Auth\Access\Gate as GateContract;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Gate;
use Livewire\Livewire;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\Attributes\Test;

class AuthorizationTest extends IntegerKeyTestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        AbilityListPolicy::$denied = [];
        config()->set('permissions-manager.authorization.enabled', true);
        Gate::policy(Role::class, AbilityListPolicy::class);
        Gate::policy(Permission::class, AbilityListPolicy::class);

        $this->actingAs($this->createUser());
    }

    /**
     * Each action, the ability it needs, and how to call it.
     *
     * @return array<string, array{string, string, string}>
     */
    public static function actions(): array
    {
        return [
            'open create role' => ['roles', 'create', 'createRole'],
            'save new role' => ['roles', 'create', 'saveNew'],
            'duplicate role' => ['roles', 'create', 'duplicateRole'],
            'open edit role' => ['roles', 'update', 'editRole'],
            'save existing role' => ['roles', 'update', 'saveExisting'],
            'delete role' => ['roles', 'delete', 'deleteRole'],
            'confirm delete role' => ['roles', 'delete', 'confirmDelete'],
            'open create permission' => ['permissions', 'create', 'createPermission'],
            'save new permission' => ['permissions', 'create', 'saveNew'],
            'open edit permission' => ['permissions', 'update', 'editPermission'],
            'save existing permission' => ['permissions', 'update', 'saveExisting'],
            'delete permission' => ['permissions', 'delete', 'deletePermission'],
            'confirm delete permission' => ['permissions', 'delete', 'confirmDelete'],
        ];
    }

    #[Test]
    #[DataProvider('actions')]
    public function an_action_is_allowed_when_the_policy_allows_it(string $table, string $ability, string $action): void
    {
        $this->callAction($table, $action)->assertOk()->assertHasNoErrors();
    }

    #[Test]
    #[DataProvider('actions')]
    public function an_action_is_forbidden_when_the_policy_denies_it(string $table, string $ability, string $action): void
    {
        $component = $this->prepareAction($table, $action);
        AbilityListPolicy::$denied = [$ability];

        $this->runAction($component, $table, $action)->assertForbidden();
    }

    #[Test]
    public function a_denied_save_changes_nothing(): void
    {
        $role = Role::create(['name' => 'Editor', 'guard_name' => 'web']);
        $component = Livewire::test(RolesTable::class)->call('editRole', $role->id);
        AbilityListPolicy::$denied = ['update'];

        $component->set('form.name', 'Renamed')->call('saveRole')->assertForbidden();

        $this->assertSame('Editor', $role->fresh()->name);
    }

    #[Test]
    public function a_denied_delete_keeps_the_record(): void
    {
        $permission = Permission::create(['name' => 'edit posts', 'guard_name' => 'web']);
        $component = Livewire::test(PermissionsTable::class)->call('deletePermission', $permission->id);
        AbilityListPolicy::$denied = ['delete'];

        $component->call('confirmDelete')->assertForbidden();

        $this->assertModelExists($permission);
    }

    #[Test]
    public function the_tables_are_forbidden_without_view_any(): void
    {
        AbilityListPolicy::$denied = ['viewAny'];

        Livewire::test(RolesTable::class)->assertForbidden();
        Livewire::test(PermissionsTable::class)->assertForbidden();
        Livewire::test(PermissionsAndRoles::class)->assertForbidden();
    }

    #[Test]
    public function view_any_is_checked_on_every_request_not_only_the_first(): void
    {
        $component = Livewire::test(RolesTable::class)->assertOk();
        AbilityListPolicy::$denied = ['viewAny'];

        $component->call('$refresh')->assertForbidden();
    }

    #[Test]
    public function the_page_only_shows_the_tables_the_user_may_view(): void
    {
        Gate::policy(Permission::class, DenyAllPolicy::class);

        Livewire::test(PermissionsAndRoles::class)
            ->assertOk()
            ->assertSeeLivewire(RolesTable::class)
            ->assertDontSeeLivewire(PermissionsTable::class);
    }

    #[Test]
    public function the_create_buttons_are_hidden_without_create(): void
    {
        Livewire::test(PermissionsAndRoles::class)
            ->assertSee('Create a Role')
            ->assertSee('Create a Permission');

        AbilityListPolicy::$denied = ['create'];

        Livewire::test(PermissionsAndRoles::class)
            ->assertDontSee('Create a Role')
            ->assertDontSee('Create a Permission');
    }

    #[Test]
    public function the_role_menu_hides_the_actions_the_user_may_not_take(): void
    {
        $role = Role::create(['name' => 'Editor', 'guard_name' => 'web']);

        Livewire::test(RolesTable::class)
            ->assertSeeHtml("editRole('{$role->id}')")
            ->assertSeeHtml("duplicateRole('{$role->id}')")
            ->assertSeeHtml("deleteRole('{$role->id}')");

        AbilityListPolicy::$denied = ['update', 'delete'];

        Livewire::test(RolesTable::class)
            ->assertDontSeeHtml("editRole('{$role->id}')")
            ->assertSeeHtml("duplicateRole('{$role->id}')")
            ->assertDontSeeHtml("deleteRole('{$role->id}')");

        AbilityListPolicy::$denied = ['create', 'update', 'delete'];

        Livewire::test(RolesTable::class)
            ->assertDontSeeHtml("duplicateRole('{$role->id}')")
            ->assertDontSeeHtml('Actions for Editor role');
    }

    #[Test]
    public function the_permission_menu_hides_the_actions_the_user_may_not_take(): void
    {
        $permission = Permission::create(['name' => 'edit posts', 'guard_name' => 'web']);

        Livewire::test(PermissionsTable::class)
            ->assertSeeHtml("editPermission('{$permission->id}')")
            ->assertSeeHtml("deletePermission('{$permission->id}')");

        AbilityListPolicy::$denied = ['delete'];

        Livewire::test(PermissionsTable::class)
            ->assertSeeHtml("editPermission('{$permission->id}')")
            ->assertDontSeeHtml("deletePermission('{$permission->id}')");

        AbilityListPolicy::$denied = ['update', 'delete'];

        Livewire::test(PermissionsTable::class)
            ->assertDontSeeHtml("editPermission('{$permission->id}')")
            ->assertDontSeeHtml('Actions for edit posts');
    }

    #[Test]
    public function a_model_without_a_policy_is_denied(): void
    {
        $this->forgetPolicies();

        Livewire::test(RolesTable::class)->assertForbidden();
    }

    #[Test]
    public function gate_before_can_allow_a_model_without_a_policy(): void
    {
        $this->forgetPolicies();
        Gate::before(fn () => true);

        Livewire::test(RolesTable::class)->assertOk()->call('createRole')->assertOk();
    }

    #[Test]
    public function guests_are_denied(): void
    {
        auth()->logout();

        Livewire::test(RolesTable::class)->assertForbidden();
    }

    #[Test]
    public function nothing_is_checked_when_authorisation_is_disabled(): void
    {
        config()->set('permissions-manager.authorization.enabled', false);
        $this->forgetPolicies();
        auth()->logout();
        $role = Role::create(['name' => 'Editor', 'guard_name' => 'web']);

        Livewire::test(PermissionsAndRoles::class)
            ->assertOk()
            ->assertSee('Create a Role')
            ->assertSee('Create a Permission');

        Livewire::test(RolesTable::class)
            ->assertSeeHtml("editRole('{$role->id}')")
            ->assertSeeHtml("deleteRole('{$role->id}')")
            ->call('editRole', $role->id)
            ->set('form.name', 'Renamed')
            ->call('saveRole')
            ->assertHasNoErrors();

        $this->assertSame('Renamed', $role->fresh()->name);
    }

    /**
     * Mount the table and, for actions that need it, put it in the state the action expects.
     */
    protected function prepareAction(string $table, string $action): mixed
    {
        $isRoles = $table === 'roles';
        $record = $isRoles
            ? Role::create(['name' => 'Editor', 'guard_name' => 'web'])
            : Permission::create(['name' => 'edit posts', 'guard_name' => 'web']);
        $component = Livewire::test($isRoles ? RolesTable::class : PermissionsTable::class);

        return match ($action) {
            'saveNew' => $component->call($isRoles ? 'createRole' : 'createPermission')
                ->set('form.name', 'New one'),
            'saveExisting' => $component->call($isRoles ? 'editRole' : 'editPermission', $record->id),
            'confirmDelete' => $component->call($isRoles ? 'deleteRole' : 'deletePermission', $record->id),
            default => $component,
        };
    }

    protected function runAction(mixed $component, string $table, string $action): mixed
    {
        $isRoles = $table === 'roles';
        $model = $isRoles ? Role::class : Permission::class;
        $key = $model::query()->value('id');

        return match ($action) {
            'saveNew', 'saveExisting' => $component->call($isRoles ? 'saveRole' : 'savePermission'),
            'confirmDelete' => $component->call('confirmDelete'),
            'createRole', 'createPermission' => $component->call($action),
            default => $component->call($action, $key),
        };
    }

    protected function callAction(string $table, string $action): mixed
    {
        return $this->runAction($this->prepareAction($table, $action), $table, $action);
    }

    protected function forgetPolicies(): void
    {
        $this->app->forgetInstance(GateContract::class);
        Gate::clearResolvedInstances();
    }
}
