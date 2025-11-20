<?php

namespace ACTTraining\PermissionsManager\Enums;

use ACTTraining\PermissionsManager\Contracts\HasColor;
use Livewire\Wireable;
use Spatie\Enum\Laravel\Enum;

/**
 * Default Permission Category Enum.
 *
 * Provides basic categorisation of permissions with colour-coded badges
 * for visual grouping in the permissions management interface.
 *
 * Applications can extend this enum or create their own implementation
 * by specifying a different enum in the config/permissions-manager.php file.
 *
 * @method static self admin()
 * @method static self users()
 * @method static self content()
 * @method static self settings()
 * @method static self other()
 */
final class PermissionCategoryEnum extends Enum implements HasColor, Wireable
{
    /**
     * Get the display labels for each category.
     *
     * @return array<string, string>
     */
    protected static function labels(): array
    {
        return [
            'admin' => 'Admin',
            'users' => 'Users',
            'content' => 'Content',
            'settings' => 'Settings',
            'other' => 'Other',
        ];
    }

    /**
     * Get the Tailwind colour class for the category badge.
     *
     * Returns colour names that are used with Flux badge components
     * to provide visual categorisation of permissions.
     *
     * @return string Tailwind colour class name
     */
    public function color(): string
    {
        return match ($this) {
            self::admin() => 'pink',
            self::users() => 'orange',
            self::content() => 'blue',
            self::settings() => 'purple',
            self::other() => 'gray',
        };
    }
}
