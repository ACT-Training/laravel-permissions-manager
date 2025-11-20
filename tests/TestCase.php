<?php

namespace ACTTraining\PermissionsManager\Tests;

use ACTTraining\PermissionsManager\PermissionsManagerServiceProvider;
use Orchestra\Testbench\TestCase as Orchestra;

abstract class TestCase extends Orchestra
{
    protected function setUp(): void
    {
        parent::setUp();

        // Additional test setup can go here
    }

    protected function getPackageProviders($app)
    {
        return [
            PermissionsManagerServiceProvider::class,
        ];
    }

    protected function getEnvironmentSetUp($app)
    {
        // Set up test environment configuration
        config()->set('database.default', 'testing');
        config()->set('database.connections.testing', [
            'driver' => 'sqlite',
            'database' => ':memory:',
            'prefix' => '',
        ]);

        // Set up permissions-manager config
        config()->set('permissions-manager.category_enum', \ACTTraining\PermissionsManager\Enums\PermissionCategoryEnum::class);
        config()->set('permissions-manager.models.permission', \ACTTraining\PermissionsManager\Models\Permission::class);
        config()->set('permissions-manager.models.role', \ACTTraining\PermissionsManager\Models\Role::class);
    }
}
