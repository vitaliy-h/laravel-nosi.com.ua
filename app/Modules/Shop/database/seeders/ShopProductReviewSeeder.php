<?php

namespace App\Modules\Shop\database\seeders;

use App\Modules\Shop\Models\Product;
use App\Modules\Shop\Models\ProductReview;
use Illuminate\Database\Seeder;

class ShopProductReviewSeeder extends Seeder
{
    /**
     * [product title, name, city, rating, text]
     */
    private const array REVIEWS = [
        ['Лосини на хутрі', 'Олена', 'Київ', 5, 'Ношу вже третій місяць — жодної затяжки. Сидять ідеально, не сповзають. Замовила ще дві пари про запас.'],
        ['Чоловічі кашемірові шкарпетки', 'Андрій', 'Львів', 5, 'Брав набір на подарунок — гарна коробка, все акуратно. Доставка за день, дуже зручно.'],
        ['Махрові термошкарпетки', 'Ірина', 'Одеса', 5, 'Неймовірно м’які та теплі. Після прання не скаталися й не втратили форму. Рекомендую!'],
        ['Мереживні стрінги', 'Марія', 'Харків', 4, 'Тканина приємна, розмір відповідає таблиці. Консультант у Telegram допоміг з вибором.'],
        ['Чоловічі спортивні шкарпетки', 'Дмитро', 'Дніпро', 5, 'Бігаю в них щодня — нога не потіє, п’ята не натирається. Тепер беру тільки ці.'],
        ['Шкарпетки з ведмедиками', 'Катерина', 'Вінниця', 5, 'Купила доньці, тепер вона не хоче знімати :) Яскраві, якісні, ціна приємна.'],
    ];

    /**
     * Run the database seeds.
     *
     * Product rating aggregates are left as ShopProductSeeder set them: the
     * demo catalogue carries the review counts from the design, which these
     * few sample reviews would otherwise overwrite.
     */
    public function run(): void
    {
        ProductReview::truncate();

        $products = Product::pluck('id', 'title');

        foreach (self::REVIEWS as $i => [$productTitle, $name, $city, $rating, $text]) {
            if (!isset($products[$productTitle])) {
                continue;
            }

            ProductReview::create([
                'product_id' => $products[$productTitle],
                'name'       => $name,
                'city'       => $city,
                'rating'     => $rating,
                'text'       => $text,
                'display'    => true,
                'created_at' => now()->subDays(($i + 1) * 3),
            ]);
        }
    }
}
