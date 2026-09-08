# TODO

Ideas and unfinished work for the starter itself. Every line here was checked against the
code on 2026-09-05 — an item that turned out to be finished is kept in the "Done" list
with a pointer, so it does not get re-planned.

---

## Planned

* **Telegram bot endpoints** and the libraries behind them. (Telegram exists today only as
  a Socialite login provider — `docs/starter/oauth.md`.)
* **Price list upload/update from an Excel file.**
* **Image rotation** in the admin image field.
* **URL redirects** (managed old → new paths).
* **Canonical URLs.**
* **Structured data (schema.org) markup.**
* **`shop_order_products.title`** — the order line currently snapshots `product_name` and
  `product_article`; a separate display title is still open.
* **Language as the first URL slug**, to support multilingual sites. Touches
  `App\Modules\Core\Support\ModuleManifest` (prefix resolution) and each
  module's `Http/Routes/web.php`. The dictionary layer itself already exists —
  `docs/starter/translations.md`.

## Specified, not implemented

Both have a full written plan; the code is untouched.

* **Product variants (`shop_product_items`)** — `docs/project/product_items.md`
* **Dynamic product attributes** (manager-defined characteristics + catalog filters) —
  `docs/project/product_attributes.md`

## Done

* ~~Run migrations and other console commands from the web interface~~ —
  `app/Filament/Pages/DebugTools/DebugTools.php` + `Support/ArtisanCommandRunner`
  (whitelisted commands, destructive ones behind a second confirmation).
* ~~User roles in the admin panel~~ — `UserResource` has a `roles` select and a role
  filter, seeded by `RolesAndPermissionsSeeder`.
* ~~User filter~~ — `Filament\Filters\UserSelectFilter`, used by the cart and order
  resources; `Filament\Forms\UserSelect` is its form counterpart.
* ~~`shop_products.rank`~~ — the column exists and drives the default table sort.
* ~~Cron jobs~~ — `bootstrap/app.php` → `withSchedule()`; `cart:clear-abandoned` runs daily.
