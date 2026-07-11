<?php

namespace App\Modules\Core\Support;

class ModuleGate
{
    /**
     * Whether the module identified by its snake_case name (matching both
     * the config/modules.php key and the Str::snake'd app/Modules folder
     * name) is enabled.
     *
     * A module with no config key and no dependency is a core module
     * (Core, Index, TextPage, Sitemap, Translation, Notification, _debug, ...)
     * and is always enabled — it cannot be toggled off.
     */
    public static function isEnabled(string $module): bool
    {
        if (!self::isToggleable($module)) {
            return true;
        }

        $hasOwnKey = array_key_exists($module, config('modules', []));
        $dependsOn = config("modules.dependencies.{$module}");

        $ownEnabled = $hasOwnKey ? (bool) config("modules.{$module}") : true;
        $dependencyEnabled = $dependsOn ? self::isEnabled($dependsOn) : true;

        return $ownEnabled && $dependencyEnabled;
    }

    /**
     * Whether the module can be toggled at all, i.e. it either has its own
     * config/modules.php key or depends on one. Everything else is a core
     * module that is always enabled.
     */
    public static function isToggleable(string $module): bool
    {
        return array_key_exists($module, config('modules', []))
            || array_key_exists($module, config('modules.dependencies', []));
    }
}
