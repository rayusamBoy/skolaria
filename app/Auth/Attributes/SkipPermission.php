<?php

namespace App\Auth\Attributes;

use Attribute;

/**
 * Mark a controller class or a single method to be skipped from permission generation or enforcement.
 */
#[Attribute(Attribute::TARGET_CLASS | Attribute::TARGET_METHOD)]
class SkipPermission
{
    // marker
}