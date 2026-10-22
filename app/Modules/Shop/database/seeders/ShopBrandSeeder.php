<?php

namespace App\Modules\Shop\database\seeders;

use App\Modules\Shop\Models\Brand;
use Illuminate\Database\Seeder;
use Illuminate\Support\Str;

class ShopBrandSeeder extends Seeder
{
    /**
     * Ids are fixed by the order below (the table is truncated first) —
     * ShopProductSeeder refers to brands by title.
     */
    public const array BRANDS = [
        'Nosi', 'DMDBS', 'Корона', 'COALO', 'Золото', 'KENALIN', 'BIWEIER', 'AKEU', 'Ластівка', 'Lulola',
    ];

    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        Brand::truncate();

        foreach (self::BRANDS as $title) {
            Brand::create(['title' => $title, 'slug' => Str::slug($title)]);
        }
    }
}
