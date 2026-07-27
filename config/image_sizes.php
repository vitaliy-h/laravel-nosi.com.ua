<?php

/**
 * see docs/starter/images.md
 * Clearing preview cache
 * php artisan images:clear                         # all presets from config/image_sizes.php
 * php artisan images:clear thumb                   # entire thumb/ directory
 * php artisan images:clear thumb shop_products     # only thumb/shop_products/
 */

return [

    'thumb' => [
        'shop_products' => [
            'width'  => 500,
            'height' => 500,
            'mode'   => 'contain',
        ],
        'shop_categories' => [
            'width'  => 300,
            'height' => 300,
            'mode'   => 'crop',
        ],
        'shop_brands' => [
            'width'  => 300,
            'height' => 300,
            'mode'   => 'crop',
        ],
        'news' => [
            'width' => 300,
            'mode'  => 'contain',
        ],
    ],

    'large' => [
        'shop_products' => [
            'width'  => 1200,
            'height' => 1200,
            'mode'   => 'contain',
        ],
        'news' => [
            'width'  => 1200,
            'height' => 800,
            'mode'   => 'contain',
        ],
    ],

];
