# Nested Sets (Tree Structures) in Filament

Tree-structured models (categories, menus, regions, etc.) use the `kalnoy/nestedset` package and a shared Filament helper class that provides pre-wired form fields + position saving.

---

## Package Setup

### 1. Install

```bash
composer require kalnoy/nestedset
```

### 2. Migration

```php
use Kalnoy\Nestedset\NestedSet;

Schema::create('shop_categories', function (Blueprint $table) {
    $table->id();
    $table->string('title');
    // ... other columns ...
    NestedSet::columns($table); // adds _lft, _rgt, parent_id
    $table->timestamps();
});
```

### 3. Model

```php
use Kalnoy\Nestedset\NodeTrait;

class Category extends Model
{
    use NodeTrait;

    protected $fillable = [
        'parent_id',
        '_lft',
        '_rgt',
        // ... other fields ...
    ];
}
```

`NodeTrait` intercepts `parent_id` changes on save and automatically updates `_lft`/`_rgt` for the whole subtree.

---

## Filament Admin Integration

### The helper: `NestedSetFields`

`App\Filament\Support\NestedSetFields` provides:

| Method | Returns | Purpose |
|--------|---------|---------|
| `::make(Model::class)` | `array` (2 Select fields) | Drop into `form()` components |
| `::createAction()` | `CreateAction` | Drop into `toolbarActions()` — pre-wired with position save |
| `::applyPosition($record, $data)` | `void` | Called automatically by `BaseEditAction` |

### Resource form

```php
use App\Filament\Support\NestedSetFields;

->components([
    TextInput::make('title')->columnSpan(6)->required(),
    Toggle::make('display')->columnSpan(6)->inline(false)->default(true),

    ...NestedSetFields::make(Category::class), // adds parent_id + _position_id (6+6 cols)

    // ... rest of fields ...
])
```

`NestedSetFields::make()` accepts optional args:

```php
NestedSetFields::make(
    modelClass: Category::class,
    columnSpan: 6,        // default: 6 (each field takes 6 of 12 cols)
    labelField: 'title',  // default: 'title' — field shown in dropdowns
)
```

### Resource table toolbar

Replace the default `CreateAction::make()` with the nestedset-aware version:

```php
->toolbarActions([
    NestedSetFields::createAction(), // instead of \Filament\Actions\CreateAction::make()
    BulkActionGroup::make([
        DeleteBulkAction::make(),
    ]),
])
```

### Edit action

`BaseEditAction` calls `NestedSetFields::applyPosition()` automatically after every update. No extra wiring needed — just use `BaseEditAction::make()` as usual.

---

## How Position Saving Works

Two virtual form fields drive tree placement:

**`parent_id`** — sets the node's parent. Changing this moves the entire subtree. `NodeTrait` handles the `_lft`/`_rgt` recalculation on `save()`.

**`_position_id`** — sets order within siblings. The dropdown lists all current siblings. On edit, it pre-fills with the record's own id (= "keep current position"). If the user picks a different sibling, `afterNode($sibling)->save()` is called after the main update, placing the record immediately after the selected sibling.

Flow on save:
1. `$record->update($data)` — nestedset moves node to new parent (last child position by default)
2. `NestedSetFields::applyPosition($record, $data)` — if `_position_id ≠ record->id`, calls `$record->afterNode($sibling)->save()`

---

## Table Display

Show hierarchy with depth-based indentation in `TextColumn`:

```php
->modifyQueryUsing(fn ($query) => $query->withDepth()->defaultOrder())
->defaultSort('_lft')
->columns([
    TextColumn::make('title')
        ->formatStateUsing(fn ($record) =>
            str_repeat('— ', $record->depth) . $record->title
        )
        ->searchable(),
])
```

`withDepth()` adds a `depth` attribute (0 = root). `defaultOrder()` sorts by `_lft` which preserves tree order.

---

## Delete Guard

Prevent deletion of nodes that still have children:

```php
DeleteAction::make()
    ->iconButton()
    ->disabled(fn ($record) =>
        $record->children()->exists() ||
        $record->products()->exists()  // adapt: any relation that blocks deletion
    )
    ->tooltip(fn ($record) => match (true) {
        $record->children()->exists() => __('admin.messages.delete_forbidden_has_children'),
        $record->products()->exists()  => __('admin.messages.delete_forbidden_has_products'),
        default                        => null,
    }),
```

---

## Full Resource Example

`app/Filament/Resources/Shop/Categories/CategoryResource.php` is the reference implementation. Refer to it for a complete working example with image upload, SEO fields, and URL preview action.

---

[Back to Filament docs](../filament.md)
