# Filament Admin Panel

This project uses **Filament v5** as the admin panel solution.

The admin panel is built with a **minimal, consistent, table-first approach** and follows strict UI/UX conventions across all modules.

---

# Key Principles

* Table-first CRUD interface
* Modal-based create/edit (no separate pages)
* No breadcrumbs
* No H1 page titles
* Consistent actions (Edit / Delete only)
* Laravel-native translations

---

# Shared building blocks

Everything under `app/Filament/` that is **not** a Resource, Page or Widget exists so
resources stay short and look the same. Reach for these before writing a new form field,
table action or filter.

## Actions (`app/Filament/Actions/`)

| Class | What it gives you |
|---|---|
| `BaseEditAction::make()` | The project's edit modal: icon button, two submit buttons (*Save* and *Save & close*) and a *View logs* link, instead of Filament's single Submit |
| `ViewLogsAction::make()` | Opens `LogResource` pre-filtered to this record (`model_type` + `model_id`) in a new tab |
| `ClearLogAction::make(Model::class)` | Header action for journal pages — wipes the whole journal in one query, behind a confirmation modal |

## Traits (`app/Filament/Traits/`)

| Trait | What it gives you |
|---|---|
| `HasDefaultTable` | `applyDefaultTable()` — the standard row actions (`BaseEditAction` + `DeleteAction` before the columns), the `CreateAction` toolbar and the delete bulk action |
| `HasSeoFields` | `getSeoSection()` — the shared SEO block (`generate_seo` toggle, `slug`, `meta_title`, `meta_description`, optionally `is_indexable`); `getSlugColumn()` — the matching inline-editable `slug` table column |
| `CanGenerateGetTitleMethod` | Used by the generators below, not by resources: adds a `getTitle()` stub to a generated page class |

## Form and table helpers

| Class | What it gives you |
|---|---|
| `Tables\Columns\InlineEditColumn::make()` | A `TextColumn` that becomes an input / select / checkbox list on double-click — the default for editable table cells instead of `TextInputColumn` / `SelectColumn`. See [Inline Editing](inline-edit.md) |
| `Forms\UserSelect::make()` | A user `Select` searchable by name, surname, email and phone, labelled "Surname Name" |
| `Filters\UserSelectFilter::make()` | The same thing as a table filter — use the pair so a form and its table behave identically |
| `Support\NestedSetFields::make(Model::class)` | The `parent_id` + `_position_id` pair for any model using `NodeTrait` — see [Nested Sets](nested-sets.md) |
| `Support\ArtisanCommandRunner` | The whitelist of artisan commands the Super Admin can run from the admin panel, and the runner behind the DebugTools page. Destructive entries need a second confirmation and are refused in production — [Debug Tools](debug-tools.md) |

## Content builder (`app/Filament/Builder/`)

`Builder/Blocks/` holds one class per CMS content block — `TextBlock`, `ImageBlock`,
`GalleryBlock`, `VideoBlock`, `TeasersBlock` — each returning a Filament `Block`. Resources
that own editable content (`PageResource`, `NewsResource`) list the blocks they allow.

`BuilderContent` gives the upload fields inside those blocks a stable storage directory
(`{table}/{id}/{field}`) and keeps a `.block-map.json` alongside the files, so an image
stays attached to its block when blocks are reordered.

⚠️ `TeasersBlock` renders product teasers, which is why an editor can drop shop content
into any page — and why `docs/starter/modules.md` warns that disabling `shop` breaks
pages that still contain such blocks.

## Generators (`app/Filament/Generators/`)

`AdminResourceClassGenerator` and `AdminResourceManageRecordsPageClassGenerator` are bound
over Filament's own generators in `app/Providers/FilamentServiceProvider.php:22-23`, so
`php artisan make:filament-resource` emits this project's conventions (icon-button row
actions, `BaseEditAction`, the `--simple` page shape) instead of the vendor defaults. See
[Resource Generation](resource-generation.md).

---

# Documentation

Detailed documentation is split into separate files:

* [Resource Generation Guide](resource-generation.md)
* [Admin Dashboard](dashboard.md)
* [Translations Guide](translations.md)
* [Nested Sets (Tree Structures)](nested-sets.md)
* [Debug Tools (artisan from the panel)](debug-tools.md)
* [Inline Editing in Tables](inline-edit.md)

---

[Back to main readme](../../README.md)
