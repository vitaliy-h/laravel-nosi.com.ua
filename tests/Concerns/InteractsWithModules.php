<?php

namespace Tests\Concerns;

/**
 * Lets a test state exactly which modules are on, instead of inheriting
 * whatever config/modules.php happens to ship with. A test for "shop_brands
 * off" must not also fail because someone switched `sms` off in the repo.
 */
trait InteractsWithModules
{
    /**
     * Turn every toggleable module on, then apply the given overrides.
     *
     * @param  array<string, bool>  $overrides  keyed by module name, e.g. ['shop_brands' => false]
     */
    protected function withModules(array $overrides = []): void
    {
        foreach (config('modules', []) as $key => $value) {
            // Skip the `dependencies` sub-array: only the bool flags are toggles.
            if (is_bool($value)) {
                config(["modules.{$key}" => true]);
            }
        }

        foreach ($overrides as $module => $enabled) {
            config(["modules.{$module}" => $enabled]);
        }
    }
}
