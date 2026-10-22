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
 * `price` is the regular price; `price2`, when set and different, is the
 * current discounted one and `price` is shown crossed out.
 */
class ShopProductSeeder extends Seeder
{
    /**
     * [photo, title, brand, category id, price, price2, is_hit, is new, rating, reviews_count]
     */
    private const array PRODUCTS = [
        // top sales
        ['01', 'Жіночі шкарпетки Cashmere, парфумовані', 'Nosi', 1, 89, null, true, false, 5, 214],
        ['02', 'Чоловічі шкарпетки з начосом', 'DMDBS', 2, 79, null, true, false, 5, 162],
        ['03', 'Махрові термошкарпетки', 'Корона', 1, 99, null, true, false, 4.8, 96],
        ['04', 'Шкарпетки «Дикий шовк», парфумовані', 'DMDBS', 1, 85, null, true, false, 4.4, 71],
        ['05', 'Чоловічі кашемірові шкарпетки', 'DMDBS', 2, 89, null, true, false, 5, 128],
        ['06', 'Короткі кашемірові шкарпетки', 'DMDBS', 1, 75, null, true, false, 4.3, 54],
        ['07', 'Шкарпетки-слідки з принтами', 'COALO', 3, 59, null, true, false, 4.9, 143],
        ['08', 'Шкарпетки з ведмедиками', 'COALO', 3, 79, null, true, false, 4.8, 88],
        ['09', 'Вовняні шкарпетки меланж', 'Золото', 1, 119, null, true, false, 4.9, 117],
        ['10', 'Чоловічі шкарпетки «Норка»', 'DMDBS', 2, 109, null, true, false, 4.7, 63],
        ['11', 'Сорочка в клітинку на хутрі', 'Nosi', 7, 899, null, true, false, 4.2, 22],
        ['16', 'Лосини на хутрі', 'KENALIN', 7, 549, null, true, false, 4.9, 176],
        // new arrivals
        ['12', 'Мереживні стрінги', 'Nosi', 6, 129, null, false, true, 5, 12],
        ['13', 'Мереживні стрінги 729', 'BIWEIER', 6, 119, null, false, true, 4.9, 8],
        ['14', 'Мереживні стрінги 720', 'BIWEIER', 6, 119, null, false, true, 4.3, 6],
        ['15', 'Стрінги на тонких бретелях', 'Nosi', 6, 99, null, false, true, 4.8, 15],
        ['17', 'Лосини на хутрі, XL–6XL', 'Nosi', 7, 449, null, false, true, 5, 9],
        ['18', 'Жіночі джогери на хутрі', 'AKEU', 7, 799, null, false, true, 5, 4],
        ['19', 'Шкарпетки з вишивкою', 'COALO', 1, 69, null, false, true, 4.4, 11],
        ['20', 'Бамбукові шкарпетки, парфумовані', 'DMDBS', 2, 75, null, false, true, 4.9, 7],
        ['26', 'Чоловічі штани на флісі', 'KENALIN', 7, 899, null, false, true, 5, 3],
        ['29', 'Чоловічі штани на байці', 'Ластівка', 7, 849, null, false, true, 4.4, 5],
        // sale
        ['21', 'Короткі білі шкарпетки', 'DMDBS', 1, 79, 49, false, false, 4.9, 167],
        ['22', 'Шкарпетки з сіткою', 'DMDBS', 1, 85, 55, false, false, 4.8, 88],
        ['23', 'Чоловічі спортивні шкарпетки', 'DMDBS', 2, 95, 59, false, false, 4.4, 64],
        ['24', 'Худі на хутрі', 'Nosi', 7, 1399, 899, false, false, 5, 14],
        ['25', 'Джогери на флісі', 'Nosi', 7, 799, 549, false, false, 4.3, 19],
        ['27', 'Жіночі джогери на флісі', 'KENALIN', 7, 999, 649, false, false, 4.9, 41],
        ['28', 'Трусики «Перфорація», великі розміри', 'Lulola', 6, 115, 69, false, false, 4.4, 22],
        ['30', 'Чоловічі вовняні шкарпетки', 'Золото', 2, 165, 99, false, false, 4.9, 54],
    ];

    public function run(): void
    {
        Product::truncate();
        Storage::disk('public')->deleteDirectory('shop_products');

        $brands = array_flip(ShopBrandSeeder::BRANDS);
        $rank = count(self::PRODUCTS);

        foreach (self::PRODUCTS as $i => [$photo, $title, $brand, $categoryId, $price, $price2, $isHit, $isNew, $rating, $reviewsCount]) {
            $id = $i + 1;
            $slug = Str::slug($title);

            // Rating aggregates are normally maintained by ProductReviewObserver;
            // the demo catalogue carries the numbers from the design instead.
            $product = new Product;
            $product->forceFill([
                'title'            => $title,
                'slug'             => $slug,
                'article'          => 'NS-' . str_pad((string) $id, 4, '0', STR_PAD_LEFT),
                'price'            => $price,
                'price2'           => $price2,
                'is_hit'           => $isHit,
                'category_id'      => $categoryId,
                'brand_id'         => $brands[$brand] + 1,
                'rank'             => $rank--,
                'display'          => true,
                'rating'           => $rating,
                'reviews_count'    => $reviewsCount,
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

    private function copyPhoto(int $id, string $field, string $source, string $name): string
    {
        $path = "shop_products/{$id}/{$field}/{$name}.jpg";
        Storage::disk('public')->put($path, File::get(public_path("demo/nosi/products/{$source}")));

        return $path;
    }
}
