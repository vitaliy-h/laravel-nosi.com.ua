<?php

namespace App\Modules\Shop\database\seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class ShopSeeder extends Seeder
{
    public function run(): void
    {
        DB::statement('SET FOREIGN_KEY_CHECKS=0;');
        $this->call(ShopCategorySeeder::class);
        $this->call(ShopBrandSeeder::class);
        $this->call(ShopProductSeeder::class);
        $this->call(ShopReviewSeeder::class);
        DB::statement('SET FOREIGN_KEY_CHECKS=1;');
    }
}
