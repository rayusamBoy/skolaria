<?php

declare(strict_types=1);

namespace PermissionKit\Console;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\Cache;
use Spatie\Permission\PermissionRegistrar;

class ClearCacheCommand extends Command
{
    protected $signature = 'permission-kit:clear-cache';

    protected $description = 'Clear Spatie permission cache.';

    public function handle(PermissionRegistrar $registrar): int
    {
        $registrar->forgetCachedPermissions();

        // Invalidate the resolved-name cache by incrementing the version key
        Cache::put('permission_kit.cache_version', Cache::get('permission_kit.cache_version', 1) + 1);

        $this->info('Permission cache cleared.');

        return self::SUCCESS;
    }
}
