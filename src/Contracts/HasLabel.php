<?php

namespace ACTTraining\PermissionsManager\Contracts;

/**
 * Optional interface for native category enums that provide their own display label.
 *
 * Native backed enums that do not implement this interface (or a public label()
 * method) are labelled from their case name, e.g. "DataKpis" becomes "Data Kpis".
 */
interface HasLabel
{
    /**
     * Get the human-readable label for this enum value.
     */
    public function label(): string;
}
