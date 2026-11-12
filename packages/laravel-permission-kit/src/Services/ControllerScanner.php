<?php

declare(strict_types=1);

namespace PermissionKit\Services;

use Illuminate\Filesystem\Filesystem;
use Illuminate\Support\Collection;
use PermissionKit\Attributes\SkipPermission;
use PermissionKit\Contracts\NamingStrategy;
use PermissionKit\Contracts\Scanner;
use PermissionKit\Exceptions\ScannerException;
use PermissionKit\Support\Context;
use PermissionKit\Support\PermissionDefinition;
use ReflectionClass;
use ReflectionMethod;

class ControllerScanner implements Scanner
{
    protected static array $namespace_map = [];

    public function __construct(
        protected Filesystem $files,
        protected NamingStrategy $naming,
    ) {}

    /**
     * @return Collection<int, PermissionDefinition>
     */
    public function scan(Context $context): Collection
    {
        $classes = [];

        // 1. Discover controllers from configured directory namespaces (APPLY PREFIX)
        foreach ($context->namespaces as $namespace) {
            $directories = $this->directoriesForNamespace($namespace);

            if (empty($directories)) {
                throw new ScannerException("No directory found for namespace '{$namespace}'. Check your composer.json autoload section.");
            }

            foreach ($directories as $base_dir) {
                if (! is_dir($base_dir)) {
                    continue;
                }

                foreach ($this->files->allFiles($base_dir) as $file) {
                    if ($file->getExtension() !== 'php') {
                        continue;
                    }

                    $fqcn = $this->fqcnFromFile($file->getPathname(), $namespace, $base_dir);

                    if ($this->canReflect($fqcn)) {
                        $classes[] = [
                            'class' => new ReflectionClass($fqcn),
                            'apply_prefix' => true, // Scanned from namespace -> Apply prefix
                        ];
                    }
                }
            }
        }

        // 2. Discover explicitly defined extra classes (DO NOT APPLY PREFIX)
        foreach ($context->extra_classes as $fqcn) {
            if ($this->canReflect($fqcn)) {
                $classes[] = [
                    'class' => new ReflectionClass($fqcn),
                    'apply_prefix' => false, // Shared root controller -> No prefix
                ];
            }
        }

        // 3. Deduplicate purely by FQCN (If duplicated, prioritize apply_prefix=true)
        $unique_classes = [];
        foreach ($classes as $item) {
            $fqcn = $item['class']->getName();
            if (! isset($unique_classes[$fqcn]) || $item['apply_prefix']) {
                $unique_classes[$fqcn] = $item;
            }
        }
        $classes = array_values($unique_classes);

        // 4. Drop excluded namespaces
        $classes = $this->excludeByNamespace($classes, $context->exclude_namespaces);

        // 5. Apply skip rules & extract permissions
        $definitions = collect();
        foreach ($classes as $item) {
            $class = $item['class'];
            $apply_prefix = $item['apply_prefix'];

            if ($this->shouldSkipClass($class, $context)) {
                continue;
            }

            $definitions = $definitions->merge($this->scanMethods($class, $context, $apply_prefix));
        }

        return $definitions->unique('name')->values();
    }

    /**
     * Drop controllers that belong to excluded namespaces.
     *
     * @param  array<int, array{class: ReflectionClass, apply_prefix: bool}>  $classes
     * @param  array<int, string>  $excluded_prefixes
     * @return array<int, array{class: ReflectionClass, apply_prefix: bool}>
     */
    protected function excludeByNamespace(array $classes, array $excluded_prefixes): array
    {
        if (empty($excluded_prefixes)) {
            return $classes;
        }

        return array_values(array_filter(
            $classes,
            static function (array $item) use ($excluded_prefixes): bool {
                $fqcn = $item['class']->getName();
                foreach ($excluded_prefixes as $prefix) {
                    if (str_starts_with($fqcn, $prefix.'\\') || $fqcn === $prefix) {
                        return false;
                    }
                }

                return true;
            }
        ));
    }

    protected function scanMethods(ReflectionClass $class, Context $context, bool $apply_prefix): Collection
    {
        $definitions = collect();
        $skip_methods = config('permission-kit.skip_methods', []);

        foreach ($class->getMethods(ReflectionMethod::IS_PUBLIC) as $method) {
            if ($method->getDeclaringClass()->getName() !== $class->getName()) {
                continue;
            }

            if (\in_array($method->getName(), $skip_methods, true)) {
                continue;
            }

            if (! empty($method->getAttributes(SkipPermission::class))) {
                continue;
            }

            // Per-controller skip array
            $defaults = $class->getDefaultProperties();
            if (isset($defaults['skipPermission']) && \is_array($defaults['skipPermission'])) {
                if (\in_array($method->getName(), $defaults['skipPermission'], true)) {
                    continue;
                }
            }

            $definitions->push($this->naming->build($class, $method, $context, $apply_prefix));
        }

        return $definitions;
    }

    protected function shouldSkipClass(ReflectionClass $class, Context $context): bool
    {
        $short_name = $class->getShortName();

        if (\in_array($short_name, $context->skip_names, true)) {
            return true;
        }

        if (\in_array($class->getName(), $context->skip_names, true)) {
            return true;
        }

        // Normalise both sides to forward slashes so Windows paths still match.
        $file = str_replace('\\', '/', $class->getFileName() ?: '');

        foreach ($context->skip_paths as $pattern) {
            $pattern = str_replace('\\', '/', $pattern);
            if ($pattern !== '' && str_contains($file, $pattern)) {
                return true;
            }
        }

        if (! empty($class->getAttributes(SkipPermission::class))) {
            return true;
        }

        if ($class->isAbstract() || $class->isInterface() || $class->isTrait()) {
            return true;
        }

        if (! str_ends_with($short_name, 'Controller')) {
            return true;
        }

        return false;
    }

    protected function canReflect(string $fqcn): bool
    {
        try {
            if (! class_exists($fqcn)) {
                return false;
            }
            new ReflectionClass($fqcn);

            return true;
        } catch (\Throwable) {
            return false;
        }
    }

    protected function fqcnFromFile(string $file_path, string $namespace, string $base_dir): string
    {
        $relative = str_replace($base_dir, '', $file_path);
        $relative = trim(str_replace(['/', '\\'], '\\', $relative), '\\');
        $relative = preg_replace('/\.php$/', '', $relative);

        return rtrim($namespace, '\\').'\\'.$relative;
    }

    /**
     * Resolve the filesystem directory (or directories) for a namespace
     * by reading composer.json PSR-4 mappings.
     *
     * @return array<int, string>
     */
    protected function directoriesForNamespace(string $namespace): array
    {
        if (empty(self::$namespace_map)) {
            self::$namespace_map = $this->buildNamespaceMap();
        }

        $best_prefix = null;
        $best_len = 0;

        foreach (self::$namespace_map as $prefix => $dirs) {
            if (str_starts_with($namespace, $prefix) && \strlen($prefix) > $best_len) {
                $best_prefix = $prefix;
                $best_len = \strlen($prefix);
            }
        }

        if ($best_prefix === null) {
            return [];
        }

        $relative = str_replace('\\', '/', substr($namespace, \strlen($best_prefix)));

        return array_map(
            fn (string $dir) => rtrim($dir, '/').($relative !== '' ? '/'.$relative : ''),
            self::$namespace_map[$best_prefix]
        );
    }

    /**
     * @return array<string, array<int, string>>
     */
    protected function buildNamespaceMap(): array
    {
        $composer_path = config('permission-kit.composer_json_path') ?? base_path('composer.json');

        if (! \is_string($composer_path) || ! file_exists($composer_path)) {
            return [];
        }

        $json = json_decode((string) file_get_contents($composer_path), true) ?? [];
        $psr4 = $json['autoload']['psr-4'] ?? [];
        $psr4_dev = $json['autoload-dev']['psr-4'] ?? [];

        // Directory of the composer.json, used as the resolution root for relative paths.
        $root = str_replace('\\', '/', dirname($composer_path));

        $map = [];
        foreach ($psr4 + $psr4_dev as $prefix => $dirs) {
            $dirs = (array) $dirs;

            $map[$prefix] = array_map(function (string $dir) use ($root) {
                $dir = str_replace('\\', '/', trim($dir, '/\\'));

                return "$root/$dir";
            }, $dirs);
        }

        return $map;
    }
}
