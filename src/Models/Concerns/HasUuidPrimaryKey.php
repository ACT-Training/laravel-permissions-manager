<?php

namespace ACTTraining\PermissionsManager\Models\Concerns;

/**
 * Trait for models using UUID as primary key.
 *
 * Provides consistent UUID handling for Permission and Role models.
 * Works alongside Laravel's HasUuids trait.
 */
trait HasUuidPrimaryKey
{
    /**
     * Initialize the trait.
     */
    public function initializeHasUuidPrimaryKey(): void
    {
        $this->incrementing = false;
        $this->keyType = 'string';
    }

    /**
     * Get the primary key for the model.
     */
    public function getKeyName(): string
    {
        return 'uuid';
    }
}
