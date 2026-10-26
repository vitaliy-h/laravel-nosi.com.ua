# Action Return Values

Actions hold the business logic and are shared between web and API controllers.
They used to return whatever shape their author picked, so a caller could not
tell from the signature whether a failure came back as a value, as `null`, or as
an exception. This page is the rule.

---

# The Rule

An Action returns **exactly one of three things**:

**1. `void`** — it mutates state and has nothing to report.

```php
public function handle(Cart $cart): void
```

`RecalculateCartTotalAction`, `RemoveCartItemAction`, `UpdateUserDataAction`.

**2. A domain value** (`Order`, `User`, `float`, `array`, …) — it always
succeeds, and any failure is an exception.

```php
public function handle(OrderData $data): Order
```

`CreateOrderAction`, `RegisterAction`, `CalculateDiscountAction`.

**3. `ActionResult<T>`** — the failure is an **expected business outcome** the
caller must branch on: an invalid promocode, a quota exceeded, a repeated
subscription.

```php
/** @return ActionResult<PromocodeData> */
public function handle(string $code, float $subtotal): ActionResult
```

`ValidatePromocodeAction`, `ApplyPromocodeToCartAction`, `SubscribeAction`.

## Not `bool`

A bare `true` / `false` carries no reason for the failure. Every case that
reaches for one is better served by `ActionResult` (which has a message) or by
`void` (nothing to report). A genuine two-state question belongs in a **Query**
class, not an Action — see `docs/starter/queries.md`.

## Not an exception, for expected outcomes

An invalid promocode is user input, not an exceptional condition. Exceptions
stay for the genuinely exceptional — otherwise controllers turn into `try/catch`
and every message needs its own exception class.

---

# ActionResult

`App\Modules\Core\Dtos\ActionResult` is the one envelope, generic over its
payload:

```php
/** @template TData */
class ActionResult extends Data
{
    public function __construct(
        public readonly bool $success,
        public readonly ?string $message = null,
        public readonly mixed $data = null,
    ) {}

    public static function ok(mixed $data = null, ?string $message = null): self;
    public static function fail(string $message): self;
}
```

It serialises to `{ success, message, data }` — the same envelope the API layer
already uses (see `.claude/backend.md`).

## Payload DTOs

The envelope never carries payload fields. Each action that has a payload
defines its own small DTO **in its own module** and puts it in `data`:

```php
// app/Modules/ShopCart/Dtos/PromocodeData.php
class PromocodeData extends Data
{
    public function __construct(
        public readonly int $promocode_id,
        public readonly string $code,
        public readonly float $discount,
    ) {}
}
```

Note the fields are **non-nullable**. They only exist on the success branch —
that is the whole point of splitting the envelope from the payload. A DTO that
mixes both is forced to make every payload field nullable or defaulted so the
failure branch can be constructed, which leaves a failure carrying a
`discount` of `0.0` and a `code` of `null`.

## Using it

```php
// success with payload and a message
return ActionResult::ok(
    new PromocodeData($promocode->id, $promocode->code, $discount),
    t('shop.promocode_applied'),
);

// success with nothing to hand back
return ActionResult::ok(message: __('site.subscribe.saved'));

// expected failure
return ActionResult::fail(t('shop.promocode_invalid'));
```

At the call site:

```php
$result = $this->applyPromocode->handle($cart, $code);

if (! $result->success) {
    return back()->with('error', $result->message);
}

$cart->update(['promocode_id' => $result->data->promocode_id]);
```

## Success is not always "the happy path"

`SubscribeAction` returns `ok()` for both a new and an already-known email —
re-subscribing is idempotent, not an error. The difference lives in the payload
(`alreadyExists`) and in the message. Use `fail()` only when the caller must
treat the outcome as a failure.

---

# Static analysis

The `@template` / `@return` annotations are what narrows `$result->data` at the
call site. There is no static analyzer in `require-dev` yet, so today they are
**documentation only** — at runtime `data` is `mixed` and a typo in a payload
field name surfaces in tests, not in the editor. Adding `phpstan/larastan` would
make them enforced.

---

[Back to main readme](../../README.md)
