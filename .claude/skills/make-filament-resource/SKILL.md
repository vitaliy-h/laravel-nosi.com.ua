---
name: make-filament-resource
description: >
    Interactive generator for Filament v5 admin panel resources following project conventions:
    simple resource with table + form, navigation item in AdminPanelProvider, translations.
    Use when the user says "make filament resource", "create admin resource", "зроби ресурс в адмінку",
    "filament resource", "admin resource", or invokes /make-filament-resource.
---

## Overview

Generates a Filament v5 `--simple` resource for a given model. Reads model fields and casts
to auto-build `table()` and `form()`, then wires the resource into `AdminPanelProvider` navigation
and adds missing translation keys.

---

## Flow

### Step 1 — Model

If the user did not specify a model, ask: **Which model?** (PascalCase, e.g. `Product`)

Read `app/Modules/Page/Enums/PageModule.php` to get existing modules.
Find the model at `app/Modules/{Module}/Models/{ModelName}.php`.

If multiple modules exist with that model name, present options and ask.
If not found anywhere, tell the user and stop.

Read the model file. Extract:

- `$fillable` — list of fields to work with
- `$casts` — determine field types (booleans, datetimes, enums, arrays)
- `use` traits — detect `HasImage`, `HasVisibility`
- `BelongsTo` / `HasMany` / other relations — read method names and return types
- Presence of specific fields: `img`, `title`, `name`, `slug`, `rank`, `display`,
  `published_at` (or any `*_at` timestamp), `meta_title`, `meta_description`,
  `text`, `body`, `short_text`
- **SEO mode**: active when model has ANY of `slug`, `meta_title`, `meta_description`
- **Nested set mode**: active when model `use`s `Kalnoy\Nestedset\NodeTrait`

### Step 2 — Confirm plan

Print a summary before generating:

```
Model:     App\Modules\{Module}\Models\{ModelName}
Resource:  app/Filament/Resources/{Module}/{ModelNamePlural}/{ModelName}Resource.php
Pages dir: app/Filament/Resources/{Module}/{ModelNamePlural}/Pages/
Nav:       AdminPanelProvider → NavigationItem for {ModelName}
Lang keys: lang/en/admin.php + lang/uk/admin.php (same keys in both)
```

`{ModelNamePlural}` is the artisan generator's auto-pluralized form of `{ModelName}` (e.g.
`Product` → `Products`, `Category` → `Categories`). The `{Module}/` segment is **not**
always there — see «Where the resource actually goes» below, and print the path you
picked in this summary.

Ask: **Proceed?**

### Step 3 — Run artisan

**Before running**, detect the correct runner:

```bash
# Check for Laravel Sail first
if [ -f ./vendor/bin/sail ]; then
    ARTISAN="./vendor/bin/sail artisan"
else
    ARTISAN="php artisan"
fi
```

If Sail is present → use `./vendor/bin/sail artisan` for all artisan calls.
If Sail is absent → check that `php --version` satisfies the project's PHP requirement (`composer.json` → `require.php`). If the system `php` doesn't match, look for versioned binaries (`php8.4`, `php8.3`, …) or report that no suitable PHP was found and create files manually.

```bash
# with Sail:
./vendor/bin/sail artisan make:filament-resource {Module}/{ModelName} --simple --generate --no-interaction

# without Sail (compatible php):
php artisan make:filament-resource {Module}/{ModelName} --simple --generate --no-interaction
```

This creates:

- `app/Filament/Resources/{Module}/{ModelNamePlural}/{ModelName}Resource.php`
- `app/Filament/Resources/{Module}/{ModelNamePlural}/Pages/Manage{ModelName}s.php`

#### Where the resource actually goes

`app/Filament/Resources/` is grouped by **admin domain**, not by `app/Modules/` folder, and
only a domain that owns several resources gets a folder of its own. Check the existing tree
before generating and follow it:

| Case | Path | Real examples |
|---|---|---|
| Domain with several resources | `Resources/{Domain}/{ModelNamePlural}/{ModelName}Resource.php` | `Shop/Products/ProductResource.php`, `Shop/Categories/CategoryResource.php`, `Geo/GeoCountries/GeoCountryResource.php` |
| Single resource | `Resources/{ModelNamePlural}/{ModelName}Resource.php` | `Users/UserResource.php`, `Subscribers/SubscriberResource.php`, `SmsTemplates/SmsTemplateResource.php`, `SocialProfiles/SocialProfileResource.php` |
| Model name is already plural / equals the folder name | `Resources/{ModelName}/{ModelName}Resource.php` | `News/NewsResource.php`, `Feedback/FeedbackResource.php` |

Two things the generator's default path gets wrong here:

- **The domain is not the module.** `ShopCart` and `ShopOrder` models are administered under
  `Shop/`: `Shop/Cart/CartResource.php`, `Shop/Orders/OrderResource.php`,
  `Shop/Promocodes/PromocodeResource.php`. `Core` models are administered flat:
  `Logs/LogResource.php`, `AuthLogs/AuthLogResource.php`.
- **`Shop/Cart/` is singular** — the folder was not pluralized. Match the neighbours rather
  than trusting the pluralizer.

So pass the path you actually want to `make:filament-resource` (`Shop/Product`, `Users/User`,
…) and move the generated folder if the generator guessed differently.

The generated file will have placeholder `form()` and `table()` — replace both entirely per rules below.

### Step 4 — Rewrite `table()`

#### Default sort

Priority (first match wins):

1. Field `rank` present → `->defaultSort('rank', 'desc')`
2. Field matching `*_at` (other than `created_at`/`updated_at`) → `->defaultSort('{field}', 'desc')`
3. Otherwise → `->defaultSort('created_at', 'desc')`

#### Columns order

1. `ImageColumn::make('img')` — if `img` field exists (no label needed, it's visual)
2. `InlineEditColumn::make('title')` (or `name`) with the form's rules, e.g. `->rules(['required', 'max:255'])->searchable()` — **nested set mode**: wrap with depth prefix (the field still edits the raw title):
   ```php
   InlineEditColumn::make('title')
       ->rules(['required', 'max:255'])
       ->formatStateUsing(fn ($record) => str_repeat('— ', $record->depth) . $record->title)
       ->searchable(),
   ```
3. `static::getSlugColumn()` — if `slug` exists (SEO mode: inline-editable, unique per table)
4. Enum fields (from `$casts` where cast is an Enum class):
   ```php
   TextColumn::make('{field}')
       ->searchable()
       ->badge()
       ->formatStateUsing(fn($state) => $state?->label()),
   ```
5. Relation columns — for each `BelongsTo` relation:
   ```php
   TextColumn::make('{relation}.{title_or_name}')
       ->label(__('admin.fields.{relation_id}')),
   ```
   Detect the related model's title field (`title` vs `name`) by reading the related model.
6. Other notable scalar fields (article, price, etc.) — `TextColumn::make('{field}')`
7. `InlineEditColumn::make('rank')` — if `rank` field exists (see `docs/filament/inline-edit.md`):
   ```php
   InlineEditColumn::make('rank')
       ->editAsNumber()
       ->rules(['required', 'integer'])
       ->sortable()
       ->alignEnd(),
   ```
8. `TextColumn::make('{field}')->dateTime()->sortable()` — for non-hidden date fields (e.g. `published_at`)
9. `ToggleColumn::make('display')` — if `display` field exists (always last before hidden)
10. Always at the end (hidden by default):
    ```php
    TextColumn::make('created_at')
        ->dateTime()
        ->sortable()
        ->toggleable(isToggledHiddenByDefault: true),
    TextColumn::make('updated_at')
        ->dateTime()
        ->sortable()
        ->toggleable(isToggledHiddenByDefault: true),
    TextColumn::make('id')
        ->sortable()
        ->toggleable(isToggledHiddenByDefault: true),
    ```

#### Filters

Add `SelectFilter` for each:

- Enum field:
  ```php
  SelectFilter::make('{field}')
      ->options(\App\Modules\{Module}\Enums\{EnumClass}::class),
  ```
  (Filament SelectFilter accepts a backed enum class directly.)
- BelongsTo relation:
  ```php
  SelectFilter::make('{relation_id}')
      ->relationship('{relation}', '{title_or_name}')
      ->searchable()
      ->preload(),
  ```

If no filters → leave `->filters([])` (empty, no comment).

#### Record actions (standard)

```php
->recordActions([
    BaseEditAction::make()->iconButton(),
    DeleteAction::make()->iconButton(),
], position: \Filament\Tables\Enums\RecordActionsPosition::BeforeColumns)
->toolbarActions([
    \Filament\Actions\CreateAction::make(),
    BulkActionGroup::make([
        DeleteBulkAction::make(),
    ]),
])
```

#### Record actions (nested set mode)

Replace `DeleteAction` with a guarded version (prevent deletion of non-leaf nodes):

```php
->recordActions([
    BaseEditAction::make()->iconButton(),
    DeleteAction::make()
        ->iconButton()
        ->disabled(fn ($record) => $record->children()->exists())
        ->tooltip(fn ($record) => match (true) {
            $record->children()->exists() => __('admin.messages.delete_forbidden_has_children'),
            default                        => null,
        }),
], position: \Filament\Tables\Enums\RecordActionsPosition::BeforeColumns)
->toolbarActions([
    NestedSetFields::createAction(), // NOT CreateAction::make()
    BulkActionGroup::make([
        DeleteBulkAction::make(),
    ]),
])
```

Add to imports: `use App\Filament\Support\NestedSetFields;`

#### Table query (nested set mode)

```php
->modifyQueryUsing(fn ($query) => $query->withDepth()->defaultOrder())
->defaultSort('_lft')
```

### Step 5 — Rewrite `form()`

Structure: `->columns(12)` always.

#### Field order

**Row 1 — identity & control fields** (always together in this order):

- `TextInput::make('title')` or `name` if no `title` — no `->live()` / `->afterStateUpdated()` (slug handled by `HasSeoFields` when in SEO mode):
  ```php
  TextInput::make('title')
      ->columnSpan({title_span})
      ->required(),
  ```
- `TextInput::make('rank')->numeric()->columnSpan(2)->default(1)` — if exists
- `Toggle::make('display')->columnSpan(2)->inline(false)->default(true)` — if exists

**Row 1 columnSpan rule — title/name field MUST fill remaining space:**

Calculate fixed spans of other Row 1 fields:
- `rank` present → +2
- `display` present → +2

`title_span = 12 − (sum of other Row 1 field spans)`

Example: `rank`+`display` → 2+2=4 → title gets `->columnSpan(8)`.
Example: `display` only → 2 → title gets `->columnSpan(10)`.
Example: no other fields → title gets `->columnSpan(12)`.

**Row 1b — tree placement fields** (nested set mode only, after Row 1):

```php
...NestedSetFields::make(ModelName::class), // parent_id + _position_id, 6+6 cols
```

Import: `use App\Filament\Support\NestedSetFields;`
See `docs/filament/nested-sets.md` for full details.

**Row 2 — date/time fields** (non-hidden `*_at` fields, e.g. `published_at`):

```php
DateTimePicker::make('published_at')->columnSpan(4),
```

**Row 3 — relations and enums** (columnSpan(4) each unless there are many):

- For each `BelongsTo` relation:
  ```php
  Select::make('{relation_id}')
      ->relationship('{relation}', '{title_or_name}')
      ->searchable()
      ->preload()
      ->columnSpan(4),
  ```
- For each enum field:
  ```php
  Select::make('{field}')
      ->options(\App\Modules\{Module}\Enums\{EnumClass}::options())
      ->columnSpan(4),
  ```
  All project enums use `HasEnumHelpers` trait which provides `options()`. Always use `::options()`.

**Row 4 — image upload** (if `img` field exists):

```php
FileUpload::make('img')
    ->image()
    ->columnSpan(6)
    ->visibility('public')
    ->getUploadedFileNameForStorageUsing(function ($file, $record) {
        return ($record ?? new {ModelName}())->getFilePath(
            $file->getClientOriginalName(),
            'img',
        );
    }),
```

**Row 5 — other scalar fields** (article, price, etc.):

- Numeric fields: `->columnSpan(2)` or `->columnSpan(3)`
- String fields: `->columnSpan(4)`

**Row 6 — large text fields** (`text`, `body`, `short_text`, etc.):

- `short_text`: `Textarea::make('short_text')->columnSpan(6),`
- `text` / `body`: `RichEditor::make('text')->columnSpanFull(),`

**Row 7 — SEO / meta fields** (always last, if SEO mode active):

```php
static::getSeoSection(),
```

#### Translation labels

No resource in this codebase sets a `$translationNamespace` property. Plain fields (`title`,
`display`, `slug`, ...) are left without an explicit `->label()` call: `FilamentServiceProvider`
labels every field/column/entry/filter from `admin.fields.{name}` when that key exists, so the
key **must** exist (in both `en` and `uk`), otherwise the admin shows Filament's humanized English
name. Add `->label(__('admin.fields.{field_name}'))` explicitly only
where the field/column name doesn't already read as a good label on its own — most commonly
relation columns in the table (e.g. `category.title` needs a label because the translation key
is `category_id`, not `category.title`) and relation selects in the form.

**Before adding any field, check `lang/en/admin.php['fields']` for an existing key.** Field
keys are flat (not nested per-resource) and shared across resources. If missing, add the key
to `lang/en/admin.php` and `lang/uk/admin.php` (Step 8).

### Step 6 — Fix resource namespace and model reference

After artisan generates the file, ensure:

1. Namespace matches: `App\Filament\Resources\{Module}\{ModelNamePlural}` (or just
   `App\Filament\Resources\{Module}` when the generator collapsed the plural folder — see Step 3)
2. `protected static ?string $model = {ModelName}::class;` with correct import
3. `getPages()` references the correct Pages class:
   ```php
   'index' => \App\Filament\Resources\{Module}\{ModelNamePlural}\Pages\Manage{ModelName}s::route('/'),
   ```
4. Required imports added at top:
    - `use App\Filament\Actions\BaseEditAction;`
    - `use Filament\Actions\{BulkActionGroup, DeleteAction, DeleteBulkAction};`
    - `use Filament\Tables\Filters\SelectFilter;` (if filters used)
    - `use Filament\Tables\Columns\{ImageColumn, TextColumn, ToggleColumn};`
    - `use App\Filament\Tables\Columns\InlineEditColumn;` (rank, title / name, slug)
    - `use Filament\Forms\Components\{FileUpload, RichEditor, Select, Textarea, TextInput, Toggle, DateTimePicker};`
    - All Enum and Model imports used
    - **SEO mode only**: `use App\Filament\Traits\HasSeoFields;`
    - **Nested set mode only**: `use App\Filament\Support\NestedSetFields;`
5. **SEO mode only**: add `use HasSeoFields;` inside the resource class body (after the opening brace, before `$model`)
6. Resource labels, as the first methods of the class (otherwise the admin shows the English
   class name, e.g. "Create product"):
   ```php
   public static function getModelLabel(): string
   {
       return __('admin.models.{model_key}');
   }

   public static function getPluralModelLabel(): string
   {
       return __('admin.tables.{table_key}');
   }
   ```

### Step 7 — Add navigation to AdminPanelProvider

File: `app/Providers/Filament/AdminPanelProvider.php`

Decision: standalone item or inside existing group?

- If model belongs to a module that already has a `NavigationGroup` → add inside that group
- Otherwise → add as top-level `NavigationItem` in `->items([...])`

Template for NavigationItem:

```php
NavigationItem::make(__('admin.tables.{table_key}'))
    ->icon('heroicon-o-{icon}')
    ->url(\App\Filament\Resources\{Module}\{ModelNamePlural}\{ModelName}Resource::getUrl())
    ->isActiveWhen(fn (): bool => Request::routeIs('filament.admin.resources.{module-slug}.{model-plural-slug}*')),
```

Choose a sensible heroicon based on the model (cube, squares-2x2, document-text, etc.).

Route pattern: Filament converts `{Module}/{ModelName}` to kebab-case segments, e.g.:

- `Shop/Product` → `filament.admin.resources.shop.products*`
- `News/News` → `filament.admin.resources.news.*`

Ask the user what icon and navigation group (if unsure).

### Step 8 — Add missing translation keys

Check `lang/en/admin.php` and `lang/uk/admin.php` — both must get the same keys. The admin panel
is shown in Ukrainian by default (`ADMIN_LOCALE=uk`).

**In `['tables']`** — add the navigation label key used in Step 7:

```php
'{table_key}' => '{Human Label}',
```

**In `['models']`** — the singular label used in Step 6 (in `uk` — accusative case, lowercase,
because Filament renders "Створити :label" / "Редагувати :label"):

```php
'{model_key}' => '{singular label}',   // uk: 'товар', 'сторінку', 'категорію'
```

**In `['fields']`** — add keys for any fields not already present.
Add the same keys to `lang/uk/admin.php` with Ukrainian translations (ask the user if unsure).

### Step 9 — Summary

Print:

- Resource file path
- Pages file path
- Navigation added to: AdminPanelProvider (item or group)
- Translation keys added (with files)
- Any fields skipped and why
- Next step: `php artisan filament:optimize-clear` (or `./vendor/bin/sail artisan filament:optimize-clear` if Sail is used) then test in browser

---

## Conventions reference

### Table column types by field

| Field type             | Column class                                                                    |
|------------------------|---------------------------------------------------------------------------------|
| string with `img` name | `ImageColumn`                                                                   |
| string / text (short)  | `TextColumn` with `->searchable()`                                              |
| enum (in `$casts`)     | `TextColumn` with `->badge()->formatStateUsing(fn($state) => $state?->label())` |
| boolean                | `ToggleColumn`                                                                  |
| integer `rank`         | `InlineEditColumn` with `->editAsNumber()->rules(['required', 'integer'])->sortable()->alignEnd()` |
| timestamp/date         | `TextColumn` with `->dateTime()->sortable()`                                    |
| relation               | `TextColumn::make('{rel}.{field}')`                                             |

### Form field types by field

| Field              | Component                | columnSpan |
|--------------------|--------------------------|------------|
| `title` / `name`   | `TextInput`              | 12 − (sum of other Row 1 spans) |
| `slug`             | via `getSeoSection()` (SEO mode)  | —          |
| `rank`             | `TextInput->numeric()`   | 2          |
| `display`          | `Toggle->inline(false)`  | 2          |
| `*_at` datetime    | `DateTimePicker`         | 4          |
| relation `*_id`    | `Select->relationship()` | 4          |
| enum               | `Select->options()`      | 4          |
| `img`              | `FileUpload->image()`    | 6          |
| `price`, numeric   | `TextInput->numeric()`   | 2–3        |
| other string       | `TextInput`              | 4          |
| `short_text`       | `Textarea`               | 6          |
| `text` / `body`    | `RichEditor`             | Full       |
| `meta_title`       | via `getSeoSection()` (SEO mode)  | — |
| `meta_description` | via `getSeoSection()` (SEO mode)  | — |

### File paths

| Artifact   | Path                                                           |
|------------|----------------------------------------------------------------|
| Resource   | `app/Filament/Resources/{Module}/{ModelNamePlural}/{ModelName}Resource.php`      |
| Pages      | `app/Filament/Resources/{Module}/{ModelNamePlural}/Pages/Manage{ModelName}s.php` |
| Navigation | `app/Providers/Filament/AdminPanelProvider.php`                |
| Lang EN    | `lang/en/admin.php`                                            |
| Lang UK    | `lang/uk/admin.php`                                            |

### Namespace pattern

```
App\Filament\Resources\{Module}\{ModelNamePlural}
```

Example: `App\Filament\Resources\Shop\Products` for `Shop/Products/ProductResource.php`.
When model and module names coincide (e.g. `News`), the plural folder collapses and the
namespace is just `App\Filament\Resources\{Module}` (e.g. `App\Filament\Resources\News`).
