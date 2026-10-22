<?php

namespace App\Modules\Promo\database\seeders;

use App\Modules\Promo\Enums\PromoStyleEnum;
use App\Modules\Promo\Models\Promo;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Storage;

class PromoSeeder extends Seeder
{
    public function run(): void
    {
        Promo::truncate();
        Storage::disk('public')->deleteDirectory('promos');

        $dir = 'promos/1/img/';
        Storage::disk('public')->put($dir . 'promo-1.jpg', File::get(public_path('demo/nosi/promo/1.jpg')));

        Promo::create([
            'rank'        => 2,
            'style'       => PromoStyleEnum::PLUM,
            'kicker'      => 'Подарунок без вагань',
            'title'       => 'Набори в коробці',
            'text'        => '5 або 7 пар у святковій упаковці з листівкою — готовий подарунок до будь-якої дати.',
            'button_text' => 'Обрати набір',
            'url'         => '/shop/podarunkovi-nabory',
            'img'         => $dir . 'promo-1.jpg',
        ]);

        Promo::create([
            'rank'        => 1,
            'style'       => PromoStyleEnum::ACCENT,
            'kicker'      => 'Тільки до неділі',
            'big_text'    => '−40%',
            'text'        => 'На колготки та панчохи минулих колекцій.',
            'button_text' => 'До розпродажу',
            'url'         => '#sale',
        ]);
    }
}
