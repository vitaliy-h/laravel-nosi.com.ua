# Frontend Developer Guide

## Stack

- **Frontend CSS:** the Nosi design (`design/home.html`) — no CSS framework.
  `resources/views/layouts/app.blade.php` (the one layout of every page) loads
  `public/assets/css/nosi.css` (tokens, header/footer, home page blocks, `.btn`,
  `.card`, `.tabs`) and `public/assets/css/nosi-pages.css` (inner pages: `.page-head`,
  forms `.field`/`.input`, catalogue, product, cart, account, CMS blocks), plus
  bootstrap-icons (`<i class="bi bi-*">`). JS: `public/assets/js/nosi.js` and
  `abc-response.js`. The old Bootstrap demo theme in `public/demo/` is not linked anywhere.
- **Templating:** Laravel Blade
- **Bundler:** Vite 8 + Tailwind CSS 4 (`@tailwindcss/vite`, `resources/css/app.css`) —
  **configured but not used by the frontend**: no view calls `@vite`, so nothing
  built by Vite reaches a page today

⚠️ Write markup with the classes of `nosi.css` / `nosi-pages.css`, not Bootstrap or
Tailwind utilities (`row`, `col-*`, `btn-primary`, `d-flex` do nothing). Pagination
has its own view: `Paginator::defaultView('partials.pagination')` in `AppServiceProvider`.
The running line and the footer catalogue come from `Page\Services\SiteChromeComposer`
(a view composer on `partials.announce` / `partials.site_footer`), not from controllers.

Admin panel exception: `resources/js/filament/` and `resources/css/filament/` are plain files
for Filament (Alpine components, panel CSS), **not** Vite entries. They are registered with
`FilamentAsset` in `AdminPanelProvider::boot()` and copied to `public/` by
`php artisan filament:assets` — see `docs/filament/inline-edit.md`.

## Commands

```bash
npm run dev    # dev server with HMR
npm run build  # production build
```

## Directory structure

```
resources/
├── css/           # Tailwind entry point (Vite build, currently unused by views)
├── js/            # JS modules
└── views/
    ├── layouts/       # base layouts (@extends)
    ├── components/    # anonymous <x-...> components ONLY
    ├── partials/      # global @include templates
    ├── modules/       # per-module templates, folder = PageModule value
    ├── emails/        # email templates
    ├── errors/        # HTTP error pages
    └── filament/      # Filament admin overrides
```

## Naming rules

1. **Folders and files are `snake_case`.** The single exception is
   `resources/views/components/` — `kebab-case` there, because that is the
   Blade standard for `<x-...>` tags.
2. **A module folder is named after its `PageModule` enum value**
   (`shop_cart`, `user_auth`, `page_text`), not after the `app/Modules`
   folder. Modules outside the enum use `snake_case` of the module name
   (`ShopOrder` → `shop_order`, `Sitemap` → `sitemap`).
3. **A module folder has exactly three places:**
   - its root — page templates, i.e. what a controller returns
     (`index`, `show`, `edit`, `product`, `category`, `checkout`, …);
   - `partials/` — everything pulled in with `@include`;
   - `layouts/` — the module's own layout (only `profile` has one).

   No `components/` or `blocks/` inside a module.

   **One subfolder inside `partials/` is allowed, and only for a set of templates
   picked dynamically by a key** — the folder is then the key's namespace and is named
   after it. Real case: `modules/user_auth/partials/forms/{login,register,
   password_recovery,password_reset}.blade.php`, chosen by
   `'modules.user_auth.partials.forms.' . $mode` (`AuthPageViewModel.php:36`). A
   template `@include`d by a literal name goes flat into `partials/` — do not create a
   folder just to group files.
4. **A module's main page is always `index.blade.php`.**
5. **A module's template lives in that module**, even when another module
   includes it — `modules.shop.partials.product_card` is included from
   `modules/index/index.blade.php`, and stays in `shop`.

## Global `resources/views/partials/`

Templates used by more than one module, or by the layout itself: `head`, `navbar`,
`footer`, `scripts`, `alerts`, `modals`, `hero`, `page_title`, `page_content`.

The same subfolder rule applies here: `partials/builder/` holds one template per CMS
content block (`text`, `image`, `gallery`, `video`, `teasers`), resolved by
`partials/builder.blade.php` as `'partials.builder.' . $block['type']`.

⚠️ A template only one module uses belongs to that module, not here — pagination for the
profile page lives in `modules/profile/partials/pagination.blade.php` (rule 5), even
though `links()` can reference any view path.

## `components/` vs `partials/`

The difference is *how the template is pulled in*, and Laravel enforces it:
everything in `resources/views/components/` is auto-registered as an anonymous
component, so a file there is reachable as `<x-file-name />`.

| | `components/` | `partials/` |
|---|---|---|
| Usage | `<x-rating-stars :value="…" />` | `@include('partials.page_title')` |
| Inputs | `@props` — explicit contract | inherits the parent's variables |
| Parent scope | isolated, sees nothing | sees everything |
| `$slot` / `$attributes` | yes | no |
| Naming | `kebab-case` | `snake_case` |

**The test:** does it declare `@props` and get called as a tag? → `components/`.
Does it read variables from the surrounding scope and get `@include`d? →
`partials/` (global) or `modules/{module}/partials/` (module-local).

Never put an `@include`-only template in `components/` — Laravel will still
register it as `<x-...>`, and that tag would render broken, because a component
does not inherit the parent's variables.

New reusable UI should be written as a component (`@props` + `<x-...>`), not as
a partial. Partials are for templates that legitimately read page context
(`page_title`, `alerts`, `page_content`).

## Blade conventions

- Layouts in `resources/views/layouts/` — extend with `@extends('layouts.app')`
- Module pages are returned as `modules.{module}.{page}` — e.g.
  `$this->render('modules.shop.product', $page->toViewData())`

## Blade gets ViewModels, not models

A template asks a ViewModel for a field. It does not walk relations, format
numbers or dates, or build URLs — that work happened before the view:

```blade
{{-- no --}}
<a href="{{ save_route('shop.product', [$product->category->slug, $product->slug_with_id]) }}">
{{ t('shop.currency') }}{{ number_format($order->total_amount, 2) }}

{{-- yes --}}
<a href="{{ $product->url }}">
{{ $order->total_amount_formatted }}
```

Missing a field is a signal to add it to the ViewModel, not to reach into the
model from the template. View variables are snake_case. Presentation choices
(which CSS class a status maps to) do stay in blade.

See `docs/starter/view-models.md`.

## Translations in Blade

Use `t()` helper (not `__()`) for public site strings:
```blade
{{ t('news.title') }}
{{ t('shop.add_to_cart') }}
```

`__()` / `@lang()` is for admin panel (Filament) strings only.

## Asset references

Always use Vite helpers — never hardcode paths:
```blade
@vite(['resources/css/app.css', 'resources/js/app.js'])
```
