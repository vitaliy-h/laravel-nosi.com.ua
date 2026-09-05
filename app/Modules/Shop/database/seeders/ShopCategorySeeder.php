<?php

namespace App\Modules\Shop\database\seeders;

use App\Modules\Shop\Models\Category;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

class ShopCategorySeeder extends Seeder
{
    public function run(): void
    {
        Category::truncate();
        Storage::disk('public')->deleteDirectory('shop_categories');

        $imgIndex = 1;
        $makeImg = function () use (&$imgIndex): string {
            $sourceFile = public_path("demo/assets/img/product/product-{$imgIndex}.webp");
            $filename = Str::uuid() . '.webp';
            $dir = 'shop_categories/' . $imgIndex . '/img/';
            Storage::disk('public')->makeDirectory($dir);
            Storage::disk('public')->put($dir . $filename, File::get($sourceFile));
            $imgIndex++;

            return $dir . $filename;
        };

        $tree = [
            [
                'title'    => 'Clothing',
                'slug'     => 'clothing',
                'display'  => true,
                'img'      => $makeImg(),
                'children' => [
                    [
                        'title'   => "Men's Wear",
                        'slug'    => 'mens-wear',
                        'display' => true,
                        'img'     => $makeImg(),
                    ],
                    [
                        'title'   => "Women's Wear",
                        'slug'    => 'womens-wear',
                        'display' => true,
                        'img'     => $makeImg(),
                    ],
                    [
                        'title'   => "Kids' Clothing",
                        'slug'    => 'kids-clothing',
                        'display' => true,
                        'img'     => $makeImg(),
                    ],
                    [
                        'title'   => 'Accessories',
                        'slug'    => 'clothing-accessories',
                        'display' => true,
                        'img'     => $makeImg(),
                    ],
                ],
            ],

            [
                'title'    => 'Electronics',
                'slug'     => 'electronics',
                'display'  => true,
                'img'      => $makeImg(),
                'children' => [
                    [
                        'title'   => 'Smartphones',
                        'slug'    => 'smartphones',
                        'display' => true,
                        'img'     => $makeImg(),
                    ],
                    [
                        'title'   => 'Laptops',
                        'slug'    => 'laptops',
                        'display' => true,
                        'img'     => $makeImg(),
                    ],
                    [
                        'title'   => 'Tablets',
                        'slug'    => 'tablets',
                        'display' => true,
                        'img'     => $makeImg(),
                    ],
                    [
                        'title'   => 'Accessories',
                        'slug'    => 'electronics-accessories',
                        'display' => true,
                        'img'     => $makeImg(),
                    ],
                ],
            ],
        ];

        Category::rebuildTree($tree);
    }
}
