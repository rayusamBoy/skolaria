<?php

declare(strict_types=1);

namespace PermissionKit\Facades;

use Illuminate\Support\Facades\Facade;

class PermissionKit extends Facade
{
    protected static function getFacadeAccessor(): string
    {
        return \PermissionKit\PermissionKit::class;
    }
}
