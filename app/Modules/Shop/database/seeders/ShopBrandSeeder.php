<?php

namespace App\Modules\Shop\database\seeders;

use App\Modules\Shop\Models\Brand;
use Illuminate\Database\Seeder;

class ShopBrandSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        Brand::truncate();

        $brands = [
            ['title' => 'Apple','slug'=>'apple'],
            ['title' => 'Samsung','slug'=>'samsung'],
            ['title' => 'Nike','slug'=>'nike'],
            ['title' => 'Adidas','slug'=>'adidas'],
        ];

        foreach ($brands as $brand) {
            Brand::create($brand);
        }
    }
}
