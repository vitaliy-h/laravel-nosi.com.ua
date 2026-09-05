# API Documentation

## Overview

This project provides a modular Laravel-based API with a clean architecture approach.

### Core Principles

- Actions contain business logic
- Controllers are thin (HTTP layer only)
- DTOs are used for structured input
- Resources are used for API responses
- Web and API share the same Actions (no duplication)

---

## Authentication

We use Laravel Sanctum for API authentication.

### Token Auth

```
Authorization: Bearer <token>
```

---

## Response Format

A **successful** response carries `data` (`Core\Traits\Api\ApiResponse::success()`):

```json
{
    "success": true,
    "message": "Optional message",
    "data": {}
}
```

A **failed** response carries `errors` instead of `data` — same three keys, never both
(`ApiResponse::error()`, and the API exception renderer in `bootstrap/app.php`):

```json
{
    "success": false,
    "message": "Validation failed",
    "errors": {
        "email": ["Invalid credentials"]
    }
}
```

`errors` is `null` for failures that carry no field-level detail (401, 403, 404, 429):

```json
{
    "success": false,
    "message": "Unauthenticated.",
    "errors": null
}
```

---

## Error Handling

Every `api/*` request goes through the renderer in `bootstrap/app.php`, so an exception
never escapes as HTML:

| Status | When | `errors` |
|---|---|---|
| 422 | `ValidationException` | field → array of messages |
| 401 | `AuthenticationException` (missing/invalid token) | `null` |
| 403 | `AccessDeniedHttpException` | `null` |
| 404 | `ModelNotFoundException`, `NotFoundHttpException` | `null` |
| 429 | `ThrottleRequestsException` | `null` |
| 500 | anything else | `null` in production; exception class and a 5-frame trace when `APP_DEBUG=true` |

Unhandled exceptions are passed to `report()` before the 500 response is returned, so
they still reach the log channels.

---

## Module route descriptions

* [Auth](./auth.md)
* [Profile](./profile.md)
* [Shop](./shop.md)
* [Translation](./translation.md)

---

[Back to main readme](../../README.md)
