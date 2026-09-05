<?php

namespace App\Modules\Core\Support;

class ModuleGate
{
    /**
     * Whether the module is enabled, by its snake_case name (matching both the
     * config/modules.php `list` key and the Str::snake'd app/Modules folder name).
     *
     * Everything is on by default. A module is off only when config/modules.php
     * says so out loud, which is why that file lists nothing but the modules you
     * may want to switch off, all of them commented out.
     *
     * A module listed under `dependencies` also goes off with its parent.
     */
    public static function isEnabled(string $module, array $seen = []): bool
    {
        if (!config("modules.list.{$module}", true)) {
            return false;
        }

        $dependsOn = config("modules.dependencies.{$module}");

        if ($dependsOn === null) {
            return true;
        }

        // A cycle in `dependencies` (a → b → a) would otherwise recurse forever.
        if (in_array($module, $seen, true)) {
            return true;
        }

        return self::isEnabled($dependsOn, [...$seen, $module]);
    }
}
