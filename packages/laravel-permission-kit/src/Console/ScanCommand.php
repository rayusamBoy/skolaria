<?php

declare(strict_types=1);

namespace PermissionKit\Console;

use Illuminate\Console\Command;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Cache;
use PermissionKit\Services\PermissionCollector;
use PermissionKit\Services\PermissionManifest;

class ScanCommand extends Command
{
    protected $signature = 'permission-kit:scan
                        {--context= : Restrict to a single context key}
                        {--format=console : console|json|php}
                        {--pretty : Pretty-print JSON output}
                        {--save : Write the manifest file (preserves your edits)}
                        {--force : With --save, overwrite the manifest completely instead of merging}';

    protected $description = 'Scan controllers and print discovered permissions (without touching the database).';

    public function handle(PermissionCollector $collector, PermissionManifest $manifest): int
    {
        $definitions = $collector->collect($this->option('context'));

        if ($definitions->isEmpty()) {
            $this->warn('No permissions found.');

            return self::SUCCESS;
        }

        if ($this->option('save')) {
            if ($manifest->path() === '') {
                $this->error('manifest_path is empty. Set it in config/permission-kit.php.');

                return self::FAILURE;
            }

            $manifest->save($definitions, merge: ! $this->option('force'));

            // Bust the name cache so the middleware picks up manifest changes
            Cache::put('permission_kit.cache_version', Cache::get('permission_kit.cache_version', 1) + 1);

            $this->info("Manifest saved to {$manifest->path()}");
        }

        return match ($this->option('format')) {
            'json' => $this->outputJson($definitions),
            'php' => $this->outputPhp($definitions),
            default => $this->outputConsole($definitions),
        };
    }

    protected function outputConsole(Collection $definitions): int
    {
        $this->table(
            ['Context', 'Controller', 'Method', 'Permission', 'Label'],
            $definitions->map(fn ($d) => [
                $d->context, $d->controller, $d->method, $d->name, $d->label ?? '',
            ])->all()
        );

        $this->info(\sprintf('Total: %d permission(s) discovered.', $definitions->count()));

        return self::SUCCESS;
    }

    protected function outputJson(Collection $definitions): int
    {
        $payload = $definitions->map(fn ($d) => [
            'name' => $d->name,
            'label' => $d->label,
            'description' => $d->description,
            'context' => $d->context,
            'controller' => $d->controller,
            'method' => $d->method,
        ])->values()->all();

        $flags = JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES;
        if ($this->option('pretty')) {
            $flags |= JSON_PRETTY_PRINT;
        }

        $this->line(json_encode($payload, $flags));

        return self::SUCCESS;
    }

    protected function outputPhp(Collection $definitions): int
    {
        $this->line('Copy this snippet into config/permission-kit.php under "names":');
        $this->newLine();

        $grouped = [];
        foreach ($definitions as $d) {
            $parts = explode('.', $d->name);
            $resource = $parts[0] ?? 'misc';
            $grouped[$resource][] = $d;
        }

        $this->line("    'names' => [");
        foreach ($grouped as $resource => $defs) {
            $this->line("        '{$resource}' => [");
            foreach ($defs as $d) {
                $this->line("            '{$d->method}' => [");
                $this->line("                'permission'  => '{$d->name}',");
                $this->line("                'label'       => ".var_export($d->label ?? '', true).',');
                $this->line("                'description' => ".var_export($d->description ?? '', true).',');
                $this->line('            ],');
            }
            $this->line('        ],');
        }
        $this->line('    ],');

        return self::SUCCESS;
    }
}
