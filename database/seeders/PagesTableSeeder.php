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
                'title'               => 'Main Page',
                'slug'                => '',
                'module'              => PageModule::INDEX,
                'display'             => true,
                'display_menu'        => false,
                'display_menu_footer' => false,
                'is_indexable'        => true,
                'meta_title'          => 'Main Page',
                'meta_description'    => 'Main Page',
                'content'             => [
                    [
                        'type' => 'teasers',
                        'data' => [
                            'title' => '',
                            'style' => 'default',
                            'items' => [
                                [
                                    'icon'  => 'bi-truck',
                                    'title' => 'Free Shipping',
                                    'text'  => 'Nulla sit morbi vestibulum eros duis amet, consectetur vitae lacus. Ut quis tempor felis sed nunc viverra.',
                                ],
                                [
                                    'icon'  => 'bi-piggy-bank',
                                    'title' => 'Money Back Guarantee',
                                    'text'  => 'Nullam gravida felis ac nunc tincidunt, sed malesuada justo pulvinar. Vestibulum nec diam vitae eros.',
                                ],
                                [
                                    'icon'  => 'bi-percent',
                                    'title' => 'Discount Offers',
                                    'text'  => 'Nulla ipsum nisi vel adipiscing amet, dignissim consectetur ornare. Vestibulum quis posuere elit auctor.',
                                ],
                                [
                                    'icon'  => 'bi-headset',
                                    'title' => '24/7 Support',
                                    'text'  => 'Ipsum dolor amet sit consectetur adipiscing, nullam vitae euismod tempor nunc felis vestibulum ornare.',
                                ],
                            ],
                        ],
                    ],
                ],
            ],
            [
                'title'               => 'News',
                'slug'                => 'news',
                'module'              => PageModule::NEWS,
                'display'             => true,
                'display_menu'        => true,
                'display_menu_footer' => false,
                'is_indexable'        => true,
                'meta_title'          => 'News',
                'meta_description'    => 'News',
            ],
            [
                'title'               => 'Feedback',
                'slug'                => 'feedback',
                'module'              => PageModule::FEEDBACK,
                'display'             => true,
                'display_menu'        => true,
                'display_menu_footer' => true,
                'is_indexable'        => true,
                'meta_title'          => 'Feedback',
                'meta_description'    => 'Feedback',
            ],
            [
                'title'               => 'Shop',
                'slug'                => 'shop',
                'module'              => PageModule::SHOP,
                'display'             => true,
                'display_menu'        => true,
                'display_menu_footer' => true,
                'is_indexable'        => true,
                'meta_title'          => 'Shop',
                'meta_description'    => 'Shop',
            ],
            [
                'title'               => 'Cart',
                'slug'                => 'cart',
                'module'              => PageModule::SHOP_CART,
                'display'             => true,
                'display_menu'        => true,
                'display_menu_footer' => true,
                'is_indexable'        => false,
                'meta_title'          => 'Cart',
                'meta_description'    => 'Cart',
            ],
            [
                'title'               => 'Auth',
                'slug'                => 'auth',
                'module'              => PageModule::AUTH,
                'display'             => true,
                'display_menu'        => false,
                'display_menu_footer' => false,
                'is_indexable'        => false,
                'meta_title'          => 'Auth',
                'meta_description'    => 'Auth',
            ],
            [
                'title'               => 'Profile',
                'slug'                => 'profile',
                'module'              => PageModule::PROFILE,
                'display'             => true,
                'display_menu'        => false,
                'display_menu_footer' => false,
                'is_indexable'        => false,
                'meta_title'          => 'Profile',
                'meta_description'    => 'Profile',
            ],
            [
                'title'               => 'Text Page',
                'slug'                => 'text-page-1',
                'module'              => PageModule::TEXT,
                'display'             => true,
                'display_menu'        => true,
                'display_menu_footer' => true,
                'meta_title'          => 'Text Page 1',
                'meta_description'    => 'Text Page 1',
                'content'             => [
                    [
                        'type' => 'text',
                        'data' => [
                            'style'   => 'default',
                            'title'   => null,
                            'content' => '<p>text page 1</p>',
                        ],
                    ],
                ],
            ],
            [
                'title'               => 'Text Page 2',
                'slug'                => 'text-page-2',
                'module'              => PageModule::TEXT,
                'display'             => true,
                'display_menu'        => true,
                'display_menu_footer' => true,
                'meta_title'          => 'Text Page 2',
                'meta_description'    => 'Text Page 2',
                'content'             => [
                    [
                        'type' => 'text',
                        'data' => [
                            'style'   => 'default',
                            'title'   => null,
                            'content' => '<p>text page 2</p>',
                        ],
                    ],
                ],
            ],
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
