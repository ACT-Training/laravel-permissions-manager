<?php

namespace ACTTraining\PermissionsManager\Livewire\Concerns;

use ACTTraining\PermissionsManager\Support\Authorization;
use Illuminate\Auth\Access\AuthorizationException;
use Illuminate\Database\Eloquent\Model;

trait AuthorizesActions
{
    /**
     * Check an ability against a model class, or against one record when a key is given.
     *
     * The record is only loaded when authorisation is enabled, so the disabled
     * path runs no extra queries.
     *
     * @param  class-string<Model>  $modelClass
     *
     * @throws AuthorizationException
     */
    protected function authorizeAction(string $ability, string $modelClass, int|string|null $key = null): void
    {
        if (! Authorization::enabled()) {
            return;
        }

        Authorization::authorize($ability, $key === null ? $modelClass : $modelClass::findOrFail($key));
    }
}
