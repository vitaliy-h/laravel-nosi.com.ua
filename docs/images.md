# Images

Project uses Laravel public storage for uploaded images.

---

# Filesystem

Set correct filesystem disk in `.env`:

```env
FILESYSTEM_DISK=public
```

---

# HasImage Trait

All models with images MUST use:

```php
use App\Modules\Core\Traits\Models\HasImage;
```

Trait responsibilities:

* Generates image path and normalizes filename (transliteration via `Str::slug(...)`)
* Moves temp files to permanent path after record is created
* Deletes old files when image field is replaced
* Deletes image conversions (previews) when originals are replaced or removed
* Deletes entire directory when model is deleted
* Deletes all conversion directories for the record when model is deleted
* Cleans up empty directories after file deletion
* Generates eager presets (see [Eager thumb on save](#eager-thumb-on-save)) after create/update
* Appends `$model->image` and `$model->images` URL accessors

---

# Single Image Field

## Path format

```text
{table}/{id}/{field}/{filename}
```

Example:

```text
news/15/img/banner.jpg
```

## Model setup

```php
class News extends Model
{
    use HasImage;

    protected $fillable = ['img', ...];
}
```

Default field is `img`. To use a different field name, declare `$imgFields`:

```php
class Product extends Model
{
    use HasImage;

    protected array $imgFields = ['cover'];

    protected $fillable = ['cover', ...];
}
```

## Filament upload

```php
FileUpload::make('img')
    ->image()
    ->visibility('public')
    ->getUploadedFileNameForStorageUsing(function ($file, $record) {
        return ($record ?? new News())->getFilePath(
            $file->getClientOriginalName(),
            'img',
        );
    })
```

## URL accessor

```php
$model->image // full public URL, or /assets/images/no_image.svg if empty
```

---

# Array Image Field

## Path format

Each image gets its own UUID subdirectory to avoid filename collisions:

```text
{table}/{id}/{field}/{uuid}/{filename}
```

Example:

```text
news/15/imgs/550e8400-e29b-41d4-a716-446655440000/photo.jpg
```

## Migration

```php
$table->text('imgs')->nullable();
```

## Model setup

Declare `$imgArrayFields` with the field names and add `array` cast:

```php
class News extends Model
{
    use HasImage;

    protected array $imgArrayFields = ['imgs'];

    protected $fillable = ['img', 'imgs', ...];

    protected $casts = [
        'imgs' => 'array',
    ];
}
```

Multiple array fields:

```php
protected array $imgArrayFields = ['gallery', 'attachments'];

protected $casts = [
    'gallery'     => 'array',
    'attachments' => 'array',
];
```

### Custom storage path segment (`$imgArrayPathFields`)

By default, array field paths use the **database column name** as the path segment
(`imgs` → `{table}/{id}/imgs/…`). When the Filament field name differs from the column
name, map the column to the storage segment:

```php
class Product extends Model
{
    use HasImage;

    protected array $imgArrayFields = ['imgs'];
    protected array $imgArrayPathFields = ['imgs' => 'gallery'];

    protected $casts = [
        'imgs' => 'array',
    ];
}
```

Filament uploads gallery files with field `gallery`; after save the permanent path is:

```text
shop_products/15/gallery/photo.jpg
```

The `imgs` column still stores the path; only the on-disk directory name changes.

Filament upload for Product gallery (note `gallery` in `getFilePath`, not `imgs`):

```php
FileUpload::make('imgs')
    ->image()
    ->multiple()
    ->getUploadedFileNameForStorageUsing(function ($file, $record) {
        return ($record ?? new Product())->getFilePath(
            $file->getClientOriginalName(),
            'gallery',
        );
    })
```

## Filament upload

```php
FileUpload::make('imgs')
    ->image()
    ->multiple()
    ->reorderable()
    ->panelLayout('grid')
    ->visibility('public')
    ->getUploadedFileNameForStorageUsing(function ($file, $record) {
        return ($record ?? new News())->getFilePath(
            $file->getClientOriginalName(),
            'imgs',
        );
    })
```

## URL accessor

```php
$model->images // array of full public URLs
```

---

# Combining single and array fields

```php
class Article extends Model
{
    use HasImage;

    protected array $imgFields       = ['cover', 'thumbnail'];
    protected array $imgArrayFields  = ['gallery'];

    protected $fillable = ['cover', 'thumbnail', 'gallery', ...];

    protected $casts = [
        'gallery' => 'array',
    ];
}
```

---

# How temp→permanent move works

On **create**, Filament stores the file at a temp path because the record has no ID yet:

```text
{table}/temp/{field}/{filename}          ← single
{table}/temp/{field}/{uuid}/{filename}   ← array
```

After the record is saved, `HasImage` automatically:

1. Moves every temp file to the permanent path with the real ID.
2. Updates the stored paths in the database (`saveQuietly`).
3. Deletes empty temp directories.

No manual action required.

---

# File deletion behaviour

| Event | What happens |
|---|---|
| Field value replaced | Old original deleted; conversions for old path deleted; empty parent dirs removed |
| Image removed from array | Removed originals deleted; conversions for removed paths deleted; empty parent dirs removed |
| Model deleted | Entire `{table}/{id}/` original directory deleted; all `{preset}/{table}/{id}/` conversion directories deleted |

Conversion cleanup uses every preset defined in `config/image_sizes.php` (not only eager presets).

---

# Notes

* Always use `public` visibility on `FileUpload`
* File path → `getFilePath()`
* Never store paths manually — always go through trait methods
* `$imgFields` defaults to `['img']` if not declared
* `$imgArrayFields` defaults to `[]` if not declared

---

# Image Resizing

The project supports **named image presets** (e.g. `thumb`, `large`). Presets listed in
`config/image.php` → `eager_presets` are generated when a model with `HasImage` is saved
(admin create/update). All other presets from `config/image_sizes.php` are generated
**on demand** on the first HTTP request.

Original uploads are unchanged. `$model->image` still returns the full-size original URL.

---

## Eager thumb on save

When a record with `HasImage` is created or an image field is updated, the trait calls
`ImageConversionService::generateEager()` for each affected path. Eager presets are
configured in `config/image.php`:

```php
'eager_presets' => ['thumb'],
```

Only presets in this list are pre-generated on save. Other presets (e.g. `large`) remain
lazy — they are created on the first request to `/storage/{preset}/…`.

Eager generation runs after temp→permanent move on create, and after old files are deleted
on update. Failures are reported to the log and do not block the save.

---

## How it works

```
Browser GET /storage/thumb/shop_products/1/img/product.jpg
  │
  ├─ preview already cached at thumb/shop_products/1/img/product.jpg?  → yes  → webserver serves file (no PHP)
  │
  ├─ preset "thumb" exists in config/image_sizes.php?  → no  → 404
  │
  ├─ original exists at shop_products/1/img/product.jpg?  → no  → 404
  │
  ├─ entity "shop_products" configured for preset "thumb"?  → no  → 404
  │
  └─ resize original → save preview → serve file
```

For `thumb`, the preview is usually already on disk after admin save (eager generation).
The on-demand branch above applies to non-eager presets and as a fallback when eager
generation did not run or failed.

Route: `GET /storage/{preset}/{path}` (registered in `app/Modules/Core/routes/web.php`).

**Breaking change:** URLs under `/img/{preset}/…` are no longer served. Cached preview files on disk are unchanged — no cache rebuild is required.

Image processing is handled by [Intervention Image](https://image.intervention.io/) via
the `intervention/image-laravel` package. Driver defaults to GD (`config/image.php`).

---

## Storage paths

| Type | Disk path | Web URL |
|------|-----------|---------|
| Original | `storage/app/public/{table}/{id}/{field}/{filename}` | `/storage/{table}/{id}/{field}/{filename}` |
| Preview | `storage/app/public/{preset}/{table}/{id}/{field}/{filename}` | `/storage/{preset}/{table}/{id}/{field}/{filename}` |

Example:

```text
Original:  shop_products/1/img/product.jpg
Preview:   thumb/shop_products/1/img/product.jpg
```

Previews live under a `{preset}/` prefix on the `public` disk. When an original is
replaced, removed from an array field, or the model is deleted, `HasImage` removes the
matching conversion files or `{preset}/{table}/{id}/` directories automatically.

---

## Preset configuration

All available sizes are defined in **`config/image_sizes.php`** — the single source of
truth for frontend and backend.

```php
return [
    'thumb' => [
        'shop_products' => [
            'width'  => 300,
            'height' => 300,
            'mode'   => 'crop',
        ],
        'news' => [
            'width' => 300,
            'mode'  => 'contain',
        ],
    ],
    'large' => [
        'shop_products' => [
            'width'  => 1200,
            'height' => 1200,
            'mode'   => 'contain',
        ],
    ],
];
```

**Structure:**

| Level | Key | Meaning |
|-------|-----|---------|
| 1 | `thumb`, `large`, … | Preset name (used in URLs and helpers) |
| 2 | `shop_products`, `news`, … | Entity name — must match the **table name** (first segment of the image path) |

**Fields per entity:**

| Field | Required | Description |
|-------|----------|-------------|
| `width` | yes | Target width in pixels |
| `height` | no | Target height in pixels; optional for `contain` (height scales proportionally) |
| `mode` | yes | `crop` or `contain` (see below) |

### Resize modes

| Mode | Behaviour | Typical use |
|------|-----------|-------------|
| `crop` | Exact W×H, image is cropped to fill the box | Square thumbnails, cards |
| `contain` | Fit inside W (and H if given), keep aspect ratio, never upscale | Banners, detail views |

---

## Building preview URLs

### Global helper

```php
image_url(string $path, string $preset): string
```

`$path` is the raw relative path stored in the database (e.g. `shop_products/1/img/product.jpg`).

```php
image_url($product->img, 'thumb');
// → http://localhost/storage/thumb/shop_products/1/img/product.jpg
```

Pass a non-empty path. For models, prefer `imageUrl()` — it handles the empty-image fallback.

### HasImage method

```php
$product->imageUrl('thumb');
```

* If `img` is empty → returns `/assets/images/no_image.svg` (same fallback as `$model->image`)
* Otherwise → delegates to `image_url($this->img, $preset)`

`imageUrl()` is an explicit method, not a JSON accessor. It is **not** appended to API
responses automatically.

### Original vs preview

```php
$product->image;              // full-size original: /storage/shop_products/1/img/product.jpg
$product->imageUrl('thumb');   // resized preview:    /storage/thumb/shop_products/1/img/product.jpg
```

---

## Usage in Blade

```blade
{{-- Thumbnail in catalog --}}
<img src="{{ $product->imageUrl('thumb') }}" alt="{{ $product->name }}">

{{-- Large image on detail page --}}
<img src="{{ $product->imageUrl('large') }}" alt="{{ $product->name }}">

{{-- Raw path from DB (e.g. in a loop over imgs array) --}}
<img src="{{ image_url($path, 'thumb') }}" alt="">
```

---

## Clearing preview cache

```bash
php artisan images:clear                         # all presets from config/image_sizes.php
php artisan images:clear thumb                     # entire thumb/ directory
php artisan images:clear thumb shop_products       # only thumb/shop_products/
```

The command is idempotent: missing directories are ignored. An unknown preset name prints
an error and exits with code `1`.

After clearing, previews are regenerated on the next HTTP request.

---

## Adding a new preset or entity

1. Open `config/image_sizes.php`.
2. Add a preset key (or an entity under an existing preset).
3. Set `width`, optional `height`, and `mode`.
4. Use the preset name in templates: `$model->imageUrl('my_preset')`.

**Entity name must match the Eloquent table name** — the first path segment of stored
images. Current entities with images:

| Entity key | Model |
|------------|-------|
| `shop_products` | `Product` |
| `shop_categories` | `Category` |
| `shop_brands` | `Brand` |
| `news` | `News` |

Example — add a `medium` preset for categories:

```php
'medium' => [
    'shop_categories' => [
        'width'  => 600,
        'height' => 400,
        'mode'   => 'crop',
    ],
],
```

```blade
<img src="{{ $category->imageUrl('medium') }}" alt="">
```

No code changes are required beyond the config file.

---

## Image driver

Intervention Image driver is configured in `config/image.php`:

```php
'driver' => env('IMAGE_DRIVER', \Intervention\Image\Drivers\Gd\Driver::class),
```

GD is the default and works out of the box in Sail. To use Imagick, set `IMAGE_DRIVER`
in `.env` to `\Intervention\Image\Drivers\Imagick\Driver::class` (requires `ext-imagick`).

---

## Resizing notes

* **`thumb`** is generated eagerly on admin save (`config/image.php` → `eager_presets`).
* Other presets are generated **on demand** — there is no queue or batch pre-generation.
* First request for a lazy preset runs PHP image processing; later requests serve the cached file.
* Array image fields (`imgs`) are not covered by `imageUrl()` — use `image_url($path, $preset)` with each path from the array.
* If you change dimensions in `config/image_sizes.php`, run `images:clear` for the affected preset so old cached files are removed; re-save records in admin to regenerate eager thumbs.

---

[Back to main readme](./README.md)
