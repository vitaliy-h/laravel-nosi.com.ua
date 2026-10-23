# Shop

Product catalog, cart with promo codes, checkout and the customer account. Reviews are
about the shop as a whole, not its products — [reviews.md](reviews.md).
Code — the `Shop`, `ShopCart`, `ShopOrder` modules (`app/Modules/`) and the admin resources
`app/Filament/Resources/Shop/`. How it works technically — `docs/starter/cart.md`,
`docs/api/shop.md`.

---

## 1. Catalog

**Categories** — a tree of arbitrary depth (nested set): name, slug, image, text,
SEO fields, sort order, "show" toggle.

**Brands** — name, slug, logo, text, SEO. Optional: enabled separately
(`shop_brands`).

**Product** — category, brand, SKU, price (`price`) and a second price (`price2`, old /
struck-through), main photo + gallery, text, SEO, sort order, "show" toggle.
Products have no reviews or rating.

### Pages

| URL | What it shows |
|---|---|
| `/{shop}` | shop home page |
| `/{shop}/{category}` | products of the category **including all its subcategories** |
| `/{shop}/{category}/{slug-id}` | product page: photos, description, specifications |

`{shop}` — the slug of the page with the "Shop" module in the admin panel; it is changed there too.

### Filters, sorting, pagination

- **Brand** — list of brands with the product count next to each.
- **Price** — a "from / to" range; the bounds come from the actual product prices in the selection.
- **Search** — by product name.
- Each filter's counts and bounds are calculated taking the **other** selected filters into
  account, but not itself — so a selection doesn't "lock" its own options.
- Sorting: newest, price ↑/↓, name A–Z / Z–A.
- Per page: 12 / 24 / 48 / 96 products.

### API

`GET /api/shop/categories`, `/categories/{id}`, `/products`, `/products/{id}` — the same
data for a mobile client or frontend. Described in `docs/api/shop.md`.

---

## 2. Cart

- Add a product, change quantity, remove — without reloading the page;
  the counter in the header updates.
- A guest gets a cart bound to the session. After logging in, the guest cart is
  **merged** into the user's cart (identical products are not duplicated).
- A cart line remembers the name, SKU and price at the moment it was added.
- The cart has its own link `/{cart}/{uuid}`.
- Guest carts that nobody claimed are deleted after 30 days
  (daily `cart:clear-abandoned` command).

### Promo codes

- Type: **fixed amount** or **percentage** (for percentage — a discount cap `max_discount`).
- Restrictions: minimum order amount, start and end dates, usage limit,
  "active" toggle, a note for the manager.
- The code is case-insensitive.
- A promo code is applied in the cart and **checked again at checkout** — if it has
  expired or reached its limit in the meantime, the discount is removed.
- Enabled separately (`shop_promocodes`).

---

## 3. Orders

**Checkout** — `/{cart}/checkout`: email (required), first name, last name, phone,
comment. No registration required.

What happens at checkout — in a single transaction:

1. cart recalculation (including the promo code check);
2. order creation with a copy of the cart lines, totals and promo code;
3. cart deletion.

An order gets a UUID for links and a sequential **number** for humans. The promo code and
its code string are kept in the order even if the promo code is later deleted.

**Order pages**: `/orders/{uuid}`, `/orders/{uuid}/success`,
`/orders/{uuid}/error` — reserved for future online payment.

**Payment** — only fields so far: status (unpaid / processing / paid), type
(none / online / manual), payment date. There is no payment integration.

**Account** — a logged-in customer sees their orders in the profile
(`/profile/orders`).

---

## 4. Admin panel

| Section | What you can do |
|---|---|
| Categories | tree (parent category, order), CRUD |
| Brands | CRUD |
| Products | CRUD, photos, gallery |
| Promo codes | CRUD, usage counter |
| Carts | CRUD, filter by user |
| Orders | CRUD, filters by payment status, payment type and user; changing the discount or promo code recalculates the total |

**Dashboard**: catalog, order, cart and promo code statistics, sales chart,
latest orders, top products.

---

## 5. Enabling / disabling

`config/modules.php`, the `list` section:

| Key | What it disables | Along with it |
|---|---|---|
| `shop` | the whole shop | cart, brands, orders, promo codes |
| `shop_cart` | cart | orders, promo codes |
| `shop_brands` | brands in the admin panel and filter | — |
| `shop_promocodes` | promo codes | — |

Before disabling `shop`, remove product blocks from page content.

---

## 6. Not implemented yet

- Product variants (size, color) — [`docs/todo/product_items.md`](../todo/product_items.md).
- Product attributes and filtering by them — [`docs/todo/product_attributes.md`](../todo/product_attributes.md).
- Online payment, delivery, stock / inventory, order fulfillment statuses,
  order emails to the customer.
