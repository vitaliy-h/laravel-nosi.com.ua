# ABC-response — the server-driven AJAX protocol

The frontend has no SPA framework. Interactive pieces — cart changes, promocodes, profile
forms, the subscribe box — post to a normal Laravel route and get back a **list of DOM
actions**, which `public/assets/js/abc-response.js` applies. The server renders HTML; the
browser only patches it in.

---

## Marking up the trigger

```blade
{{-- form --}}
<form data-abcr="{{ route('change_product_count') }}" method="POST"> ... </form>

{{-- button or link --}}
<button data-abcr="/favorite/add" data-product-id="15" data-method="POST">…</button>
```

| Attribute | Meaning |
|---|---|
| `data-abcr` | the endpoint to call — this is what turns the element into an AJAX trigger |
| `data-method` | HTTP method for buttons/links (default `GET`); forms use their own `method` |
| any other `data-*` | sent along as query parameters |
| `class="validate"` | opt-in jQuery validation before the request |

While a request is in flight the script adds `.abcr-wait` to the trigger and
`.abcr-disabled` to submit buttons — style them, do not reimplement them.

Keep `action="…"` on forms as well as `data-abcr`: with JS disabled the form still submits
normally, which is why the controllers answer both JSON and a redirect (see
`PromocodeController` in `docs/starter/cart.md`).

---

## The response

```json
{
  "actions": [
    { "method": "html",  "selector": "#mini-cart",  "content": "<div>…</div>" },
    { "method": "text",  "selector": "#cart-count", "content": "5" },
    { "method": "alert", "content": "Added to cart" }
  ]
}
```

Each entry is `{method, selector, content}` — `selector` is passed to
`querySelectorAll`, so an action can update several nodes at once.

Available methods:

| Group | Methods |
|---|---|
| Content | `html`, `text`, `append`, `prepend`, `replaceWith`, `remove`, `clearForm` |
| Visibility | `show`, `hide` |
| Attributes | `addClass`, `removeClass`, `prop`, `removeAttr` |
| Navigation | `location`, `reload`, `scroll`, `scrollTop` |
| Feedback | `alert`, `error`, `modal`, `script` |

Building the response from a controller:

```php
return response()->json([
    'actions' => [
        [
            'method'   => 'html',
            'selector' => '#mini-cart',
            'content'  => view('modules.shop_cart.partials.cart_modal_item', [...])->render(),
        ],
        [
            'method'   => 'text',
            'selector' => '.js_cart_count',
            'content'  => $count,
        ],
    ],
]);
```

The pieces of HTML sent back are ordinary blade partials rendered server-side — that is the
whole point: one template renders both the full page and the patch.

---

## Who uses it

`ShopCart` (`AjaxCartController::change`, `PromocodeController`), `Profile`
(profile and password forms), `Subscriber`. `CartAction` cases exist precisely because each
trigger updates a different set of selectors — see `docs/starter/cart.md`.

---

## The `_debug` playground

`app/Modules/_debug/` is a single controller that exercises the protocol:
`GET /_debug/abc-response` renders a page with one button per action type, and
`GET|POST /_debug/abc-response/{action}` answers with that action. Use it to check the
runner after changing `abc-response.js`, or as a live reference for what each method does.

Both routes sit behind `auth` + `RoleMiddleware::using('super_admin')`, so the page is
unreachable for everyone else. The module has no toggle in `config/modules.php`; delete the
folder if a project wants it gone (`docs/starter/modules.md`).

---

[Back to main readme](../../README.md)
