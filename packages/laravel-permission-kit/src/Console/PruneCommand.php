<?php

declare(strict_types=1);

namespace PermissionKit\Console;

use Illuminate\Console\Command;
use PermissionKit\Services\PermissionCollector;
use PermissionKit\Services\PermissionSyncService;

class PruneCommand extends Command
{
    protected $signature = 'permission-kit:prune
                            {--context= : Restrict to a single context key}
                            {--force : Skip confirmation}';

    protected $description = 'Delete orphaned permissions not found in the current scan. Requires allow_prune=true.';

    public function handle(PermissionCollector $collector, PermissionSyncService $sync): int
    {
        if (! config('permission-kit.allow_prune', false)) {
            $this->error('Pruning is disabled. Set permission-kit.allow_prune=true in config.');

            return self::FAILURE;
        }

        if (! $this->option('force') && ! $this->confirm('Delete orphaned permissions not found in the current scan?', false)) {
            $this->info('Prune cancelled.');

            return self::SUCCESS;
        }

        $definitions = $collector->collect($this->option('context'));
        $report = $sync->sync($definitions, false, 'preserve', true);

        $this->info(\sprintf('Pruned %d permission(s).', \count($report->pruned)));

        foreach ($report->pruned as $name) {
            $this->line("  - {$name}");
        }

        if (\count($report->orphaned) > \count($report->pruned)) {
            $this->warn('Some orphans were kept because they are still assigned to roles/users.');
        }

        return self::SUCCESS;
    }
}
