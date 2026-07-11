<?php

namespace App\Modules\Geo\database\seeders;

use Illuminate\Database\Seeder;

class GeoSeeder extends Seeder
{
    public function run(): void
    {
        $this->call(GeoTablesSeeder::class);
    }
}
