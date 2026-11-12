<?php

declare(strict_types=1);

namespace PermissionKit\Attributes;

use Attribute;

#[Attribute(Attribute::TARGET_CLASS | Attribute::TARGET_METHOD)]
class SkipPermission {}
