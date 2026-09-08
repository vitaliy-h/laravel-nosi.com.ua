# View Models

A ViewModel is the last layer before blade. It is fed an Eloquent model and
exposes fields that are **already prepared for rendering**.

Blade asks the ViewModel for a field. It does not walk relations, does not
format numbers or dates, and does not build URLs:

```blade
{{-- before --}}
<a href="{{ save_route('shop.product', [$product->category->slug, $product->slug_with_id]) }}">
{{ t('shop.currency') }}{{ number_format($order->total_amount, 2) }}
{{ $news->published_at->format('d.m.Y H:i') }}

{{-- after --}}
<a href="{{ $product->url }}">
{{ $order->total_amount_formatted }}
{{ $news->published_at_formatted }}
```

The reason is not tidiness. A relation touched inside a `@foreach` is an N+1
query the template hides; a format repeated in four blades drifts apart; and a
model handed to a view exposes every column it happens to have, including the
ones nobody meant to publish.

---

# Where they live

```text
app/Modules/{Module}/ViewModels/
```

The base class is `App\Modules\Core\Abstract\BaseViewModel`, built on
`spatie/laravel-data`.

Two levels:

**1. `{Model}ViewModel`** — one entity, reused across screens. The same
`ProductViewModel` serves the home page teaser, the category listing and the
cart modal.

**2. `{Page}ViewModel`** — one screen, composing the entity ViewModels with
whatever else that page shows: breadcrumbs, a filter, the active menu section.

---

# Writing one

Construction goes through spatie's magical creation: declare
`from{Type}()` and callers write `::from()`. Do **not** override `from()`
itself — its signature belongs to `Data`.

```php
namespace App\Modules\Shop\ViewModels;

use App\Modules\Core\Abstract\BaseViewModel;
use App\Modules\Shop\Models\Product;

class ProductViewModel extends BaseViewModel
{
    public function __construct(
        public readonly int $id,
        public readonly string $title,
        public readonly ?string $url,
        public readonly bool $has_discount,
        public readonly ?CategoryViewModel $category,
    ) {}

    public static function fromProduct(Product $product): self
    {
        $category = $product->relationLoaded('category') ? $product->category : null;

        return new self(
            id: $product->id,
            title: (string) $product->title,
            url: $category
                ? save_route('shop.product', [$category->slug, $product->slug_with_id])
                : null,
            has_discount: (bool) $product->price2,
            category: $category ? CategoryViewModel::from($category) : null,
        );
    }
}
```

Fields are `readonly` and named in **snake_case** — the name a page ViewModel
gives a property is the name blade sees as a variable.

## Collections and paginators

`collect()` handles both, and it keeps a paginator a paginator, so `links()`
still works in the template:

```php
ProductViewModel::collect($products);
```

## Handing a page to the view

`toViewData()` turns a page ViewModel's public properties into view variables.
Unlike `toArray()` it does **not** flatten nested ViewModels — blade gets the
object:

```php
$page = new ShopProductPageViewModel(
    product: ProductViewModel::from($product),
    category: CategoryViewModel::from($category),
    cart_quantity: $quantity,
    reviews: ProductReviewViewModel::collect($reviews),
    rating: ProductRatingViewModel::forProduct($product, $breakdown),
);

return $this->render('modules.shop.product', $page->toViewData());
```

`toArray()` stays what tests assert against.

Layout variables (`page`, `breadcrumbs`, `header_menu`, `footer_menu`,
`cart_count`, meta) come from `LayoutViewModel`, which `PageContextSharer`
builds once per request and shares — a controller never assembles them itself.
For a screen with no CMS page behind it there is `shareWithoutPage()`.

`PageContextSharer` runs per request, so it is the one allowed to read the request
and the cart session; the ViewModels get the values as arguments. That is why
`MenuItemViewModel::fromPage($page, $currentPath)` is handed the current path
instead of asking `request()` which page is open — a ViewModel that reads ambient
state renders differently depending on who calls it. See `.claude/backend.md`,
«Request-scoped state».

---

# What a ViewModel must not do

**Query the database.** It receives loaded models, so eager-loading is the
caller's job. A ViewModel that touches a lazy relation inside a collection is an
N+1 waiting to happen. When a relation is missing, degrade — return `null` for
the URL — rather than fetch it:

```php
$category = $product->relationLoaded('category') ? $product->category : null;
```

**Reach into a module that can be switched off.** Check `ModuleGate` in the
caller and pass the result in. See [Disabling Modules](modules.md).

**Carry anything the page does not render.** No password hashes, no OAuth
tokens, no internal flags. If a test wants a field the template never shows,
fix the test, not the ViewModel.

---

# Formatting money

The ViewModel formats amounts; the model never did and no longer pretends to.
`Core\Support\Money` is the value object behind it — it rounds to the two
decimals the schema stores and owns the currency symbol:

```php
subtotal_amount_formatted: Money::from($cart->subtotal_amount)->format(),
has_discount: Money::from($cart->discount_amount)->isPositive(),
```

`Money` is not an Eloquent cast: `shop_*.total_amount` and friends stay
`decimal:2`, so Filament, the dashboard queries and the API keep reading plain
numbers. It is built where an amount is used — here to print it, and in an
Action (`RecalculateCartTotalAction`, `RecalculateOrderTotalAction`) to add and
subtract it.

---

# What stays outside the layer

**Input DTOs.** `ShopFilterData`, `OrderFilterData` and friends are rendered by
the page, but the same objects also build the query — they belong to the input
side and stay DTOs.

**`ActionResult`.** A partial that prints `success` / `message` from an Action's
result is not rendering an entity; no ViewModel is needed for two strings. See
[Action Return Values](actions.md).

**Presentation choices.** A ViewModel gives the data (`payment_status`,
`payment_status_label`); the CSS class picked from it stays in blade.

**Routes of another module.** `OrderViewModel` carries the order's own public
URL, but the Profile listing builds its `profile.orders.show` link from `id` —
a module's ViewModel does not embed another module's routes.

---

# Testing

Unit-test the prepared fields, not the passthrough ones: URLs built from
relations, formatted amounts and dates, derived flags, and the degraded path
when a relation was not loaded. `tests/Unit/**/ViewModels/` holds the examples.

---

[Back to main readme](../../README.md)
