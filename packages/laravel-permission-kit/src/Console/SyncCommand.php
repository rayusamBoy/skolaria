<?php

declare(strict_types=1);

namespace PermissionKit\Console;

use Illuminate\Console\Command;
use PermissionKit\Services\PermissionCollector;
use PermissionKit\Services\PermissionManifest;
use PermissionKit\Services\PermissionSyncService;

class SyncCommand extends Command
{
    protected $signature = 'permission-kit:sync
                            {--context= : Restrict to a single context key}
                            {--dry-run : Show what would be created/updated without writing}
                            {--prune : Delete orphaned permissions (requires allow_prune=true)}
                            {--mode= : update|preserve|overwrite (overrides config)}';

    protected $description = 'Sync discovered permissions into the Spatie permissions table.';

    public function handle(PermissionCollector $collector, PermissionSyncService $sync, PermissionManifest $manifest): int
    {
        // Prefer the manifest when it exists — it holds user-edited labels/descriptions.
        $definitions = $manifest->exists()
            ? $manifest->toDefinitions()
            : $collector->collect($this->option('context'));

        if ($definitions->isEmpty()) {
            $this->warn('No permissions to sync.');

            return self::SUCCESS;
        }

        $report = $sync->sync(
            $definitions,
            (bool) $this->option('dry-run'),
            $this->option('mode'),
            (bool) $this->option('prune'),
        );

        $this->info(\sprintf('Processed %d permission(s).', $report->totalProcessed()));
        $this->line(\sprintf('  Created:   %d', \count($report->created)));
        $this->line(\sprintf('  Updated:   %d', \count($report->updated)));
        $this->line(\sprintf('  Unchanged: %d', \count($report->unchanged)));

        if (! empty($report->created)) {
            $this->newLine();
            $this->line('Created:');
            foreach ($report->created as $name) {
                $this->line("  + {$name}");
            }
        }

        if (! empty($report->pruned)) {
            $this->newLine();
            $this->line('Pruned:');
            foreach ($report->pruned as $name) {
                $this->line("  - {$name}");
            }
        }

        if ($this->option('dry-run')) {
            $this->newLine();
            $this->warn('Dry run: no changes were written.');
        }

        return self::SUCCESS;
    }
}
