<?php

namespace ACTTraining\PermissionsManager\Tests\Fixtures\IntegerKeys;

use Illuminate\Foundation\Auth\User as Authenticatable;
use Spatie\Permission\Traits\HasRoles;

class User extends Authenticatable
{
    use HasRoles;

    protected $table = 'users';

    protected $guarded = [];
}
