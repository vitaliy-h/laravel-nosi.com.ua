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
* `letter_templated` → stores all templates in DB
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

```php
enum LetterEnum: string
{
    case ORDER_CREATED = 'order_created';
}
```

---

## Step 2: Add translations

```
resources/lang/en/letters.php
resources/lang/uk/letters.php
```

```php
\App\Modules\Notification\Enums\LetterEnum::ORDER_CREATED->value => [
    'subject' => 'Order created',
    'body' => 'Hello {{ user_name }}',
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

Emails are sent using facade:

```php
LetterTemplateFacade::send(
    new SendLetterTemplateData(
        key: LetterEnum::FEEDBACK,
        replace: [
            'user_name' => $request->input('name'),
            'message' => $request->input('message'),
        ],
        attachments: [
            new LetterAttachmentData(
                path: '', // full path to file
        ],
        source: LogLetterSourceEnum::CONSOLE
    )
);
```

---

# 5. Replace system

Inside templates you can use variables:

```
{{ user_name }}
{{ order_id }}
```

They will be replaced automatically using replace array.

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

Each email has a source:

* admin
* system
* api
* custom values

---

[Back to main readme](./README.md)
