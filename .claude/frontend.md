# Frontend Developer Guide

## Stack

- **Bundler:** Vite 8
- **CSS:** Tailwind CSS 4 (via `@tailwindcss/vite` plugin)
- **Templating:** Laravel Blade
- **Integration:** `laravel-vite-plugin`

## Commands

```bash
npm run dev    # dev server with HMR
npm run build  # production build
```

## Directory structure

```
resources/
├── css/           # global styles, Tailwind entry point
├── js/            # JS modules
└── views/
    ├── blocks/        # reusable Blade content blocks
    ├── components/    # Blade components
    ├── emails/        # email templates
    ├── errors/        # HTTP error pages
    ├── filament/      # Filament admin overrides
    ├── layouts/       # base layouts
    ├── pages/         # page-level templates per module
    └── sitemap/       # XML templates for /sitemap*.xml
```

## Blade conventions

- Use Blade components (`<x-component-name />`) for reusable UI
- Page templates live in `resources/views/pages/` grouped by module
- Layouts in `resources/views/layouts/` — extend with `@extends('layouts.app')`

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
