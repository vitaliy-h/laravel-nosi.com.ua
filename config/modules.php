<?php

/*
|--------------------------------------------------------------------------
| Modules
|--------------------------------------------------------------------------
|
| Everything is enabled by default. To switch a module off, uncomment its
| line. Only modules that may be switched off are listed here — core CMS
| modules have no line and cannot be disabled.
|
| Disabling a module stops its routes, commands, migrations, seeders and
| admin resources from loading. `shop_brands`, `shop_product_reviews`,
| `shop_promocodes`, `sms`, `letter_templates` and `social_profiles` have no
| folder of their own: they only hide the admin surfaces of code living inside
| Shop / ShopCart / Notification / UserAuth, the tables stay. Social login on the
| site keeps working with `social_profiles` off — only its admin list goes.
|
| Careful with `shop`: product blocks left in page content will error once
| the shop tables stop loading. Clean that content up first.
|
*/

return [

    /*
    |--------------------------------------------------------------------------
    | Switches
    |--------------------------------------------------------------------------
    |
    | Kept under their own key so a module can never collide with `dependencies`
    | and so ModuleGate reads one flat list.
    |
    */
    'list' => [
        // 'user_auth'       => false,
        // 'feedback'        => false,
        'geo'             => false,
        'news'            => false,
        // 'shop'            => false,
        // 'shop_cart'       => false,
        'shop_brands'     => false,
        'shop_promocodes' => false,
        // 'shop_product_reviews'    => false,
        'subscriber'      => false,
        'sms'             => false,
        // 'letter_templates'        => false,
        'social_profiles' => false,
    ],

    /*
    |--------------------------------------------------------------------------
    | Dependencies
    |--------------------------------------------------------------------------
    |
    | A module here goes off together with its parent, whatever its own line
    | says: `shop => false` also takes down the cart, brands, reviews, and
    | through the cart the orders and promocodes. ShopOrder and Profile have
    | no line of their own above — they exist only as dependants.
    |
    */
    'dependencies' => [
        'shop_cart'            => 'shop',
        'shop_brands'          => 'shop',
        'shop_product_reviews' => 'shop',
        'shop_order'           => 'shop_cart',
        'shop_promocodes'      => 'shop_cart',
        'profile'              => 'user_auth',
        'social_profiles'      => 'user_auth',
    ],
];
