<?php
namespace App\Auth\Attributes;

use Attribute;

/**
 * Mark a controller class to be skipped from permission generation.
 *
 * Usage:
 *  #[\App\Attributes\SkipPermissions]
 *  class SomeController { ... }
 */
#[Attribute(Attribute::TARGET_CLASS)]
class SkipPermissions
{
    // empty marker
}
