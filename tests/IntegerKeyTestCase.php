<?php

namespace ACTTraining\PermissionsManager\Tests;

use ACTTraining\PermissionsManager\Tests\Fixtures\IntegerKeys\Permission;
use ACTTraining\PermissionsManager\Tests\Fixtures\IntegerKeys\Role;
use ACTTraining\PermissionsManager\Tests\Fixtures\IntegerKeys\User;

/**
 * Runs the package against spatie's standard integer-id tables and
 * app-side models, as a non-UUID app (such as Pipeline 2) would.
 */
abstract class IntegerKeyTestCase extends TestCase
{
    protected function defineEnvironment($app)
    {
        parent::defineEnvironment($app);

        $app['config']->set('auth.providers.users.model', User::class);

        $app['config']->set('permission.models.permission', Permission::class);
        $app['config']->set('permission.models.role', Role::class);
        $app['config']->set('permission.column_names.model_morph_key', 'model_id');

        $app['config']->set('permissions-manager.models.permission', Permission::class);
        $app['config']->set('permissions-manager.models.role', Role::class);
        $app['config']->set('permissions-manager.models.user', User::class);
    }

    /**
     * Create an integer-id user.
     */
    protected function createUser(): User
    {
        static $count = 0;
        $count++;

        return User::create(['name' => "User {$count}", 'email' => "user{$count}@example.com"]);
    }

    protected function defineDatabaseMigrations()
    {
        $this->loadMigrationsFrom(__DIR__ . '/database/integer-key-migrations');
        $this->loadMigrationsFrom(__DIR__ . '/../database/migrations');
    }
}
