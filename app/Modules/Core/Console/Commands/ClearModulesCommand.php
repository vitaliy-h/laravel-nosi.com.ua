<?php

namespace App\Modules\Core\Console\Commands;

use App\Modules\Core\Support\ModuleManifest;
use Illuminate\Console\Command;

class ClearModulesCommand extends Command
{
    protected $signature = 'modules:clear';

    protected $description = 'Remove the cached module manifest';

    /**
     * Without a manifest the application builds one in memory on every boot —
     * slower, but always current. That is the local default.
     */
    public function handle(ModuleManifest $manifest): int
    {
        $manifest->clear();

        $this->components->info('Module manifest cleared.');

        return self::SUCCESS;
    }
}
