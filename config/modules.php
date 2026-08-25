<?php

/*
|--------------------------------------------------------------------------
| Toggleable modules
|--------------------------------------------------------------------------
|
| Only modules listed below can be disabled. Everything else under
| app/Modules (Core, Index, TextPage, Sitemap, Translation, Notification,
| _debug, ...) is a core module with no key here and is always enabled —
| see App\Modules\Core\Support\ModuleGate.
|
| A key does NOT have to match an app/Modules folder: `shop_brands`,
| `shop_reviews`, `shop_promocodes` and `sms` are feature toggles for code
| that lives inside Shop / ShopCart / Notification. Their tables and models
| always exist, but every admin surface they own (navigation item, resource,
| dashboard card) is gated on the key, so switching it off removes the
| feature from the panel.
|
| Modules that have their own key here still get disabled if listed as a
| dependent of a disabled module below (e.g. shop => false also disables
| shop_cart even though shop_cart has no key of its own).
|
| `shop` CAN be toggled, but disabling it only stops its routes/commands/
| migrations/seeders/admin resources from loading — it does NOT remove
| shop data from views that render it outside the Shop module itself
| (e.g. teasers/featured products on the homepage). Disabling shop with
| existing product blocks left in page content will error when those
| blocks try to query the (now unloaded) shop tables. Remove/replace that
| content manually before disabling shop.
|
*/

return [
    'auth'            => true,
    'feedback'        => true,
    'geo'             => true,
    'news'            => true,
    'shop'            => true,
    'shop_cart'       => true,
    'shop_brands'     => true,
    'shop_promocodes' => true,
    'shop_reviews'    => true,
    'subscribers'     => true,
    'sms'             => true,

    /*
    |----------------------------------------------------------------------
    | Module dependencies
    |----------------------------------------------------------------------
    |
    | Modules that only make sense together with a parent module: no cart
    | or orders without the shop, no profile (and its social_profiles
    | table) without auth. A dependent module is enabled only when its own
    | key (if any) AND its parent are both enabled — see
    | App\Modules\Core\Support\ModuleGate::isEnabled().
    |
    */
    'dependencies' => [
        'shop_cart'       => 'shop',
        'shop_orders'     => 'shop_cart',
        'shop_brands'     => 'shop',
        'shop_reviews'    => 'shop',
        'shop_promocodes' => 'shop_cart',
        'profile'         => 'auth',
    ],
];
