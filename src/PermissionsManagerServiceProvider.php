<?php

namespace ACTTraining\PermissionsManager;

use ACTTraining\PermissionsManager\Livewire\PermissionsAndRoles;
use ACTTraining\PermissionsManager\Livewire\PermissionsTable;
use ACTTraining\PermissionsManager\Livewire\RolesTable;
use Illuminate\Support\ServiceProvider;
use Livewire\Livewire;

class PermissionsManagerServiceProvider extends ServiceProvider
{
    /**
     * Register services.
     */
    public function register(): void
    {
        $this->mergeConfigFrom(
            __DIR__ . '/../config/permissions-manager.php',
            'permissions-manager'
        );
    }

    /**
     * Bootstrap services.
     */
    public function boot(): void
    {
        $this->loadViewsFrom(__DIR__ . '/../resources/views', 'permissions-manager');

        // Register Livewire components
        Livewire::component('permissions-manager::permissions-table', PermissionsTable::class);
        Livewire::component('permissions-manager::roles-table', RolesTable::class);
        Livewire::component('permissions-manager::permissions-and-roles', PermissionsAndRoles::class);

        // Publishing
        if ($this->app->runningInConsole()) {
            // Publish config
            $this->publishes([
                __DIR__ . '/../config/permissions-manager.php' => config_path('permissions-manager.php'),
            ], 'permissions-manager-config');

            // Publish migrations
            $this->publishes([
                __DIR__ . '/../database/migrations' => database_path('migrations'),
            ], 'permissions-manager-migrations');

            // Publish views
            $this->publishes([
                __DIR__ . '/../resources/views' => resource_path('views/vendor/permissions-manager'),
            ], 'permissions-manager-views');
        }
    }
}
