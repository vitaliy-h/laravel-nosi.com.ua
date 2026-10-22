<?php

namespace App\Modules\Shop\database\seeders;

use App\Modules\Shop\Models\Category;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Storage;

class ShopCategorySeeder extends Seeder
{
    /**
     * Ids are fixed by the order below (the table is truncated first) —
     * ShopProductSeeder refers to them.
     */
    public const array CATEGORIES = [
        1 => ['Жіночі шкарпетки', 'zhinochi-shkarpetky'],
        2 => ['Чоловічі шкарпетки', 'cholovichi-shkarpetky'],
        3 => ['Дитячі', 'dytiachi'],
        4 => ['Колготки', 'kolgotky'],
        5 => ['Панчохи та гольфи', 'panchokhy-ta-golfy'],
        6 => ['Білизна', 'bilyzna'],
        7 => ['Домашній одяг', 'domashnii-odiag'],
        8 => ['Подарункові набори', 'podarunkovi-nabory'],
    ];

    public function run(): void
    {
        Category::truncate();
        Storage::disk('public')->deleteDirectory('shop_categories');

        $tree = [];
        $rank = count(self::CATEGORIES);

        foreach (self::CATEGORIES as $id => [$title, $slug]) {
            $dir = "shop_categories/{$id}/img/";
            Storage::disk('public')->put($dir . "{$slug}.jpg", File::get(public_path("demo/nosi/categories/{$id}.jpg")));

            $tree[] = [
                'title'            => $title,
                'slug'             => $slug,
                'display'          => true,
                'rank'             => $rank--,
                'img'              => $dir . "{$slug}.jpg",
                'meta_title'       => $title,
                'meta_description' => $title . ' — купити в інтернет-магазині Nosi з доставкою по Україні.',
            ];
        }

        Category::rebuildTree($tree);
    }
}
