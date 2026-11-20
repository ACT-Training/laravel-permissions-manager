<?php

namespace ACTTraining\PermissionsManager\Tests\Fixtures\Policies;

/**
 * A policy that denies every ability.
 */
class DenyAllPolicy extends AbilityListPolicy
{
    protected function allows(string $ability): bool
    {
        return false;
    }
}
