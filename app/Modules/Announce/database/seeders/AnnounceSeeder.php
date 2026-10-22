<?php

namespace App\Modules\Announce\database\seeders;

use App\Modules\Announce\Models\Announce;
use Illuminate\Database\Seeder;

class AnnounceSeeder extends Seeder
{
    public function run(): void
    {
        Announce::truncate();

        $items = [
            'Безкоштовна доставка від 800 ₴',
            '3 пари шкарпеток за ціною 2',
            'Відправка в день замовлення до 16:00',
            'Обмін протягом 14 днів',
        ];

        $rank = count($items);

        foreach ($items as $title) {
            Announce::create([
                'title'   => $title,
                'rank'    => $rank--,
                'display' => true,
            ]);
        }
    }
}
