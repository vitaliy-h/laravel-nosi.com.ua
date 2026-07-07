<?php

namespace App\Modules\Geo\database\seeders;

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
