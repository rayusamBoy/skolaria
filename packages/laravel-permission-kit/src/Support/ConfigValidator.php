<?php

declare(strict_types=1);

namespace PermissionKit\Support;

use Illuminate\Contracts\Foundation\Application;
use Illuminate\Support\Facades\Log;

class ConfigValidator
{
    public static function check(Application $app): bool
    {
        $contexts = config('permission-kit.contexts', []);
        if (empty($contexts)) {
            Log::warning('[permission-kit] No contexts configured.');

            return false;
        }

        foreach ($contexts as $key => $ctx) {
            if (empty($ctx['namespaces'])) {
                Log::warning("[permission-kit] Context '{$key}' has no namespaces configured.");
            }
        }

        return true;
    }
}
