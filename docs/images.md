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
* Deletes entire directory when model is deleted
* Cleans up empty directories after file deletion
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

## Filament upload

```php
FileUpload::make('imgs')
    ->image()
    ->multiple()
    ->reorderable()
    ->panelLayout('grid')
    ->visibility('public')
    ->getUploadedFileNameForStorageUsing(function ($file, $record) {
        return ($record ?? new News())->getArrayFilePath(
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
| Field value replaced | Old file deleted, empty parent dirs removed |
| Image removed from array | Removed files deleted, empty parent dirs removed |
| Model deleted | Entire `{table}/{id}/` directory deleted |

---

# Notes

* Always use `public` visibility on `FileUpload`
* Single fields → `getFilePath()`, array fields → `getArrayFilePath()`
* Never store paths manually — always go through trait methods
* `$imgFields` defaults to `['img']` if not declared
* `$imgArrayFields` defaults to `[]` if not declared

---

[Back to main readme](./README.md)
