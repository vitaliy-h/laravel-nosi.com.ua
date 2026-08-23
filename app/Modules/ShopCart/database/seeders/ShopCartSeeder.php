<?php

namespace App\Modules\ShopCart\database\seeders;

use Illuminate\Database\Seeder;

class ShopCartSeeder extends Seeder
{
    public function run(): void
    {
        $this->call(PromocodeSeeder::class);
    }
}
