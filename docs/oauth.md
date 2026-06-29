# OAuth / Social Login

Implemented via [Laravel Socialite](https://laravel.com/docs/socialite). Supports Google, Facebook, Telegram.

---

## Config

All provider credentials live in `config/social.php`. On boot, `AppServiceProvider` merges them into `config('services.*)` so Socialite can read them.

```php
// config/social.php
'providers' => ['google', 'facebook', 'telegram'],

'google' => [
    'client_id'     => env('GOOGLE_CLIENT_ID'),
    'client_secret' => env('GOOGLE_CLIENT_SECRET'),
    'redirect'      => env('GOOGLE_REDIRECT_URI'),
],
// same structure for facebook, telegram
```

Required `.env` keys:

```env
GOOGLE_CLIENT_ID=
GOOGLE_CLIENT_SECRET=
GOOGLE_REDIRECT_URI="${APP_URL}/auth/social/google/callback"

FACEBOOK_CLIENT_ID=
FACEBOOK_CLIENT_SECRET=
FACEBOOK_REDIRECT_URI="${APP_URL}/auth/social/facebook/callback"

TELEGRAM_BOT_ID=
TELEGRAM_BOT_TOKEN=
TELEGRAM_REDIRECT_URI="${APP_URL}/auth/social/telegram/callback"
```

---

## Database

Table `social_profiles` links a user to a social account:

| column | type | notes |
|---|---|---|
| `user_id` | FK | cascades on delete |
| `provider` | string | google / facebook / telegram |
| `provider_id` | string | unique per provider |
| `token` | string | access token |
| `refresh_token` | string | nullable |
| `token_expires_at` | timestamp | nullable |
| `avatar` | string | nullable |

Model: `App\Modules\Core\Models\SocialProfile`  
Relation on User: `User::socialProfiles()` → `hasMany(SocialProfile::class)`

---

## Routes

```
GET  /auth/social/{provider}           → redirect to provider  (social.redirect)
GET  /auth/social/{provider}/callback  → handle response        (social.callback)
POST /profile/social/{provider}/disconnect → disconnect account (profile.social.disconnect)
```

`{provider}` must be one of `config('social.providers')`, otherwise 404.

---

## Login / Register Flow

Handled by `OAuthController::callback()`:

1. Gets user from Socialite
2. If user is **already logged in** → connects the social profile to their account (connect mode)
3. If user is **a guest**:
   - Looks up existing `SocialProfile` by `provider` + `provider_id`
   - If found → logs in as that user (updates token)
   - If not found → looks up `User` by email (auto-merge if email matches)
   - If no email match → creates new user via `RegisterAction` (email and password nullable)
   - Attaches `SocialProfile` to the user
   - Logs in

Key actions:

| Action | Responsibility |
|---|---|
| `FindOrCreateSocialUserAction` | Guest login — find or create user + social profile |
| `ConnectSocialProfileAction` | Attach social profile to existing logged-in user |
| `DisconnectSocialProfileAction` | Detach social profile; blocks if it's the only auth method |

---

## Disconnect Guard

`DisconnectSocialProfileAction` throws `ValidationException` if all of these are true:

- user has no `password`
- user has no `email`
- no other social profiles exist

This prevents locking a user out of their account.

---

## User Model Changes

`email` and `password` are nullable (OAuth users may have neither).  
`#[Fillable]` on `User` includes `ip`, `email_verified_at`.

`RegisterAction` always saves `Request::ip()` on create.  
`LoginAction` updates `ip` on every successful password login.

---

## Admin Panel

`/admin/social-profiles` — lists all social profiles, filterable by provider.  
Located under the **Users** navigation group.

---

## Profile Page

`/profile/edit` shows a **Social Accounts** section.  
Each provider shows either a **Connect** button (redirects to OAuth) or a **Disconnect** button (POST form).

---

## Adding a New Provider

1. Install the Socialite community provider if needed (`socialiteproviders/X`)
2. Add credentials block to `config/social.php` and add the provider name to `providers` array
3. Add `.env` keys
4. Register the event listener in `AppServiceProvider::mergeSocialConfig()` (only needed for community providers, not official Socialite drivers)
5. Add translation key to `lang/en/dictionary.php` under `social`
