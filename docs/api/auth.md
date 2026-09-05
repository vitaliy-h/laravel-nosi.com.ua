# Auth Module

## Overview

Handles user authentication for both Web and API using shared Actions.

---

# Endpoints

## Register

### Request

```http
POST /api/auth/register
```

### Body

```json
{
  "name": "John Doe",
  "email": "john@example.com",
  "password": "password",
  "password_confirmation": "password",
  "terms": true
}
```

### Success Response

```json
{
  "success": true,
  "message": "Registration successful",
  "data": {
    "token": "1|asdasd123",
    "user": {
      "id": 1,
      "name": "John Doe",
      "email": "john@example.com"
    }
  }
}
```

---

## Login

### Request

```http
POST /api/auth/login
```

### Body

```json
{
  "email": "john@example.com",
  "password": "password"
}
```

### Success Response

```json
{
  "success": true,
  "message": "Login successful",
  "data": {
    "token": "1|asdasd123",
    "user": {
      "id": 1,
      "name": "John Doe",
      "email": "john@example.com"
    }
  }
}
```

### Error Response

```json
{
  "success": false,
  "message": "Validation failed",
  "errors": {
    "email": [
      "Invalid credentials"
    ]
  }
}
```

---

## Logout

Revokes **only the token used for this request** (`currentAccessToken()->delete()`), so a
user's other devices stay logged in.

### Request

```http
POST /api/auth/logout
Authorization: Bearer 1|asdasd123
```

Requires the `auth:sanctum` middleware — no body.

### Success Response

```json
{
  "success": true,
  "message": "Logged out."
}
```

### Error Response

Without a valid token the request never reaches the controller — the API exception
renderer in `bootstrap/app.php` turns the `AuthenticationException` into `401`:

```json
{
  "success": false,
  "message": "Unauthenticated.",
  "errors": null
}
```

Covered by `tests/Feature/UserAuth/AuthApiTest.php`
(`test_logout_with_token_succeeds`, `test_logout_without_token_is_unauthorized`).

---

[Back to main readme](../api/README.md)
