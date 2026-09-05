<?php

namespace App\Modules\ShopCart\database\seeders;

use App\Modules\ShopCart\Enums\PromocodeTypeEnum;
use App\Modules\ShopCart\Models\Promocode;
use Illuminate\Database\Seeder;

class PromocodeSeeder extends Seeder
{
    public function run(): void
    {
        Promocode::truncate();

        $promocodes = [
            [
                'code'         => 'WELCOME10',
                'type'         => PromocodeTypeEnum::PERCENT,
                'value'        => 10,
                'max_discount' => 50,
                'display'      => true,
                'comment'      => 'Demo: 10% off, capped at $50.',
            ],
            [
                'code'             => 'MINUS20',
                'type'             => PromocodeTypeEnum::FIXED,
                'value'            => 20,
                'min_order_amount' => 100,
                'usage_limit'      => 100,
                'display'          => true,
                'comment'          => 'Demo: $20 off orders from $100.',
            ],
            [
                'code'       => 'EXPIRED',
                'type'       => PromocodeTypeEnum::PERCENT,
                'value'      => 50,
                'starts_at'  => now()->subMonth(),
                'expires_at' => now()->subDay(),
                'display'    => true,
                'comment'    => 'Demo: expired code.',
            ],
        ];

        foreach ($promocodes as $promocode) {
            Promocode::create($promocode);
        }
    }
}
