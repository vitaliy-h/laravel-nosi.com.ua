<?php

namespace App\Modules\Shop\database\seeders;

use App\Modules\Shop\Models\Product;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

class ShopProductSeeder extends Seeder
{
    public function run(): void
    {
        Product::truncate();
        Storage::disk('public')->deleteDirectory('shop_products');

        for ($i = 1; $i <= 12; $i++) {
            $sourceFile = public_path("demo/assets/img/product/product-{$i}.webp");
            $filename = Str::uuid() . '.webp';
            $dir = 'shop_products/'.$i.'/img/';
            Storage::disk('public')->makeDirectory($dir);
            Storage::disk('public')->put(
                $dir . $filename,
                File::get($sourceFile)
            );
            Product::create([
                //'id'=>$i,
                'title' => fake()->words(3, true),
                'slug' => fake()->slug(),
                'price' => fake()->numberBetween(30, 300),
                'category_id' => fake()->randomElement([2,3,4,5,7,8,9,10]),
                'brand_id' => fake()->numberBetween(1, 4),
                'img' => $dir. $filename,
            ]);
        }
    }
}
