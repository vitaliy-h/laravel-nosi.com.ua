# Translation Module

## Overview

Provides translations stored in database for frontend and mobile applications.

Supports locale-based and group-based loading.

---

# Endpoints

## Get All Translations

### Request

```http
GET /api/translations?locale=en
```

### Success Response

```json
{
    "success": true,
    "message": "OK",
    "data": {
        "index": {
            "hero_title": "Welcome",
            "hero_subtitle": "Starter project on Laravel",
            "first_block_title": "Fast",
            "first_block_subtitle": "Laravel + Filament starter",
            "second_block_title": "Flexible",
            "second_block_subtitle": "Modular architecture"
        },
        "feedback": {
            "name": "Name",
            "email": "Email",
            "message": "Message",
            "saved": "Feedback has been saved."
        },
        "news": {
            "read_more": "Read more"
        },
        "auth": {
            "login": "Sign In",
            "register": "Register",
            "password_recovery": "Password recovery",
            "name": "Name",
            "email": "Email address",
            "password": "Password",
            "new_password": "New password",
            "password_confirmation": "Confirm password",
            "remember_me": "Remember me",
            "invalid_credentials": "Invalid credentials",
            "forgot_password": "Forgot Password?",
            "terms": "I agree to the <a href=\"#\">Terms of Service</a> and <a href=\"#\">Privacy Policy</a>",
            "user_not_found": "User not found",
            "password_reset_email_sent": "Email with password reset link has been sent.",
            "password_reset_success": "Password reset success.",
            "password_reset_error": "Error occurred while resetting password."
        },
        "profile": {
            "my_profile": "My profile",
            "account_settings": "Account Settings",
            "personal_information": "Personal Information",
            "user_data_updated": "Personal Information has been successfully updated.",
            "user_data_update_error": "Error occurred while updating personal data. Please try again."
        },
        "buttons": {
            "send": "Send",
            "save": "Save",
            "cancel": "Cancel",
            "login": "Login",
            "logout": "Sign Out",
            "create_acc": "Create Account",
            "request_password_recovery": "Reset Password",
            "menu": "Reset Password",
            "save_changes": "Save Changes"
        }
    },
    "errors": null
}
```

---

## Get Group Translations

### Request

```http
GET /api/translations/auth?locale=en
```

### Success Response

```json
{
    "success": true,
    "message": "OK",
    "data": {
        "login": "Sign In",
        "register": "Register",
        "password_recovery": "Password recovery",
        "name": "Name",
        "email": "Email address",
        "password": "Password",
        "new_password": "New password",
        "password_confirmation": "Confirm password",
        "remember_me": "Remember me",
        "invalid_credentials": "Invalid credentials",
        "forgot_password": "Forgot Password?",
        "terms": "I agree to the <a href=\"#\">Terms of Service</a> and <a href=\"#\">Privacy Policy</a>",
        "user_not_found": "User not found",
        "password_reset_email_sent": "Email with password reset link has been sent.",
        "password_reset_success": "Password reset success.",
        "password_reset_error": "Error occurred while resetting password."
    },
    "errors": null
}
```

---

# Notes

- Frontend/mobile apps should cache translations
- Locale can be passed via query parameter
- Group loading is recommended for performance

---

[Back to main readme](../api/README.md)
