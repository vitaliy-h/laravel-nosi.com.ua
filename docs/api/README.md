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

All API responses follow this structure:

```json
{
    "success": true,
    "message": "Optional message",
    "data": {}
}
```

---

## Error Handling

- Validation errors → 422
- Unauthorized → 401
- Forbidden → 403
- Server errors → 500 (never exposed in production)

All errors are logged automatically.

---

## Local development

You will find in the directory `docs/api` postman collection file with all the requests described below.

---

## Module route descriptions

* [Auth](./auth.md)
* [Profile](./profile.md)
* [Shop](./shop.md)
* [Translation](./translation.md)

---

[Back to main readme](../README.md)
