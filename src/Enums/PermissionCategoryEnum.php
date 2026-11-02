<?php

namespace ACTTraining\PermissionsManager\Enums;

use ACTTraining\PermissionsManager\Contracts\HasColor;
use ACTTraining\PermissionsManager\Contracts\HasLabel;

/**
 * Default Permission Category Enum.
 *
 * Provides basic categorisation of permissions with colour-coded badges
 * for visual grouping in the permissions management interface.
 *
 * Applications can create their own implementation and specify it in the
 * config/permissions-manager.php file. The stored category value is the
 * enum's backing value.
 */
enum PermissionCategoryEnum: string implements HasColor, HasLabel
{
    case Admin = 'admin';
    case Users = 'users';
    case Content = 'content';
    case Settings = 'settings';
    case Other = 'other';

    /**
     * Get the display label for the category.
     */
    public function label(): string
    {
        return match ($this) {
            self::Admin => 'Admin',
            self::Users => 'Users',
            self::Content => 'Content',
            self::Settings => 'Settings',
            self::Other => 'Other',
        };
    }

    /**
     * Get the Tailwind colour name for the category badge.
     */
    public function color(): string
    {
        return match ($this) {
            self::Admin => 'pink',
            self::Users => 'orange',
            self::Content => 'blue',
            self::Settings => 'purple',
            self::Other => 'gray',
        };
    }
}
