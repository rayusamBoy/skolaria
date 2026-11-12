<?php

declare(strict_types=1);

namespace PermissionKit\Tests\Fixtures\Models;

use Illuminate\Foundation\Auth\User as Authenticatable;
use Spatie\Permission\Traits\HasRoles;

class User extends Authenticatable
{
    use HasRoles;

    protected $guarded = [];

    protected $hidden = ['password', 'remember_token'];
}
