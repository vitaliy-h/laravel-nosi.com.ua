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
                'slug' => '/electronics',
                'display'=>true,
                'children' => [
                    [
                        'title' => 'Phones',
                        'slug' => '/phones',
                        'display'=>true,
                    ],
                    [
                        'title' => 'Laptops',
                        'slug' => '/laptops',
                        'display'=>true,
                    ],
                ],
            ],
            [
                'title' => 'Clothing',
                'slug' => '/clothing',
                'display'=>true,
                'children' => [
                    [
                        'title' => 'Men',
                        'slug' => '/men',
                        'display'=>true,
                    ],
                    [
                        'title' => 'Women',
                        'slug' => '/women',
                        'display'=>true,
                    ],
                ],
            ],
        ];

        Category::rebuildTree($tree); // nested set
    }
}
