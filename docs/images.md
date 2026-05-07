# Images

Project uses Laravel public storage for uploaded images.

---

# Filesystem

Set correct filesystem disk in `.env`:

```env
FILESYSTEM_DISK
```

Default and recommended value:

```env
FILESYSTEM_DISK=public
```

---

# Image Field Convention

Default field name for single image:

```php
img
```

Database field stores:

```text
full path relative to storage
```

Example:

```text
news/1/img/image.jpg
```

---

# HasImage Trait

All models with images MUST use:

```php
HasImage
```

Trait responsibilities:

* Generates image path
* Adds `image` attribute with full image URL
* Deletes image automatically when model is deleted

---

# Image Path Format

Generated image path:

```text
{table}/{id}/{field}/{filename}
```

Example:

```text
news/15/img/banner.jpg
```

---

# Image URL Attribute

Trait automatically appends:

```php
$model->image
```

Returns:

```text
full public image URL
```

---

# Filament Upload Configuration

Example configuration:

```php
FileUpload::make('img')
    ->label(__('admin.news.fields.img'))
    ->image()
    ->visibility('public')
    ->getUploadedFileNameForStorageUsing(function ($file, $record) {
        return ($record ?? new News())->getFilePath(
            $file->getClientOriginalName(),
            'img',
        );
    })
```

---

# Notes

* Always use `public` visibility
* Always use `HasImage` for models with images
* Always use `img` for single image field name
* Storage path generation must go through `getFilePath()`
