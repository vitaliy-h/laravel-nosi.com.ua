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
  "password_confirmation": "password"
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

[Back to main readme](../api/README.md)
