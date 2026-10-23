<?php

namespace App\Modules\Review\database\seeders;

use App\Modules\Review\Models\Review;
use Illuminate\Database\Seeder;

class ReviewSeeder extends Seeder
{
    /**
     * [name, city, rating, text]
     */
    private const array REVIEWS = [
        ['Олена', 'Київ', 5, 'Замовляю вже втретє — все приходить швидко й акуратно запаковане. Якість на висоті, жодної затяжки.'],
        ['Андрій', 'Львів', 5, 'Брав набір на подарунок — гарна коробка, все акуратно. Доставка за день, дуже зручно.'],
        ['Ірина', 'Одеса', 5, 'Приємно здивувала швидкість: оформила ввечері, наступного дня вже забрала на пошті. Рекомендую!'],
        ['Марія', 'Харків', 4, 'Консультант у Telegram допоміг з розміром і відповів за кілька хвилин. Розмір підійшов ідеально.'],
        ['Дмитро', 'Дніпро', 5, 'Чесні ціни і великий вибір. Тепер усі шкарпетки для родини беру тільки тут.'],
        ['Катерина', 'Вінниця', 5, 'Обміняли розмір без жодних питань. Дуже приємний сервіс, буду замовляти ще.'],
    ];

    public function run(): void
    {
        Review::truncate();

        foreach (self::REVIEWS as $i => [$name, $city, $rating, $text]) {
            Review::create([
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
