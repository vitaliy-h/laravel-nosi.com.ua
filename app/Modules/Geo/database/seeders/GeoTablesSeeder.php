<?php

namespace App\Modules\Geo\Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Artisan;

class GeoTablesSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        Artisan::call('geo:download');
        Artisan::call('geo:import');
    }
}
