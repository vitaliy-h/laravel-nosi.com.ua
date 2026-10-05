# Inline editing in admin tables

A starter task (`app/Filament/`): works in the table of any resource.
Status as of 2026-10-05 — implemented; how it works and where it is used: [Inline Editing](../filament/inline-edit.md).

## What we want

In a resource table, a cell shows plain text. **Double-clicking** an editable cell
replaces the text with a field:

- text, number, date — an `input` (of the matching type);
- choosing one of several options (enum, `belongsTo`) — a `select`;
- choosing several (`belongsToMany`, an array in a json column) — a multiselect.

Save on `Enter` or blur (for a select — right after choosing); cancel on `Esc`: the
cell goes back to text without a request. After saving, the cell shows text with the
new value again (in the same format as before editing — badge, date, option label).
Show a validation error next to the field and keep the field open.

A cell that can't be edited does not react to a double-click. Editable cells get a
subtle hint (cursor / dashed underline on hover) so they can be told apart.

## Why the existing columns don't fit

Filament already has `TextInputColumn`, `SelectColumn`, `ToggleColumn` (we use them in
`LanguageResource`, `PageResource`: `rank`, `display`). They show the field
**all the time**: the table turns into a form, the text is hard to read, and formatting
(`badge()`, `formatStateUsing()`, `dateTime()`) is lost. None of them is a
multiselect. `ToggleColumn` stays as is — boolean fields don't need a double-click.


## Where to use it first

Enable it for editing `rank` in all sections, and for product prices in the
products tables of categories and brands.
In all sections — for the name, title and URL, if they are shown in the table.

## What was done (2026-10-05)

Implemented as `App\Filament\Tables\Columns\InlineEditColumn`; full description —
[Inline Editing](../filament/inline-edit.md).

- **The column.** A `TextColumn` that implements Filament's `Editable` and saves through the
  standard `updateTableColumnState()`, so hidden/disabled columns and `rules()` are checked on
  the server. Modes: text (default), `editAsNumber()`, `editAsDate()`, `editAsDateTime()`,
  `options()` (select), `options()->multiple()` (checkbox list). `editAttribute()` edits a
  different attribute than the one shown (`category.title` → `category_id`); a `belongsToMany`
  relationship is saved with `sync()`.
- **UX as specified.** Double-click opens the field with the raw value; `Enter` / blur saves,
  a select saves on choice, the checkbox list on `Enter` / click outside; `Esc` cancels without a
  request. The error is shown under the field, the field stays open. After saving the cell shows
  the value in its usual format (badge, date, label). Hover hint: light background + dashed
  underline; an empty editable cell shows `—`.
- **Beyond the spec.**
  - Permissions: the cell is disabled when the resource's `canEdit($record)` is false (the same
    gate as the edit modal) — Filament's own editable columns don't check policies.
  - An editable cell does not run the row action: otherwise the first click of a double-click
    opened the edit modal.
  - Form logic is not run: changing a title does **not** regenerate slug/meta (like the modal
    with `generate_seo` off). Model events and observers still fire.
- **Front end.** One Alpine component (`resources/js/filament/inline-edit-column.js`) and a small
  CSS file, registered with `FilamentAsset` in `AdminPanelProvider::boot()` — no Vite build.
  Published copies in `public/js/app`, `public/css/app` are git-ignored.
- **Rollout.**
  - `rank`: pages, languages, countries, regions, cities, brands, products (replaces the old
    always-on `TextInputColumn`). Categories: no `rank` column — the tree order is the nested set.
  - Prices: `price`, `price2` in the products table (it already filters by category and brand —
    no separate products tables inside categories/brands were added).
  - `title` / `name` / `slug` wherever shown: pages, news, categories, brands, products, geo,
    languages, users, feedback. `slug` via `HasSeoFields::getSlugColumn()` (unique per table).
  - Selects: product category and brand. Date + time: news `published_at`.
    Multiselect: user roles (`super_admin` offered to Super Admins only; ids outside the list
    are rejected).
- **Shared code.** Product category options moved into `ProductResource::getCategoryOptions()`
  (form, filter, column). The `make-filament-resource` skill now generates `InlineEditColumn` for
  `rank`, `title` / `name` and `slug`.
- **Tests.** `tests/Feature/Filament/InlineEdit/` (generic behaviour; a fixture table for the json
  multiselect and the date mode), plus inline cases in `ProductResourceTest` and
  `UserResourceTest`.
- ⚠️ Editing `slug` or a product's category changes a public URL immediately.
