<?php

namespace App\Services;

use App\Repositories\SettingRepo;
use Illuminate\Support\Facades\Cache;

class FeatureModuleService
{
    protected array $moduleCache = [];

    protected ?array $disabledCache = null;

    public function __construct(protected SettingRepo $setting) {}

    /**
     * All toggleable modules (from config).
     */
    public function all(): array
    {
        return config('modules.toggleable', []);
    }

    /**
     * Modules that are currently disabled for this tenant.
     */
    public function disabled(): array
    {
        if ($this->disabledCache !== null) {
            return $this->disabledCache;
        }

        $tenantId = tenant('id') ?? 'central';
        $cacheKey = "disabled_modules:{$tenantId}";

        return $this->disabledCache = Cache::remember(
            $cacheKey,
            config('modules.cache_ttl', 3600),
            function (): mixed {
                $raw = $this->setting->get('disabled_modules');

                return $raw ? array_filter(explode(',', $raw)) : [];
            }
        );
    }

    /**
     * Is a given module enabled?
     */
    public function isEnabled(string $module): bool
    {
        return ! \in_array($module, $this->disabled(), true);
    }

    /**
     * Is a given module disabled?
     */
    public function isDisabled(string $module): bool
    {
        return ! $this->isEnabled($module);
    }

    /**
     * Determine whether a permission should be allowed given the current
     * module states. This is the central hook used everywhere.
     */
    public function isPermissionAllowed(string $permission): bool
    {
        $prefix = explode('.', $permission)[0] ?? '';
        if ($prefix === '') {
            return true;
        }

        foreach ($this->all() as $key => $module) {
            $prefixes = $module['permission_prefixes'] ?? [$key];
            if (\in_array($prefix, $prefixes, true)) {
                return $this->isEnabled($key);
            }
        }

        return true; // not part of any toggleable module
    }

    /**
     * Enable a module.
     */
    public function enable(string $module): void
    {
        $this->save(array_values(array_diff($this->disabled(), [$module])));
    }

    /**
     * Disable a module.
     */
    public function disable(string $module): void
    {
        if (! $this->isToggleable($module)) {
            return;
        }

        $disabled = $this->disabled();
        if (! \in_array($module, $disabled, true)) {
            $disabled[] = $module;
            $this->save($disabled);
        }
    }

    public function isToggleable(string $module): bool
    {
        return \array_key_exists($module, $this->all());
    }

    protected function save(array $disabled): void
    {
        $value = implode(',', array_unique($disabled));

        $this->setting->updateOrCreate('disabled_modules', $value);

        // Bust cache
        $tenantId = tenant('id') ?? 'central';
        Cache::forget("disabled_modules:{$tenantId}");
        $this->disabledCache = null;
    }
}
