<?php

namespace ACTTraining\PermissionsManager\Tests\Fixtures;

use ACTTraining\PermissionsManager\Contracts\HasColor;
use Spatie\Enum\Enum;

/**
 * A spatie/enum category enum, as used by applications on version 1.0 of the package.
 *
 * @method static self meetings()
 * @method static self data_kpis()
 */
final class LegacySpatieCategoryEnum extends Enum implements HasColor
{
    protected static function labels(): array
    {
        return [
            'meetings' => 'Meetings',
            'data_kpis' => 'Data & KPIs',
        ];
    }

    public function color(): string
    {
        return match ($this->value) {
            'meetings' => 'blue',
            default => 'purple',
        };
    }
}
