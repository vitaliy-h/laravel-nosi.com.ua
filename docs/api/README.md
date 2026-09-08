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

Every `api/*` response has the **same four keys** — a client parses one shape, not two.
`Core\Support\ApiEnvelope` is the only place that builds it; controllers reach it through
`Core\Traits\Api\ApiResponse` (`success()` / `error()`), and failures raised as exceptions
through the renderer in `bootstrap/app.php`.

A **successful** response fills `data` and leaves `errors` null:

```json
{
    "success": true,
    "message": "Optional message",
    "data": {},
    "errors": null
}
```

A **failed** response is the mirror image — `data` is null, `errors` carries the detail:

```json
{
    "success": false,
    "message": "Validation failed",
    "data": null,
    "errors": {
        "email": ["Invalid credentials"]
    }
}
```

`errors` is `null` too for failures that carry no field-level detail (401, 403, 404, 429),
so the body still has all four keys:

```json
{
    "success": false,
    "message": "Unauthenticated.",
    "data": null,
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
