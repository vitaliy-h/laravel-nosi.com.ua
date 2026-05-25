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
                'title' => 'Clothing',
                'slug' => 'clothing',
                'display' => true,
                'children' => [
                    [
                        'title' => "Men's Wear",
                        'slug' => 'mens-wear',
                        'display' => true,
                    ],
                    [
                        'title' => "Women's Wear",
                        'slug' => 'womens-wear',
                        'display' => true,
                    ],
                    [
                        'title' => "Kids' Clothing",
                        'slug' => 'kids-clothing',
                        'display' => true,
                    ],
                    [
                        'title' => 'Accessories',
                        'slug' => 'clothing-accessories',
                        'display' => true,
                    ],
                ],
            ],

            [
                'title' => 'Electronics',
                'slug' => 'electronics',
                'display' => true,
                'children' => [
                    [
                        'title' => 'Smartphones',
                        'slug' => 'smartphones',
                        'display' => true,
                    ],
                    [
                        'title' => 'Laptops',
                        'slug' => 'laptops',
                        'display' => true,
                    ],
                    [
                        'title' => 'Tablets',
                        'slug' => 'tablets',
                        'display' => true,
                    ],
                    [
                        'title' => 'Accessories',
                        'slug' => 'electronics-accessories',
                        'display' => true,
                    ],
                ],
            ],
        ];

        Category::rebuildTree($tree); // nested set
    }
}
