# SMS Template System (log_sms)

This module provides a centralized system for:

* SMS templates
* SMS sending via configurable provider
* full SMS logging (audit trail)

---

# 1. Overview

SMS system is based on:

* `SmsEnum` → defines SMS template types
* translations → default subject + body per language for initial data
* `sms_templates` → stores all templates in DB
* `log_sms` → stores all sent SMS (audit log)
* `SmsTemplateFacade` → used for sending SMS
* `SmsProviderEnum` → defines available providers
* `SmsProviderInterface` → contract for all providers
* Provider resolved from config at runtime

---

# 2. How SMS templates work

SMS templates are stored in the **database (`sms_templates`)** and can be fully edited via the admin panel.

Each template is identified by an enum value:

```php
SmsEnum::VERIFICATION
```

### Important clarification

- ❌ Translation files are NOT the source of truth for templates
- ✅ Translation files are used ONLY for import/seed initial data
- ✅ After import, templates live in DB and can be edited in admin panel

---

For each template we store in DB:

- subject
- body (text with `{{ variable }}` placeholders)
- template key (enum)

---

# 3. Adding a new SMS template

## Step 1: Add enum value

```php
// app/Modules/Notification/Enums/SmsEnum.php
enum SmsEnum: string
{
    case VERIFICATION = 'verification';
    case ORDER_SHIPPED = 'order_shipped'; // new
}
```

---

## Step 2: Add translations

```
lang/en/sms_templates.php
```

```php
\App\Modules\Notification\Enums\SmsEnum::ORDER_SHIPPED->value => [
    'subject' => 'Order shipped',
    'body' => 'Your order #{{ order_id }} has been shipped.',
],
```

---

## Step 3: Run seeder (optional)

Seeder will:

* create missing templates
* will NOT overwrite existing ones

```bash
php artisan db:seed --class=SmsTemplateSeeder
```

---

# 4. Sending SMS

SMS are sent using the facade:

```php
use App\Modules\Notification\Facades\SmsTemplateFacade;
use App\Modules\Notification\Dtos\SendSmsData;
use App\Modules\Notification\Enums\SmsEnum;

SmsTemplateFacade::send(
    new SendSmsData(
        key: SmsEnum::VERIFICATION,
        to: '+480991234567',
        replace: [
            'code' => '123456',
        ],
    )
);
```

---

# 5. Replace system

Inside templates you can use variables:

```
{{ code }}
{{ order_id }}
{{ user_name }}
```

They will be replaced automatically using the `replace` array.

---

# 6. Provider system

The active provider is configured via `config/sms.php`:

```php
// config/sms.php
return [
    'provider' => env('SMS_PROVIDER', 'log'),
];
```

Set in `.env`:

```
SMS_PROVIDER=log
```

Available providers:

| Provider         | Value | Description                            |
|------------------|-------|----------------------------------------|
| `LogSmsProvider` | `log` | Saves SMS to `log_sms` table (default) |

---

## Adding a new provider

### Step 1: Add enum value

```php
// app/Modules/Notification/Enums/SmsProviderEnum.php
enum SmsProviderEnum: string
{
    case LOG = 'log';
    case TWILIO = 'twilio'; // new
}
```

### Step 2: Create provider class

```php
// app/Modules/Notification/SmsProviders/TwilioSmsProvider.php
namespace App\Modules\Notification\SmsProviders;

use App\Modules\Notification\Contracts\SmsProviderInterface;
use App\Modules\Notification\Dtos\SendSmsData;

class TwilioSmsProvider implements SmsProviderInterface
{
    public function send(string $to, string $body): void
    {
        // integrate with Twilio SDK
    }
}
```

### Step 3: Register in SmsTemplateService

```php
// app/Modules/Notification/Services/SmsTemplateService.php
private function resolveProvider(): SmsProviderInterface
{
    return match (SmsProviderEnum::from($key)) {
        SmsProviderEnum::LOG    => new LogSmsProvider(),
        SmsProviderEnum::TWILIO => new TwilioSmsProvider(),
    };
}
```

### Step 4: Set in .env

```
SMS_PROVIDER=twilio
```

---

# 7. Logging system (log_sms)

Every SMS is automatically logged by `LogSmsProvider`.

Stored data:

* recipient phone number
* subject
* template key
* body (after replace)
* provider used
* status (sent / failed / queued)
* error message (if failed)
* timestamps

---

# 8. Status handling

Statuses (`LogSmsStatusEnum`):

* `queued` → SMS is pending
* `sent` → successfully sent
* `failed` → error occurred

---

[Back to main readme](./README.md)
