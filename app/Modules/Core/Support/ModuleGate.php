<?php

namespace App\Modules\Core\Support;

class ModuleGate
{
    /**
     * Whether the module is enabled, by its snake_case name (matching both the
     * config/modules.php key and the Str::snake'd app/Modules folder name).
     *
     * Everything is on by default. A module is off only when config/modules.php
     * says so out loud, which is why that file lists nothing but the modules you
     * may want to switch off, all of them commented out.
     *
     * A module listed under `dependencies` also goes off with its parent.
     */
    public static function isEnabled(string $module): bool
    {
        if (!config("modules.{$module}", true)) {
            return false;
        }

        $dependsOn = config("modules.dependencies.{$module}");

        return $dependsOn === null || self::isEnabled($dependsOn);
    }
}
