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
    └── pages/         # page-level templates per module
```

## Blade conventions

- Use Blade components (`<x-component-name />`) for reusable UI
- Page templates live in `resources/views/pages/` grouped by module
- Layouts in `resources/views/layouts/` — extend with `@extends('layouts.app')`

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
