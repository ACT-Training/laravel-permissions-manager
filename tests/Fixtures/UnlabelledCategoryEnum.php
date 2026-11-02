<?php

namespace ACTTraining\PermissionsManager\Tests\Fixtures;

use ACTTraining\PermissionsManager\Contracts\HasColor;

enum UnlabelledCategoryEnum: string implements HasColor
{
    case DataKpis = 'data_kpis';

    public function color(): string
    {
        return 'green';
    }
}
