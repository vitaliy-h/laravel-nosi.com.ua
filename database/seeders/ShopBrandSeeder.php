<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use App\Models\Shop\Brand;

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
