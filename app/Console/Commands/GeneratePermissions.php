<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Filesystem\Filesystem;
use Illuminate\Support\Str;
use ReflectionClass;
use ReflectionMethod;

/**
 * This command scans the app/Http/Controllers directory for controller classes and their public methods,
 * and generates permission names based on method names and configurable mapping rules.
 * The generated permission names are then printed to the console in a structured format.
 * This is intended as a developer tool to help maintain a consistent permission naming scheme across the application.
 * 
 * The printed permission names can be used to populate the permissions configuration or for reference when reviewing permissions.
 */
class GeneratePermissions extends Command
{
    protected $signature = 'permissions:generate';
    protected $description = 'Scan controllers and print permission names to the console';

    public function __construct(protected Filesystem $files)
    {
        parent::__construct();
    }

    public function handle(): int
    {
        $base = base_path('app/Http/Controllers');
        if (!$this->files->isDirectory($base)) {
            $this->error("Controllers path not found: {$base}");
            return 1;
        }

        $methodMap = config('permissions.method_map', []);
        $useDots = (bool) config('permissions.use_dots', false);
        $overrides = config('permissions.overrides', []);
        $ignoreControllers = array_map('strval', (array) config('permissions.ignore_controllers', []));
        $ignorePaths = array_map('strval', (array) config('permissions.ignore_paths', []));

        $permissions = [];
        $centralOnly = [];

        foreach ($this->files->allFiles($base) as $file) {
            if ($file->getExtension() !== 'php')
                continue;

            $relative = str_replace(app_path() . DIRECTORY_SEPARATOR, '', $file->getRealPath());
            $fqcn = 'App\\' . str_replace([DIRECTORY_SEPARATOR, '.php'], ['\\', ''], $relative);

            if (!class_exists($fqcn)) {
                require_once $file->getRealPath();
                if (!class_exists($fqcn))
                    continue;
            }

            $rc = new ReflectionClass($fqcn);
            if ($rc->isAbstract() || $rc->isInterface())
                continue;
            if (!Str::startsWith($rc->getNamespaceName(), 'App\Http\Controllers'))
                continue;

            $short = $rc->getShortName();
            if (!Str::endsWith($short, 'Controller'))
                continue;

            // ignore controllers by name
            if (\in_array($short, $ignoreControllers, true) || \in_array($rc->getName(), $ignoreControllers, true)) {
                continue;
            }

            $filepath = $file->getRealPath();
            $skipByPath = false;
            foreach ($ignorePaths as $pat) {
                if (str_starts_with($pat, '/') && str_ends_with($pat, '/')) {
                    if (@preg_match($pat, $filepath)) {
                        $skipByPath = true;
                        break;
                    }
                } else {
                    if (str_contains($filepath, $pat)) {
                        $skipByPath = true;
                        break;
                    }
                }
            }
            if ($skipByPath)
                continue;

            // class-level quick skips
            if (method_exists($rc, 'getAttributes')) {
                foreach ($rc->getAttributes() as $attr) {
                    $an = $attr->getName();
                    if (str_ends_with($an, 'SkipPermissions') || $an === \App\Auth\Attributes\SkipPermission::class) {
                        continue 2;
                    }
                }
            }
            $classDoc = $rc->getDocComment() ?: '';
            if ($classDoc && preg_match('/@permissions-ignore\b/', $classDoc))
                continue;
            $defaults = $rc->getDefaultProperties();
            if (!empty($defaults['skipPermissions']))
                continue;

            $baseName = substr($short, 0, -10);
            $resource = Str::plural(Str::snake($baseName, '_'));
            if (Str::contains($filepath, DIRECTORY_SEPARATOR . 'Central' . DIRECTORY_SEPARATOR)) {
                $centralOnly[$resource] = true;
            }

            foreach ($rc->getMethods(ReflectionMethod::IS_PUBLIC) as $m) {
                if ($m->getDeclaringClass()->getName() !== $rc->getName())
                    continue;
                $mname = $m->getName();
                if (str_starts_with($mname, '_'))
                    continue;
                if (\in_array($mname, ['__construct', '__call', 'callAction', 'middleware', 'authorize', 'middlewarePriority'], true))
                    continue;
                if (method_exists($m, 'getAttributes')) {
                    foreach ($m->getAttributes() as $attr) {
                        $an = $attr->getName();
                        if ($an === \App\Auth\Attributes\SkipPermission::class || str_ends_with($an, 'SkipPermission')) {
                            continue 2;
                        }
                    }
                }

                $perm = $this->buildPermissionName($resource, $m, $short, $methodMap, $useDots, $overrides);
                $permissions[$resource][$mname] = $perm;
            }
        }

        ksort($permissions);
        foreach ($permissions as $r => $methods) {
            ksort($methods);
            $this->line("Resource: {$r}");
            foreach ($methods as $m => $p) {
                $this->line("  {$m} -> {$p}");
            }
        }

        $central = array_values(array_keys($centralOnly));
        $this->line('Central-only resources: ' . ($central ? implode(', ', $central) : '(none)'));

        return 0;
    }

    /**
     * Build permission name with support for:
     * - method attribute/docblock override (@permission or \App\Auth\Attributes\Permission attribute)
     * - config overrides per controller short name or resource name
     * - method_map and use_dots config behavior
     */
    protected function buildPermissionName(
        string $resource,
        ReflectionMethod $m,
        string $controllerShort,
        array $methodMap,
        bool $useDots,
        array $overrides
    ): string {
        $mname = $m->getName();

        // 1) attribute/docblock override
        if (method_exists($m, 'getAttributes')) {
            foreach ($m->getAttributes() as $attr) {
                $an = $attr->getName();
                if (str_ends_with($an, 'Permission') || $an === \App\Auth\Attributes\Permission::class) {
                    $args = $attr->getArguments();
                    if (!empty($args[0]))
                        return (string) $args[0];
                }
            }
        }
        $doc = $m->getDocComment() ?: '';
        if ($doc && preg_match('/@permission\s+([^\s*]+)/', $doc, $mm)) {
            return trim($mm[1]);
        }

        // 2) explicit config overrides (controller short name first, then resource)
        if (!empty($overrides)) {
            if (isset($overrides[$controllerShort]) && isset($overrides[$controllerShort][$mname])) {
                return $overrides[$controllerShort][$mname];
            }
            if (isset($overrides[$resource]) && isset($overrides[$resource][$mname])) {
                return $overrides[$resource][$mname];
            }
        }

        // 3) automatic mapping
        // normalize method name to snake-ish segments
        $s = $mname;
        $s = preg_replace('/([a-z0-9])([A-Z])/', '$1_$2', $s);
        $s = preg_replace('/([A-Z]+)([A-Z][a-z])/', '$1_$2', $s);
        $s = preg_replace('/[^A-Za-z0-9]+/', '_', $s);
        $s = strtolower(preg_replace('/_+/', '_', $s));
        $s = trim($s, '_');

        $parts = explode('_', $s);
        $verb = $parts[0] ?? $s;
        $rest = \array_slice($parts, 1);

        if (isset($methodMap[$verb])) {
            $action = $methodMap[$verb];
            if (!empty($rest)) {
                $tail = $useDots ? implode('.', $rest) : implode('_', $rest);
                return "{$resource}.{$action}.{$tail}";
            }
            return "{$resource}.{$action}";
        }

        $suffix = $useDots ? str_replace('_', '.', $s) : $s;
        return "{$resource}.{$suffix}";
    }
}
