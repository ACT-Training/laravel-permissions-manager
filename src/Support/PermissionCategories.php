<?php

namespace ACTTraining\PermissionsManager\Support;

use ACTTraining\PermissionsManager\Contracts\HasColor;
use ACTTraining\PermissionsManager\Enums\PermissionCategoryEnum;
use BackedEnum;
use Illuminate\Support\Str;

/**
 * Reads the configured permission category enum.
 *
 * Supports native PHP backed enums (recommended) and, for backwards
 * compatibility, spatie/enum style classes that expose static toArray()
 * and make() methods and a label property. The package itself does not
 * depend on any enum package.
 */
class PermissionCategories
{
    /**
     * Get the configured category enum class.
     *
     * @return class-string
     */
    public static function enumClass(): string
    {
        return config('permissions-manager.category_enum') ?? PermissionCategoryEnum::class;
    }

    /**
     * Get every category as stored value => display label.
     *
     * @return array<string, string>
     */
    public static function options(): array
    {
        $enumClass = static::enumClass();

        if (is_subclass_of($enumClass, BackedEnum::class)) {
            return collect($enumClass::cases())
                ->mapWithKeys(fn (BackedEnum $case): array => [(string) $case->value => static::labelForCase($case)])
                ->all();
        }

        return collect($enumClass::toArray())
            ->mapWithKeys(fn ($value, $key): array => [(string) $key => (string) $enumClass::make($key)->label])
            ->all();
    }

    /**
     * Get the display label for a stored category value, or null when it is not a known category.
     */
    public static function label(?string $value): ?string
    {
        if ($value === null || $value === '') {
            return null;
        }

        return static::options()[$value] ?? null;
    }

    /**
     * Get the badge colour for a stored category value, or null when it is not a known category.
     */
    public static function color(?string $value): ?string
    {
        $category = static::find($value);

        return $category instanceof HasColor ? $category->color() : null;
    }

    /**
     * Resolve a stored category value to an enum instance.
     */
    protected static function find(?string $value): ?object
    {
        if ($value === null || $value === '' || ! array_key_exists($value, static::options())) {
            return null;
        }

        $enumClass = static::enumClass();

        if (is_subclass_of($enumClass, BackedEnum::class)) {
            return $enumClass::tryFrom($value);
        }

        return $enumClass::make($value);
    }

    /**
     * Get the label for a native enum case.
     */
    protected static function labelForCase(BackedEnum $case): string
    {
        if (method_exists($case, 'label')) {
            return (string) $case->label();
        }

        return Str::headline($case->name);
    }
}
