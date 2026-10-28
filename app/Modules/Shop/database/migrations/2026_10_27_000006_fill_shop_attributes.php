<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

/**
 * Size, season and material with their values — the list ShopAttributeSeeder
 * builds locally — plus a sample on the live catalogue: 10 random visible
 * products get two neighbouring sizes, a season and a material, so the
 * filter and the specifications have something to show. The rest is set in
 * the admin.
 *
 * A parameter whose code already exists is left alone, and the sample is
 * skipped once any product has a value. A database with no products is left
 * to the seeder.
 */
return new class extends Migration
{
    /**
     * code => [title, values]
     */
    private const array ATTRIBUTES = [
        'size'     => ['Розмір', ['35–37', '38–40', '41–43', '44–46', 'Універсальний']],
        'season'   => ['Сезон', ['Зима', 'Демісезон', 'Літо']],
        'material' => ['Матеріал', ['Бавовна', 'Вовна', 'Бамбук', 'Кашемір', 'Махра']],
    ];

    private const int SAMPLE = 10;

    public function up(): void
    {
        // A fresh install (and the test database) has no catalogue yet:
        // ShopAttributeSeeder builds the parameters there.
        if (!DB::table('shop_products')->exists()) {
            return;
        }

        $now = now();
        $rank = count(self::ATTRIBUTES);
        $options = [];

        foreach (self::ATTRIBUTES as $code => [$title, $values]) {
            $attributeId = DB::table('shop_attributes')->where('code', $code)->value('id');

            if ($attributeId === null) {
                $attributeId = DB::table('shop_attributes')->insertGetId([
                    'code' => $code, 'title' => $title, 'rank' => $rank, 'created_at' => $now, 'updated_at' => $now,
                ]);

                foreach ($values as $i => $value) {
                    DB::table('shop_attribute_options')->insert([
                        'attribute_id' => $attributeId, 'title' => $value, 'rank' => $i + 1, 'created_at' => $now, 'updated_at' => $now,
                    ]);
                }
            }

            $options[$code] = DB::table('shop_attribute_options')->where('attribute_id', $attributeId)
                ->orderBy('rank')->pluck('id')->all();
            $rank--;
        }

        if (DB::table('shop_attribute_option_product')->exists()) {
            return;
        }

        $products = DB::table('shop_products')->where('display', true)->inRandomOrder()
            ->limit(self::SAMPLE)->pluck('id');

        foreach ($products as $productId) {
            $picked = [];

            if (count($options['size']) > 1) {
                $size = random_int(0, count($options['size']) - 2);
                $picked[] = $options['size'][$size];
                $picked[] = $options['size'][$size + 1];
            }

            foreach (['season', 'material'] as $code) {
                if ($options[$code] !== []) {
                    $picked[] = $options[$code][array_rand($options[$code])];
                }
            }

            DB::table('shop_attribute_option_product')->insert(
                array_map(fn (int $optionId) => ['product_id' => $productId, 'option_id' => $optionId], $picked)
            );
        }
    }

    /**
     * Data only: the values typed in the admin since are not told apart from
     * these, so nothing is removed.
     */
    public function down(): void {}
};
