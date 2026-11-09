<?php

namespace App\Auth\Attributes;

use Attribute;

/**
 * Usage:
 *  #[\App\Attributes\Permission('notices.publish')]
 */
#[Attribute(Attribute::TARGET_METHOD)]
class Permission
{
    public function __construct(public string $name) {}
}
