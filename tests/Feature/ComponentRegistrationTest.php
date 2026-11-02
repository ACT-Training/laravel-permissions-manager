<?php

namespace ACTTraining\PermissionsManager\Tests\Feature;

use ACTTraining\PermissionsManager\Livewire\PermissionsAndRoles;
use ACTTraining\PermissionsManager\Livewire\PermissionsTable;
use ACTTraining\PermissionsManager\Livewire\RolesTable;
use ACTTraining\PermissionsManager\Tests\TestCase;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Livewire;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\Attributes\Test;

class ComponentRegistrationTest extends TestCase
{
    use RefreshDatabase;

    /**
     * @return array<string, array{string, class-string}>
     */
    public static function components(): array
    {
        return [
            'roles table' => ['permissions-manager::roles-table', RolesTable::class],
            'permissions table' => ['permissions-manager::permissions-table', PermissionsTable::class],
            'permissions and roles page' => ['permissions-manager::permissions-and-roles', PermissionsAndRoles::class],
        ];
    }

    #[Test]
    #[DataProvider('components')]
    public function it_resolves_each_component_by_its_public_name(string $name, string $class): void
    {
        Livewire::test($name)->assertOk();

        $this->assertInstanceOf($class, Livewire::test($name)->instance());
    }

    #[Test]
    public function the_page_renders_both_tables_from_its_blade_tags(): void
    {
        Livewire::test(PermissionsAndRoles::class)
            ->assertOk()
            ->assertSeeLivewire(RolesTable::class)
            ->assertSeeLivewire(PermissionsTable::class);
    }
}
