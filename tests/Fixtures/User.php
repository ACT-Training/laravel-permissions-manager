<?php

namespace ACTTraining\PermissionsManager\Tests\Fixtures;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Spatie\Permission\Traits\HasRoles;

class User extends Authenticatable
{
    use HasRoles;
    use HasUuids;

    protected $primaryKey = 'uuid';

    protected $guarded = [];
}
