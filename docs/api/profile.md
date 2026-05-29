# Profile Module

## Overview

Manages user profile data and account settings.

All endpoints require authentication.

---

# Headers

```http
Authorization: Bearer YOUR_TOKEN
```

---

# Endpoints

## Update Profile

### Request

```http
POST /api/profile/update-user-data
```

### Body

```json
{
    "name":"Admin",
    "email":"admin@example.com"
}
```

### Success Response

```json
{
  "success": true,
  "message": "Profile updated",
  "data": {
    "id": 1,
    "name": "John Doe",
    "email": "john@example.com"
  }
}
```

---

## Update Password

### Request

```http
POST /api/profile/update-user-password
```

### Body

```json
{
    "current_password": "password",
    "password": "new-password",
    "password_confirmation": "new-password"
}
```

### Success Response

```json
{
  "success": true,
  "message": "Password updated",
  "data": null
}
```

---

## Delete Account

### Request

```http
POST /api/profile/delete-user-account
```

### Success Response

```json
{
  "success": true,
  "message": "Account deleted",
  "data": null
}
```

---

[Back to main readme](../api/README.md)
