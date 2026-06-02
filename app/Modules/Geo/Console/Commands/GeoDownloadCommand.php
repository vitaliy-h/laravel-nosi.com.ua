<?php

namespace App\Modules\Geo\Console\Commands;

use App\Modules\Geo\Services\GeoDownloader;
use Illuminate\Console\Attributes\Description;
use Illuminate\Console\Attributes\Signature;
use Illuminate\Console\Command;

#[Signature('geo:download')]
#[Description('Download countries, regions and cities')]
class GeoDownloadCommand extends Command
{
    /**
     * Execute the console command.
     */
    public function handle(GeoDownloader $downloader)
    {
        $this->info('Downloading XML files...');

        $downloader->downloadAll();

        $this->info('Download completed');

        return self::SUCCESS;
    }
}
