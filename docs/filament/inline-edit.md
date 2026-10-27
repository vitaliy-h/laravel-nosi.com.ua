# Inline editing in tables

`App\Filament\Tables\Columns\InlineEditColumn` — a table cell that shows plain text and turns
into a field on **double-click**. Use it instead of `TextInputColumn` / `SelectColumn`, which
show the field all the time and lose the column formatting. `ToggleColumn` stays as it is for
booleans.

## Behaviour

- The cell renders exactly like a `TextColumn`: `badge()`, `dateTime()`, `numeric()`,
  `formatStateUsing()` keep working. On hover an editable cell gets a light background and a
  dashed underline; an empty editable cell shows `—` so there is something to click.
- Double-click → field with the **raw** value (not the formatted one: a category shown as
  `— Child` is edited as `Child`).
- `Enter` or blur saves, a select saves right after choosing, `Esc` cancels without a request.
  The checkbox list (multiselect) saves on `Enter` or a click outside it.
- A validation error is shown under the field and the field stays open. After a successful
  save Livewire re-renders the table, so the cell shows the new value in its usual format.
- An editable cell does not run the row action — a click on it no longer opens the edit modal.
  Other cells of the row still do.

## Permissions

The cell is disabled — plain text, no double-click, row click works — when the resource's
`canEdit($record)` is false, i.e. the same gate as the edit modal (policies, `Gate::before` for
the Super Admin). Outside a resource (a table on a custom page or widget) there is no gate.
Passing your own `->disabled(...)` replaces this check.

## What is *not* run

Inline saving sets one attribute and calls `save()`: model events and observers fire
(`PageObserver` drops the route cache, `HasSeoFields` runs on `saving`), but the form does not —
no `mutateFormDataBeforeSave`, no `BaseEditAction` hooks. The `generate_seo` toggle is not
sent, so changing a title **does not** regenerate the slug or meta — the same as saving the edit
modal with the toggle left off. Repeat the form's validation in `rules()`.

⚠️ Editing `slug`, or a product's category (its slug is part of the product URL), changes a
public URL immediately — old links break, as they would after the same change in the modal.

## API

```php
use App\Filament\Tables\Columns\InlineEditColumn;

// text (default)
InlineEditColumn::make('title')
    ->rules(['required', 'max:255'])
    ->searchable(),

// number / date / date + time (datetime-local, minutes, app timezone)
InlineEditColumn::make('rank')->editAsNumber()->rules(['required', 'integer'])->sortable()->alignEnd(),
InlineEditColumn::make('published_at')->editAsDateTime()->rules(['date'])->dateTime(),
InlineEditColumn::make('starts_on')->editAsDate()->date(),

// select on a belongsTo: show the related title, edit the foreign key
InlineEditColumn::make('category.title')
    ->editAttribute('category_id')
    ->options(fn (): array => Category::pluck('title', 'id')->all())
    ->rules(['required', Rule::exists(Category::class, 'id')]),

// select on an enum — display stays a badge
InlineEditColumn::make('status')
    ->options(Status::options())
    ->rules([Rule::enum(Status::class)])
    ->badge()
    ->formatStateUsing(fn ($state) => $state?->label()),

// multiselect on a belongsToMany / morphToMany — saved with sync()
InlineEditColumn::make('roles.name')
    ->editAttribute('roles')
    ->multiple()
    ->options(fn (): array => Role::pluck('name', 'id')->all())
    ->badge(),

// multiselect on a json array column
InlineEditColumn::make('tags')->multiple()->options([...]),
```

| Method | What it does |
|---|---|
| `editAsNumber()`, `editAsDate()`, `editAsDateTime()` | Field type; text by default. Not `number()` / `date()`: `TextColumn::date()` is the display formatter |
| `options(array\|Closure)` | Edit with a select of these options (key → label) |
| `multiple()` | With `options()`: a checkbox list, the state is an array |
| `editAttribute(string)` | The attribute to edit when the column shows something else (`category.title` → `category_id`). A `BelongsToMany` relationship name is saved with `sync()` |
| `rules(array\|Closure)` | Validation, as in Filament's editable columns; the closure gets `$record`. Without `required` the value is nullable |

The options list is **not** a whitelist by itself: `Rule::exists` / `Rule::enum` (or a closure
rule against `$column->getOptions()`, see `UserResource` roles) is what stops a crafted request.

`HasSeoFields::getSlugColumn()` returns the ready `slug` column — `max:255` + unique per table,
the same rules as the SEO section's field.

## Where it is used

| Resource | Columns |
|---|---|
| Pages | `title`, `slug`, `rank` |
| News | `title`, `slug`, `published_at` (date + time) |
| Languages | `title`, `rank` |
| Countries / Regions / Cities | `title`, `rank` |
| Categories | `title`, `slug` (no `rank` column: the tree order is the nested set) |
| Brands | `title`, `slug`, `rank` |
| Products | `title`, category (select), brand (select), `price` (the smallest set's, see `docs/project/shop.md`), `rank` |
| Users | `name`, roles (multiselect; `super_admin` offered to Super Admins only) |
| Feedback | `name` |

## Files

| File | Role |
|---|---|
| `app/Filament/Tables/Columns/InlineEditColumn.php` | The column: `Editable` + Filament's `CanUpdateState` / `CanBeValidated`, the edit gate, the field markup |
| `resources/js/filament/inline-edit-column.js` | Alpine component (open / save / cancel), loaded on demand with `x-load` |
| `resources/css/filament/inline-edit-column.css` | Hover hint, field and checkbox-list styles |
| `app/Providers/Filament/AdminPanelProvider.php` (`boot()`) | Registers both files with `FilamentAsset` |

The JS and CSS are **not** built by Vite: they are plain files that `php artisan filament:assets`
copies to `public/js/app/` and `public/css/app/` (git-ignored, like Filament's own assets).
`composer install` runs it through `filament:upgrade`; after editing either file run
`./vendor/bin/sail artisan filament:assets` and hard-reload the page.

Saving goes through Filament's `updateTableColumnState()` Livewire method — the same entry
point as `TextInputColumn`, so a hidden or disabled column is refused server-side.

Tests: `tests/Feature/Filament/InlineEdit/` (generic behaviour, plus a fixture table for the
json multiselect and the date mode), and the inline cases in `ProductResourceTest` and
`UserResourceTest`.
