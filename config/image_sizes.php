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

    // Storefront tiles, cut to the tile's aspect ratio (2× its largest CSS size, for retina):
    // the product card and the product page thumbs are 3:4, the home category tile 4:5.
    'card' => [
        'shop_products' => [
            'width'  => 600,
            'height' => 800,
            'mode'   => 'crop',
        ],
        'shop_categories' => [
            'width'  => 600,
            'height' => 750,
            'mode'   => 'crop',
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
        // The home hero slider (full container width) and the promo banners.
        'sliders' => [
            'width'  => 1920,
            'height' => 1080,
            'mode'   => 'contain',
        ],
        'promos' => [
            'width'  => 1200,
            'height' => 1000,
            'mode'   => 'contain',
        ],
    ],

];
