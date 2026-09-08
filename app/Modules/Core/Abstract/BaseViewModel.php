<?php

namespace App\Modules\Core\Abstract;

use ReflectionClass;
use ReflectionProperty;
use Spatie\LaravelData\Data;

/**
 * Base class for the view layer: the last step before blade.
 *
 * A ViewModel is fed an Eloquent model (or the few models one screen needs) and
 * exposes fields that are already prepared for rendering. Blade asks the
 * ViewModel for a field — it does not walk relations and does not format:
 *
 *     {{ $product->url }}            not  {{ save_route('shop.product', [$product->category->slug, ...]) }}
 *     {{ $order->total_formatted }}  not  {{ number_format($order->total_amount, 2) }}
 *
 * Money is formatted through `Core\Support\Money` — `Money::from($order->total_amount)
 * ->format()` — so the currency symbol is written in one place and never on a model.
 *
 * Two levels, both living in `app/Modules/{Module}/ViewModels/`:
 *
 * 1. `{Model}ViewModel` — one entity, reusable across screens (a product on the
 *    home page, in a category listing and in the cart is the same ViewModel);
 * 2. `{Page}ViewModel` — one screen, composing the entity ViewModels with
 *    whatever else that page shows (breadcrumbs, filter, active section).
 *
 * Construction goes through spatie's magical creation: declare
 * `public static function fromProduct(Product $product): self` and callers write
 * `ProductViewModel::from($product)`. Do not override `from()` itself — its
 * signature belongs to `Data`.
 *
 * Collections and paginators go through `collect()`, which keeps a paginator a
 * paginator, so `links()` still works in blade:
 *
 *     ProductViewModel::collect($products)
 *
 * What a ViewModel must not do:
 *
 * - query the database — it receives loaded models, so eager-loading is the
 *   caller's job (a ViewModel touching a lazy relation is an N+1 waiting in a loop);
 * - reach into a module that can be switched off — check `ModuleGate` in the
 *   caller and pass the result in;
 * - carry anything the page does not render (password hashes, tokens, internal flags).
 *
 * `toArray()` comes from `Data` and is the contract tests assert against; the
 * view itself receives the object.
 */
abstract class BaseViewModel extends Data
{
    /**
     * The view variables of a page ViewModel: every public property, under its
     * own name, for `render()` or `view()->share()`.
     *
     * Unlike `toArray()` this keeps nested ViewModels as objects — blade is
     * given the object, not a flattened array.
     *
     * @return array<string, mixed>
     */
    public function toViewData(): array
    {
        $data = [];

        foreach ((new ReflectionClass($this))->getProperties(ReflectionProperty::IS_PUBLIC) as $property) {
            if (!$property->isStatic()) {
                $data[$property->getName()] = $property->getValue($this);
            }
        }

        return $data;
    }
}
