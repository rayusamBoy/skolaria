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
    public string $name;

    public function __construct(string $name)
    {
        $this->name = $name;
    }
}
