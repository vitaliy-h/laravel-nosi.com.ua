<?php

namespace App\Modules\ShopOrders\database\seeders;

use Illuminate\Database\Seeder;

class ShopOrdersSeeder extends Seeder
{
    public function run(): void
    {
        $this->call(ShopOrderSeeder::class);
    }
}
