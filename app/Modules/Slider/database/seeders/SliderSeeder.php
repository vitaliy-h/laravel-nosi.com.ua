<?php

namespace App\Modules\Slider\database\seeders;

use App\Modules\Slider\Models\Slider;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Storage;

class SliderSeeder extends Seeder
{
    public function run(): void
    {
        Slider::truncate();
        Storage::disk('public')->deleteDirectory('sliders');

        $slides = [
            [
                'kicker'      => 'Нова колекція осінь–зима',
                'title'       => 'Тепло з перших кроків',
                'text'        => 'Вовняні, махрові та кашемірові шкарпетки для холодних днів.',
                'button_text' => 'До колекції',
                'url'         => '/shop',
            ],
            [
                'kicker'      => 'Акція тижня',
                'title'       => '3 пари за ціною 2',
                'text'        => 'Збирайте свій набір з будь-яких шкарпеток — третя пара в подарунок.',
                'button_text' => 'Зібрати набір',
                'url'         => '#sale',
            ],
            [
                'kicker'      => 'Колготки та панчохи',
                'title'       => 'Від 20 до 200 DEN',
                'text'        => 'Класика, фантазійні візерунки та теплі моделі від провідних брендів.',
                'button_text' => 'Обрати колготки',
                'url'         => '/shop/kolgotky',
            ],
        ];

        $rank = count($slides);

        foreach ($slides as $i => $slide) {
            $id = $i + 1;
            $dir = "sliders/{$id}/img/";
            Storage::disk('public')->put($dir . "slide-{$id}.jpg", File::get(public_path("demo/nosi/slider/{$id}.jpg")));

            Slider::create($slide + [
                'img'     => $dir . "slide-{$id}.jpg",
                'rank'    => $rank--,
                'display' => true,
            ]);
        }
    }
}
