<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use App\Models\Shop\Category;

class ShopCategorySeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        Category::truncate();

        $tree = [
            [
                'title' => 'Electronics',
                'slug' => 'electronics',
                'children' => [
                    [
                        'title' => 'Phones',
                        'slug' => 'phones',
                    ],
                    [
                        'title' => 'Laptops',
                        'slug' => 'laptops',
                    ],
                ],
            ],
            [
                'title' => 'Clothing',
                'slug' => 'clothing',
                'children' => [
                    [
                        'title' => 'Men',
                        'slug' => 'men',
                    ],
                    [
                        'title' => 'Women',
                        'slug' => 'women',
                    ],
                ],
            ],
        ];

        Category::rebuildTree($tree); // nested set
    }
}
