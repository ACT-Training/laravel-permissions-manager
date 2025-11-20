<?php

namespace ACTTraining\PermissionsManager\Contracts;

/**
 * Interface for enums that provide colour information.
 *
 * Used primarily for Permission Category Enums to provide
 * colour-coded badges in the permissions management UI.
 */
interface HasColor
{
    /**
     * Get the Tailwind colour class for this enum value.
     *
     * Should return a valid Tailwind/Flux colour name such as:
     * 'pink', 'red', 'orange', 'yellow', 'green', 'blue', 'purple', 'gray'
     *
     * @return string The colour name (without any 'bg-' or other prefixes)
     */
    public function color(): string;
}
