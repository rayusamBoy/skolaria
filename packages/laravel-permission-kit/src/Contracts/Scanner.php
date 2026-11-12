<?php

declare(strict_types=1);

namespace PermissionKit\Contracts;

use Illuminate\Support\Collection;
use PermissionKit\Support\Context;
use PermissionKit\Support\PermissionDefinition;

interface Scanner
{
    /**
     * @return Collection<int, PermissionDefinition>
     */
    public function scan(Context $context): Collection;
}
