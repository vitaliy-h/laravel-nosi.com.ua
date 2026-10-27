<?php

namespace App\Modules\Shop\database\seeders;

use App\Modules\Shop\Models\Product;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

/**
 * The catalogue from the home page design. Photos come from
 * public/demo/nosi/products/{NN}.jpg (main) and {NN}-hover.jpg (second photo,
 * shown on hover — stored as the first of `imgs`).
 *
 * Socks (categories 1–3) are sold by one, five and ten pairs, the sets a little
 * cheaper per pair; everything else by the piece.
 */
class ShopProductSeeder extends Seeder
{
    /** Categories sold in sets of pairs. */
    private const array SOCKS = [1, 2, 3];

    /**
     * [photo, title, brand, category id, price, is_hit, is new]
     */
    private const array PRODUCTS = [
        // top sales
        ['01', 'Жіночі шкарпетки Cashmere, парфумовані', 'Nosi', 1, 89, true, false],
        ['02', 'Чоловічі шкарпетки з начосом', 'DMDBS', 2, 79, true, false],
        ['03', 'Махрові термошкарпетки', 'Корона', 1, 99, true, false],
        ['04', 'Шкарпетки «Дикий шовк», парфумовані', 'DMDBS', 1, 85, true, false],
        ['05', 'Чоловічі кашемірові шкарпетки', 'DMDBS', 2, 89, true, false],
        ['06', 'Короткі кашемірові шкарпетки', 'DMDBS', 1, 75, true, false],
        ['07', 'Шкарпетки-слідки з принтами', 'COALO', 3, 59, true, false],
        ['08', 'Шкарпетки з ведмедиками', 'COALO', 3, 79, true, false],
        ['09', 'Вовняні шкарпетки меланж', 'Золото', 1, 119, true, false],
        ['10', 'Чоловічі шкарпетки «Норка»', 'DMDBS', 2, 109, true, false],
        ['11', 'Сорочка в клітинку на хутрі', 'Nosi', 7, 899, true, false],
        ['16', 'Лосини на хутрі', 'KENALIN', 7, 549, true, false],
        // new arrivals
        ['12', 'Мереживні стрінги', 'Nosi', 6, 129, false, true],
        ['13', 'Мереживні стрінги 729', 'BIWEIER', 6, 119, false, true],
        ['14', 'Мереживні стрінги 720', 'BIWEIER', 6, 119, false, true],
        ['15', 'Стрінги на тонких бретелях', 'Nosi', 6, 99, false, true],
        ['17', 'Лосини на хутрі, XL–6XL', 'Nosi', 7, 449, false, true],
        ['18', 'Жіночі джогери на хутрі', 'AKEU', 7, 799, false, true],
        ['19', 'Шкарпетки з вишивкою', 'COALO', 1, 69, false, true],
        ['20', 'Бамбукові шкарпетки, парфумовані', 'DMDBS', 2, 75, false, true],
        ['26', 'Чоловічі штани на флісі', 'KENALIN', 7, 899, false, true],
        ['29', 'Чоловічі штани на байці', 'Ластівка', 7, 849, false, true],
        // more
        ['21', 'Короткі білі шкарпетки', 'DMDBS', 1, 79, false, false],
        ['22', 'Шкарпетки з сіткою', 'DMDBS', 1, 85, false, false],
        ['23', 'Чоловічі спортивні шкарпетки', 'DMDBS', 2, 95, false, false],
        ['24', 'Худі на хутрі', 'Nosi', 7, 1399, false, false],
        ['25', 'Джогери на флісі', 'Nosi', 7, 799, false, false],
        ['27', 'Жіночі джогери на флісі', 'KENALIN', 7, 999, false, false],
        ['28', 'Трусики «Перфорація», великі розміри', 'Lulola', 6, 115, false, false],
        ['30', 'Чоловічі вовняні шкарпетки', 'Золото', 2, 165, false, false],
    ];

    public function run(): void
    {
        Product::truncate();
        Storage::disk('public')->deleteDirectory('shop_products');

        $brands = array_flip(ShopBrandSeeder::BRANDS);
        $rank = count(self::PRODUCTS);

        foreach (self::PRODUCTS as $i => [$photo, $title, $brand, $categoryId, $price, $isHit, $isNew]) {
            $id = $i + 1;
            $slug = Str::slug($title);

            $product = new Product;
            $product->forceFill([
                'title'            => $title,
                'slug'             => $slug,
                'article'          => 'NS-' . str_pad((string) $id, 4, '0', STR_PAD_LEFT),
                'packs'            => $this->packs($categoryId, $price),
                'is_hit'           => $isHit,
                'category_id'      => $categoryId,
                'brand_id'         => $brands[$brand] + 1,
                'rank'             => $rank--,
                'display'          => true,
                'img'              => $this->copyPhoto($id, 'img', "{$photo}.jpg", $slug),
                'imgs'             => [$this->copyPhoto($id, 'imgs', "{$photo}-hover.jpg", $slug . '-2')],
                'meta_title'       => $title,
                'meta_description' => $title . ' — купити в інтернет-магазині Nosi.',
                // «Новинки» is the newest products by date.
                'created_at' => $isNew ? now()->subMinutes($id) : now()->subDays(30 + $id),
                'updated_at' => now(),
            ])->save();
        }
    }

    /**
     * @return array<int, array{quantity: int, price: float}>
     */
    private function packs(int $categoryId, float $price): array
    {
        if (!in_array($categoryId, self::SOCKS, true)) {
            return [['quantity' => 1, 'price' => $price]];
        }

        return [
            ['quantity' => 1, 'price' => $price],
            ['quantity' => 5, 'price' => round($price * 5 * 0.9)],
            ['quantity' => 10, 'price' => round($price * 10 * 0.8)],
        ];
    }

    private function copyPhoto(int $id, string $field, string $source, string $name): string
    {
        $path = "shop_products/{$id}/{$field}/{$name}.jpg";
        Storage::disk('public')->put($path, File::get(public_path("demo/nosi/products/{$source}")));

        return $path;
    }
}
