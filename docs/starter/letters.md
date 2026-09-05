# Email Template System (log_letters)

This module provides a centralized system for:

* email templates
* email sending
* attachments handling
* full email logging (audit trail)

---

# 1. Overview

Email system is based on:

* `LetterEnum` → defines email template types
* translations → default subject + body per language for initial data
* `letter_templates` → stores all templates in DB
* `log_letters` → stores all sent emails (audit log)
* Facade → used for sending emails
* Job-based sending (queued)

---

# 2. How email templates work

⚠️ Main email layout template used for all emails:

```
resources/views/emails/template.blade.php
```

This is the base wrapper where all email content is rendered (final HTML layout).

---

Email templates are stored in the **database (`log_letters` templates system)** and can be fully edited via the admin panel.

Each template is identified by an enum value:

```php
LetterEnum::FEEDBACK
LetterEnum::PASSWORD_RESET
```

### Important clarification

- ❌ Translation files are NOT the source of truth for templates
- ✅ Translation files are used ONLY for import/seed initial data
- ✅ After import, templates live in DB and can be edited in admin panel

---

For each template we store in DB:

- subject
- body (HTML/text)
- sender / receiver defaults
- template key (enum)

---

# 3. Adding a new email template

## Step 1: Add enum value

Add the case to `app/Modules/Notification/Enums/LetterEnum.php`. The enum already ships
with `FEEDBACK`, `PASSWORD_RESET`, `REGISTRATION` and `ORDER_CREATED`, so this walkthrough
adds a new one — an order-shipped notification:

```php
enum LetterEnum: string
{
    // ... existing cases
    case ORDER_SHIPPED = 'order_shipped';
}
```

---

## Step 2: Add translations

The seeder reads the template text from `letter_templates.{key}` via `__()`, so every
locale directory the project ships needs the key. The starter ships `lang/en` only —
add the file to any other locale you create:

```
lang/en/letter_templates.php
```

```php
\App\Modules\Notification\Enums\LetterEnum::ORDER_SHIPPED->value => [
    'subject' => 'Order shipped',
    'body'    => '<p>Hello {{ $user_name }}, your order is on its way.</p>',
],
```

---

## Step 3: Run seeder (optional)

Seeder will:

* create missing templates
* will NOT overwrite existing ones

```bash
php artisan db:seed --class=LetterTemplateSeeder
```

---

# 4. Sending emails

Emails are sent through `Notification\Services\LetterTemplateService`. Inject it —
the facade `LetterTemplateFacade` stays for blade and one-off calls, but application
code should take the service in its constructor:

```php
public function __construct(
    private readonly LetterTemplateService $letters,
) {}

$this->letters->send(
    new SendLetterTemplateData(
        key: LetterEnum::FEEDBACK,
        replace: [
            'user_name' => $request->input('name'),
            'message' => $request->input('message'),
        ],
        attachments: [
            new LetterAttachmentData(
                path: '', // full path to file
            ),
        ],
        source: NotificationSourceEnum::CONSOLE,
    )
);
```

---

# 5. Replace system

Inside templates you can use variables:

```
{{ user_name }}
{{ $user_name }}
```

Both forms work — `LetterTemplateService::replace()` matches
`/{{\s*\$?key\s*}}/` — and the templates shipped in `lang/en/letter_templates.php`
use the `$`-prefixed one. Keys come from the `replace` array passed to the facade.

---

# 6. Attachments

Attachments are stored in:

```
storage/app/public/log_letters/{id}/attachments/{k}/
```

Stored format in DB:

```json
[
  "log_letters/15/attachments/0/file_name.pdf"
]
```

Attachments are automatically:

* copied into storage
* linked to log record
* attached to email

---

# 7. Logging system (log_letters)

Every email is automatically logged.

Stored data:

* recipient email
* subject
* template key
* body (after replace)
* full HTML content
* sender info
* status (sent / failed / queued)
* error message (if failed)
* attachments
* source (admin / system)
* timestamps

---


# 8. Status handling

Statuses:

* queued → email is in queue
* sent → successfully sent
* failed → error occurred

---

# 9. Source tracking

Each email records where it was sent from — `NotificationSourceEnum`
(`app/Modules/Notification/Enums/NotificationSourceEnum.php`), exactly three cases:

| Case | Value | Meaning |
|---|---|---|
| `CONSOLE` | `console` | an artisan command or a queued job |
| `ADMIN` | `admin` | triggered from the Filament admin panel |
| `SYSTEM` | `system` | triggered by the app itself, on a user action |

The value is passed per send (`source:` in `SendLetterTemplateData`) and stored on the
`log_letters` row.

---

[Back to main readme](../../README.md)
