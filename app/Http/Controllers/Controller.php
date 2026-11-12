<?php

namespace App\Http\Controllers;

abstract class Controller
{
    protected array $permissionMap = [];

    public function __construct() {}
}
