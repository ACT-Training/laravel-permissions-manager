<?php

namespace ACTTraining\PermissionsManager\Support;

use Illuminate\Auth\Access\AuthorizationException;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Gate;

/**
 * Optional Gate checks for the package's actions.
 *
 * When `permissions-manager.authorization.enabled` is false (the default)
 * every check passes, so the package behaves exactly as before. When it is
 * true, each action asks Laravel's Gate using the standard policy abilities
 * (viewAny, create, update, delete) against the configured role or
 * permission model.
 */
class Authorization
{
    /**
     * Whether Gate checks are switched on.
     */
    public static function enabled(): bool
    {
        return (bool) config('permissions-manager.authorization.enabled', false);
    }

    /**
     * Whether the current user may perform the ability (always true when disabled).
     *
     * @param  Model|class-string<Model>  $target
     */
    public static function allows(string $ability, Model|string $target): bool
    {
        return ! static::enabled() || Gate::allows($ability, $target);
    }

    /**
     * Throw a 403 when the current user may not perform the ability (no-op when disabled).
     *
     * @param  Model|class-string<Model>  $target
     *
     * @throws AuthorizationException
     */
    public static function authorize(string $ability, Model|string $target): void
    {
        if (static::enabled()) {
            Gate::authorize($ability, $target);
        }
    }
}
