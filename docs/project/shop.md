# Shop

Product catalog, cart with promo codes, checkout and the customer account. Reviews are
about the shop as a whole, not its products — [reviews.md](reviews.md).
Code — the `Shop`, `ShopCart`, `ShopOrder` modules (`app/Modules/`) and the admin resources
`app/Filament/Resources/Shop/`. How it works technically — `docs/starter/cart.md`,
`docs/api/shop.md`.

---

## 1. Catalog

**Categories** — a tree of arbitrary depth (nested set): name, slug, image, text,
SEO fields, sort order, "show" toggle, and the "header menu" / "footer menu" toggles (as on
pages): the header lists the marked categories first, then the pages; the footer has the
«Каталог» column (categories) next to «Покупцям» (pages).

In the header a marked category opens a dropdown of its visible direct children, so a
short menu can carry many categories: mark a parent («Жінкам») and its children appear
under it. A marked category nested inside another marked one shows only in that dropdown,
not twice. The header is one row: logo left, menu in the middle, profile and cart right.

Up to 1024px the menu moves into a drawer opened by the burger: a category with children
shows an arrow to the side, and tapping it slides in a panel with the list of its
children and «Усі товари» (the category itself) at the bottom. The current page is highlighted at both
levels.

**Brands** — name, slug, logo, text, SEO. Optional: enabled separately
(`shop_brands`).

**Product** — category, brand, SKU, prices by set, the "pack" toggle, parameters, main
photo + gallery, text, SEO, sort order, "show" toggle. Products have no reviews or rating,
and there is no old / crossed-out price or sale block any more.

**Sets** (`packs`, JSON `[{quantity, price}]`) — a product is sold in 1 to 3 sets, e.g.
«1 шт.» / «5 шт.» / «10 шт.», each with the price of the whole set; the admin form starts
with 1 / 5 / 10 and a row without a price is not sold. With more than one set the card and
the product page show a button per set, which swaps the price; the price is labelled
«за шт.» / «за 5 шт.», for a pack product «за уп. (5пар)» (`shop.unit_box` + `shop.unit_pack`). `price` mirrors the smallest set's price (kept by the model) — the
catalogue filter, sorting and the inline price column work on it. The **pack** toggle
(`is_pack`) only changes the unit word: «шт.» → «уп.».

In the cart each set is its own line: «7 × 3 шт.» and «2 × 5 шт.» of one product are two
lines, each with its own count (see `docs/starter/cart.md`).

**Parameters** (admin → Catalog → Parameters: `shop_attributes`, `shop_attribute_options`,
pivot `shop_attribute_option_product`) — e.g. size, season, material: a parameter has a
list of values, a product gets any number of values of each (a multiple choice per
parameter in the product form). They show in the product's specifications and, with
"in filter" on, in the catalogue sidebar. On a live database the migration
`2026_10_27_000006_fill_shop_attributes` creates size / season / material with their
values and gives 10 random visible products a sample of them; the rest is set in the admin.

### Pages

| URL | What it shows |
|---|---|
| `/{shop}` | shop home page |
| `/{shop}/{category}` | products of the category **including all its subcategories** |
| `/{shop}/{category}/{slug-id}` | product page: photos, description, specifications |

`{shop}` — the slug of the page with the "Shop" module in the admin panel; it is changed there too.

### SEO

Left empty in the admin (or with "generate SEO" on — the default when creating), the fields
are filled on save by `HasSeoFields`; texts typed by hand are never replaced:

| Field | Category | Product |
|---|---|---|
| slug | from the title, Ukrainian transliteration: `Чоловічі шкарпетки` → `cholovichi-shkarpetky` | the same |
| meta_title | `{title} — купити в інтернет-магазині Nosi` | `{title} — купити в Nosi` |
| meta_description | `Купити {title} в інтернет-магазині Nosi: оригінальні бренди, доставка Новою поштою…` | `Купити {title} від {brand} в інтернет-магазині Nosi. {start of the text} Доставка Новою поштою…` (≤ 160 chars) |

The wording lives in `Category::seoMetaTitle()` / `seoMetaDescription()` and the same pair in
`Product`. The storefront applies the same rule to a record whose fields are still empty.
The migration `2026_10_29_000001_regenerate_shop_seo_fields` rebuilt the placeholder texts of
records saved before the rule. The slugs of existing records were not changed: their
addresses are already indexed.

Images: the product card, the category tile and the product page thumbs show the `card`
preset, the product page photo, the slider and the promo banners the `large` one
(`config/image_sizes.php`) — never the uploaded original. Products of a hidden category are
left out of the sitemap: they open with 404.

### Filters, sorting, pagination

- **Brand** — list of brands with the product count next to each.
- **Parameters** — a checkbox list per parameter, under its code in the URL
  (`?season[]=3&size[]=7`): values of one parameter are alternatives, different
  parameters narrow each other down; counts as for brands.
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

**Checkout** — `/{cart}/checkout`: email (required), phone (required, Ukrainian — stored as +380XXXXXXXXX, see `App\Modules\Core\Rules\UkrainianPhone`), first name, last name,
comment. No registration required.

What happens at checkout — in a single transaction:

1. cart recalculation (including the promo code check);
2. order creation with a copy of the cart lines, totals and promo code;
3. cart deletion.

After the commit the customer gets the `order_created` letter, and the managers get a
**Telegram message** (number, customer, phone, email, comment, lines, discount, total) from
the bot `TELEGRAM_BOT_TOKEN` — `ShopOrder\Actions\SendNewOrderTelegramAction`, one queued
`Notification\Jobs\SendTelegramMessageJob` per chat (3 tries). Only orders placed on the site
trigger it, not ones created in the admin panel. Recipients — chat IDs separated by commas:
the dictionary group `telegram` of the main language (`APP_LOCALE`; admin → Languages, the
first section), and if it is empty — `TELEGRAM_CHAT_ID` in `.env`; none set — nothing is sent.
Every message is written to `log_telegram` first (admin → Logs → Telegram: queued / sent /
failed with Telegram's error), the same way letters go to `log_letters`.

A person must press Start in the bot before it may write to them. The bot answers any
message with the sender's chat ID (`Notification\Http\Controllers\Api\TelegramWebhookController`,
`POST /api/telegram/webhook`, checked by the `X-Telegram-Bot-Api-Secret-Token` header derived
from the bot token). The webhook is registered once with `php artisan telegram:webhook`
(admin → Debug Tools on the FTP-only host; `--delete` removes it). While it is set,
`getUpdates` no longer works. The texts are in `lang/{locale}/site.php` → `telegram_order`,
`telegram_bot`.

An order gets a UUID for links and a sequential **number** for humans. The promo code and
its code string are kept in the order even if the promo code is later deleted.

**Order pages**: `/orders/{uuid}`, `/orders/{uuid}/success`,
`/orders/{uuid}/error` — reserved for future online payment.

**Payment** — only fields so far: status (unpaid / processing / paid), type
(none / online / manual), payment date. There is no payment integration.
While an order is unpaid, its page shows the bank details for a transfer (recipient, IBAN,
tax ID, payment purpose), each with a copy button. Both the values and their labels are in
the dictionary, group `requisites` (admin → Languages → «Реквізити для оплати»); an empty
value hides its row, all empty — the whole block.

**Account** — a logged-in customer sees their orders in the profile
(`/profile/orders`).

---

## 4. Admin panel

| Section | What you can do |
|---|---|
| Categories | tree (parent category, order), CRUD |
| Brands | CRUD |
| Products | CRUD, photos, gallery, prices by set, parameter values |
| Parameters | CRUD, values (ordered), "in filter" and "show" toggles |
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

- Variants with their own photos / SKU (size, color as a choice in the cart) —
  [`docs/todo/product_items.md`](../todo/product_items.md); for now the sets cover
  «how many», the parameters only describe the product.
- Typed parameters (numbers with a range filter, per-category parameter sets) —
  [`docs/todo/product_attributes.md`](../todo/product_attributes.md); the simpler
  multiple-choice version above is what is built.
- Online payment, delivery, stock / inventory, order fulfillment statuses,
  order emails to the customer.
