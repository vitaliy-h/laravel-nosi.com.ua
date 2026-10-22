<?php

namespace Database\Seeders;

use App\Modules\Page\Enums\PageModule;
use App\Modules\Page\Models\Page;
use Illuminate\Database\Seeder;

class PagesTableSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $pages = [
            [
                'title'               => 'Головна',
                'slug'                => '',
                'module'              => PageModule::INDEX,
                'display'             => true,
                'display_menu'        => false,
                'display_menu_footer' => false,
                'is_indexable'        => true,
                'meta_title'          => 'Nosi — шкарпетки, колготки та білизна з доставкою по Україні',
                'meta_description'    => 'Інтернет-магазин шкарпеток, колготок і білизни: оригінальні бренди, доставка Новою поштою, обмін 14 днів.',
                // Text blocks of the home page are its SEO text («Читати повністю»).
                'content' => [
                    [
                        'type' => 'text',
                        'data' => [
                            'style'   => 'default',
                            'title'   => 'Інтернет-магазин шкарпеток, колготок та білизни Nosi',
                            'content' => '<p>Nosi — це інтернет-магазин, де легко підібрати шкарпетки, колготки та білизну для всієї родини. У каталозі зібрані моделі на кожен день, для спорту, офісу та особливих подій — від базових бавовняних пар до теплих вовняних і фантазійних колготок.</p>'
                                . '<h3>Шкарпетки для жінок, чоловіків і дітей</h3>'
                                . '<p>Ми пропонуємо класичні, короткі та високі шкарпетки, сліди, спортивні та термошкарпетки. Бавовна з еластаном тримає форму після багатьох прань, а вовна і кашемір зігріють навіть у морози.</p>'
                                . '<h3>Колготки та панчохи будь-якої щільності</h3>'
                                . '<p>Тонкі колготки 15–20 DEN для літа, універсальні 40 DEN для щоденного образу, щільні 100–200 DEN для зими. Окремо — панчохи, гольфи, моделі з візерунком і корекційні колготки.</p>'
                                . '<h3>Чому обирають нас</h3>'
                                . '<ul><li>оригінальна продукція від офіційних постачальників;</li><li>понад 3 000 моделей у наявності на складі;</li><li>відправка в день замовлення до 16:00;</li><li>обмін протягом 14 днів.</li></ul>'
                                . '<h3>Доставка по всій Україні</h3>'
                                . '<p>Доставляємо Новою поштою та Укрпоштою у будь-яке місто: Київ, Львів, Харків, Одеса, Дніпро та інші. Замовлення можна оплатити онлайн карткою або при отриманні.</p>'
                                . '<p>Підписуйтесь на розсилку, щоб першими дізнаватися про новинки та сезонні розпродажі.</p>',
                        ],
                    ],
                ],
            ],
            [
                'title'               => 'Новини',
                'slug'                => 'news',
                'module'              => PageModule::NEWS,
                'display'             => true,
                'display_menu'        => true,
                'display_menu_footer' => false,
                'is_indexable'        => true,
                'meta_title'          => 'Новини',
                'meta_description'    => 'Новини',
            ],
            [
                'title'               => 'Зворотний зв’язок',
                'slug'                => 'feedback',
                'module'              => PageModule::FEEDBACK,
                'display'             => true,
                'display_menu'        => true,
                'display_menu_footer' => false,
                'is_indexable'        => true,
                'meta_title'          => 'Зворотний зв’язок',
                'meta_description'    => 'Зворотний зв’язок',
            ],
            [
                'title'               => 'Каталог',
                'slug'                => 'shop',
                'module'              => PageModule::SHOP,
                'display'             => true,
                'display_menu'        => true,
                'display_menu_footer' => false,
                'is_indexable'        => true,
                'meta_title'          => 'Каталог',
                'meta_description'    => 'Каталог шкарпеток, колготок і білизни Nosi.',
            ],
            [
                'title'               => 'Кошик',
                'slug'                => 'cart',
                'module'              => PageModule::SHOP_CART,
                'display'             => true,
                'display_menu'        => true,
                'display_menu_footer' => false,
                'is_indexable'        => false,
                'meta_title'          => 'Кошик',
                'meta_description'    => 'Кошик',
            ],
            [
                'title'               => 'Вхід',
                'slug'                => 'auth',
                'module'              => PageModule::AUTH,
                'display'             => true,
                'display_menu'        => false,
                'display_menu_footer' => false,
                'is_indexable'        => false,
                'meta_title'          => 'Вхід',
                'meta_description'    => 'Вхід',
            ],
            [
                'title'               => 'Кабінет',
                'slug'                => 'profile',
                'module'              => PageModule::PROFILE,
                'display'             => true,
                'display_menu'        => false,
                'display_menu_footer' => false,
                'is_indexable'        => false,
                'meta_title'          => 'Кабінет',
                'meta_description'    => 'Кабінет',
            ],
            // «Покупцям» column of the footer — every text page with display_menu_footer.
            ...array_map(fn (array $page) => [
                'title'               => $page[0],
                'slug'                => $page[1],
                'module'              => PageModule::TEXT,
                'display'             => true,
                'display_menu'        => false,
                'display_menu_footer' => $page[3],
                'meta_title'          => $page[0],
                'meta_description'    => $page[0] . ' — інтернет-магазин Nosi.',
                'content'             => [
                    [
                        'type' => 'text',
                        'data' => [
                            'style'   => 'default',
                            'title'   => null,
                            'content' => '<p>' . $page[2] . '</p>',
                        ],
                    ],
                ],
            ], [
                ['Доставка та оплата', 'dostavka-ta-oplata', 'Відправляємо щодня Новою поштою та Укрпоштою. Від 800 ₴ доставка безкоштовна. Оплата карткою онлайн або при отриманні.', true],
                ['Обмін і повернення', 'obmin-i-povernennia', 'Обмін і повернення протягом 14 днів, якщо виріб не був у використанні та збережено упаковку.', true],
                ['Таблиця розмірів', 'tablytsia-rozmiriv', 'Розмір взуття 36–38 — шкарпетки 23–25, 39–42 — 25–27, 43–46 — 27–29.', true],
                ['Політика конфіденційності', 'polityka-konfidentsiinosti', 'Ми використовуємо ваші дані лише для обробки замовлень і не передаємо їх третім особам.', false],
            ]),
        ];
        // Skip pages whose module is switched off in config/modules.php.
        $pages = array_filter($pages, static fn (array $page) => $page['module']->isEnabled());

        $rank = 100;
        foreach ($pages as $page) {
            $page['rank'] = $rank--;
            $pageExist = Page::where('slug', $page['slug'])->first();
            if (!$pageExist) {
                $pageExist = new Page;
            }
            $pageExist->fill($page);
            $pageExist->save();
        }
    }
}
