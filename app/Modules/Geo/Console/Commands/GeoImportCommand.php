<?php

namespace App\Modules\Geo\Console\Commands;

use App\Modules\Geo\Services\GeoImporter;
use Illuminate\Console\Attributes\Description;
use Illuminate\Console\Attributes\Signature;
use Illuminate\Console\Command;

#[Signature('geo:import')]
#[Description('Import countries, regions and cities')]
class GeoImportCommand extends Command
{
    /**
     * Execute the console command.
     */
    public function handle(GeoImporter $geoImporter)
    {
        $geoImporter->import();

        $this->info('Import completed');

        return self::SUCCESS;
    }
}
