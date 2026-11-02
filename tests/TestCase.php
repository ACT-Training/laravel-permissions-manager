<?php

namespace ACTTraining\PermissionsManager\Tests;

use ACTTraining\PermissionsManager\Enums\PermissionCategoryEnum;
use ACTTraining\PermissionsManager\Models\Permission;
use ACTTraining\PermissionsManager\Models\Role;
use ACTTraining\PermissionsManager\PermissionsManagerServiceProvider;
use ACTTraining\PermissionsManager\Tests\Fixtures\User;
use ACTTraining\QueryBuilder\QueryBuilderServiceProvider;
use BladeUI\Icons\BladeIconsServiceProvider;
use Flux\FluxServiceProvider;
use Illuminate\Support\Facades\Blade;
use Livewire\LivewireServiceProvider;
use Orchestra\Testbench\TestCase as Orchestra;
use secondnetwork\TablerIcons\BladeTablerIconsServiceProvider;
use Spatie\Permission\PermissionServiceProvider;

abstract class TestCase extends Orchestra
{
    protected function setUp(): void
    {
        parent::setUp();

        // Flux Pro is not installable in CI, so its listbox select is stubbed.
        Blade::anonymousComponentPath(__DIR__ . '/stubs/flux', 'flux');
    }

    protected function getPackageProviders($app)
    {
        return [
            LivewireServiceProvider::class,
            FluxServiceProvider::class,
            BladeIconsServiceProvider::class,
            BladeTablerIconsServiceProvider::class,
            QueryBuilderServiceProvider::class,
            PermissionServiceProvider::class,
            PermissionsManagerServiceProvider::class,
        ];
    }

    protected function defineEnvironment($app)
    {
        $app['config']->set('app.key', 'base64:' . base64_encode(str_repeat('a', 32)));
        $app['config']->set('database.default', 'testing');
        $app['config']->set('database.connections.testing', [
            'driver' => 'sqlite',
            'database' => ':memory:',
            'prefix' => '',
            'foreign_key_constraints' => true,
        ]);

        $app['config']->set('auth.providers.users.model', User::class);

        $app['config']->set('permission.models.permission', Permission::class);
        $app['config']->set('permission.models.role', Role::class);
        $app['config']->set('permission.column_names.model_morph_key', 'model_uuid');

        $app['config']->set('permissions-manager.category_enum', PermissionCategoryEnum::class);
        $app['config']->set('permissions-manager.models.permission', Permission::class);
        $app['config']->set('permissions-manager.models.role', Role::class);
        $app['config']->set('permissions-manager.models.user', User::class);
    }

    protected function defineDatabaseMigrations()
    {
        $this->loadMigrationsFrom(__DIR__ . '/database/migrations');
        $this->loadMigrationsFrom(__DIR__ . '/../database/migrations');
    }
}
