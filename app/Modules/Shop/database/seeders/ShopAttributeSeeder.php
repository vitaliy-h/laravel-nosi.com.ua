<?php

namespace App\Modules\Shop\database\seeders;

use App\Modules\Shop\Models\Attribute;
use App\Modules\Shop\Models\Product;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

/**
 * Size, season and material with their values; the socks of ShopProductSeeder
 * get a few of each, picked by their position so every run gives the same
 * catalogue. Runs after the products.
 */
class ShopAttributeSeeder extends Seeder
{
    /**
     * code => [title, values]
     */
    private const array ATTRIBUTES = [
        'size'     => ['Розмір', ['35–37', '38–40', '41–43', '44–46', 'Універсальний']],
        'season'   => ['Сезон', ['Зима', 'Демісезон', 'Літо']],
        'material' => ['Матеріал', ['Бавовна', 'Вовна', 'Бамбук', 'Кашемір', 'Махра']],
    ];

    public function run(): void
    {
        DB::table('shop_attribute_option_product')->truncate();
        DB::table('shop_attribute_options')->truncate();
        Attribute::truncate();

        $rank = count(self::ATTRIBUTES);
        $options = [];

        foreach (self::ATTRIBUTES as $code => [$title, $values]) {
            $attribute = Attribute::create(['code' => $code, 'title' => $title, 'rank' => $rank--]);

            foreach ($values as $i => $value) {
                $options[$code][] = $attribute->options()->create(['title' => $value, 'rank' => $i + 1])->id;
            }
        }

        $socks = Product::query()->whereIn('category_id', [1, 2, 3])->orderBy('id')->get();

        foreach ($socks as $i => $product) {
            $product->attributeOptions()->sync([
                // two neighbouring sizes
                $options['size'][$i % 4],
                $options['size'][($i % 4) + 1],
                $options['season'][$i % 3],
                $options['material'][$i % 5],
            ]);
        }
    }
}
