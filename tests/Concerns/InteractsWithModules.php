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
        foreach (array_keys(config('modules.list', [])) as $key) {
            config(["modules.list.{$key}" => true]);
        }

        foreach ($overrides as $module => $enabled) {
            config(["modules.list.{$module}" => $enabled]);
        }
    }
}
