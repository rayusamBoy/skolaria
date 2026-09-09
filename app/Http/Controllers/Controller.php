<?php

namespace App\Http\Controllers;

use App\Traits\AuthorizesPermissions;

abstract class Controller
{
    use AuthorizesPermissions;

    protected $permissionMap;

    public function __construct()
    {
        // run trait boot - it will check permissionMap if present in child controller
        $this->bootPermissionMap();
    }
}
