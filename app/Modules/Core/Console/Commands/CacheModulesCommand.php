<?php

namespace App\Modules\Core\Console\Commands;

use App\Modules\Core\Support\ModuleManifest;
use Illuminate\Console\Command;

class CacheModulesCommand extends Command
{
    protected $signature = 'modules:cache';

    protected $description = 'Cache the module manifest (routes, migrations, observers, commands, page prefixes)';

    /**
     * Run this after `migrate` and before `route:cache`: the manifest resolves
     * every page-driven route prefix from the database, and a cached route table
     * freezes whatever prefixes it finds.
     */
    public function handle(ModuleManifest $manifest): int
    {
        $manifest->clear();

        $modules = $manifest->write();

        $this->components->info(sprintf(
            'Cached the manifest for %d module(s) to %s.',
            count($modules),
            $manifest->path(),
        ));

        return self::SUCCESS;
    }
}
