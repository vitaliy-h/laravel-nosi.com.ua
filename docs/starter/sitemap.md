# Sitemap

## Overview

XML sitemap split into multiple files for performance and SEO tooling compatibility.

All sitemap routes are defined in `app/Modules/Core/Http/Routes/web.php` and handled by `App\Modules\Core\Http\Controllers\SitemapController`.

---

## URLs

| URL | Description |
|-----|-------------|
| `/sitemap.xml` | Sitemap index — links to all sub-sitemaps |
| `/sitemap-base.xml` | Static CMS pages |
| `/sitemap-news-{page}.xml` | News items, paginated |
| `/sitemap-categories-{page}.xml` | Shop categories, paginated |
| `/sitemap-products-{page}.xml` | Shop products, paginated |

Page numbering starts at `1`. Pages with no records still return valid empty XML.

---

## Sub-sitemap Details

### Base (`/sitemap-base.xml`)

Includes all `pages` table rows where:
- `display = true`
- `is_indexable = true`

URL generated via `url(UrlFacade::normalizeForLink($page->slug))`. Sorted by `rank DESC`.

### News (`/sitemap-news-{page}.xml`)

Includes `News` records where `display = true`.

Sorted by `published_at DESC`. 500 items per page.

**Skipped entirely** if the `news` module page has `display = false` or `is_indexable = false`.

### Categories (`/sitemap-categories-{page}.xml`)

Includes `shop_categories` records where `display = true`.

Sorted by `_lft` (nested set tree order). 500 items per page.

URL format: `/shop/{category-slug}`

**Skipped entirely** if the `shop` module page has `display = false` or `is_indexable = false`.

### Products (`/sitemap-products-{page}.xml`)

Includes `shop_products` records where `display = true`.

Sorted by `id`. 500 items per page. Eager loads `category` relation.

URL format: `/shop/{category-slug}/{product-slug}-{product-id}`

Products without a category are excluded from output.

**Skipped entirely** if the `shop` module page has `display = false` or `is_indexable = false`.

---

## Module Indexability Check

Before generating news/categories/products, the controller checks whether the corresponding `Page` record allows indexing:

```php
Page::where('module', $module)
    ->where('display', true)
    ->where('is_indexable', true)
    ->exists();
```

- `PageModule::NEWS` gates the news sitemap
- `PageModule::SHOP` gates both categories and products sitemaps

If the check fails, the sitemap index (`/sitemap.xml`) omits those sub-sitemaps entirely, and direct requests to those URLs return empty XML.

---

## Files

| Type | Path |
|------|------|
| Controller | `app/Modules/Core/Http/Controllers/SitemapController.php` |
| Routes | `app/Modules/Core/Http/Routes/web.php` |
| Views | `resources/views/modules/sitemap/` |

Views: `index.blade.php`, `base.blade.php`, `news.blade.php`, `categories.blade.php`, `products.blade.php`

---

## Pagination Size

Default: **500 items per file** (`SitemapController::PER_PAGE`).

Change the constant to adjust.

---

[Back to main readme](../../README.md)
