<?php

namespace ACTTraining\PermissionsManager\Tests\Fixtures\Policies;

use Illuminate\Contracts\Auth\Authenticatable;
use Illuminate\Database\Eloquent\Model;

/**
 * A policy that allows every ability except those listed in $denied.
 */
class AbilityListPolicy
{
    /**
     * @var array<int, string>
     */
    public static array $denied = [];

    public function viewAny(Authenticatable $user): bool
    {
        return $this->allows('viewAny');
    }

    public function create(Authenticatable $user): bool
    {
        return $this->allows('create');
    }

    public function update(Authenticatable $user, Model $model): bool
    {
        return $this->allows('update');
    }

    public function delete(Authenticatable $user, Model $model): bool
    {
        return $this->allows('delete');
    }

    protected function allows(string $ability): bool
    {
        return ! in_array($ability, static::$denied, true);
    }
}
