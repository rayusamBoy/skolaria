<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Concerns\AuthorizesPermissions;

abstract class Controller
{
    use AuthorizesPermissions;
    public function __construct()
    {
        // run trait boot - it will check permissionMap if present in child controller
        $this->bootPermissionMap();
    }
}
