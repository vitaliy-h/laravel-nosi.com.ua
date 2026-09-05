# Impersonation — signing in as another user

An admin can open the site as a given user to reproduce what that user sees. The feature
lives in `UserAuth` and is deliberately small: two routes, three Actions, one banner.

---

## How it works

1. **Admin panel.** `UserResource`'s row action *Impersonate* calls
   `GenerateImpersonationTokenAction`, which returns a **signed URL** to the
   `impersonate` route, valid for **one hour**, and opens it in a new tab. The action is
   hidden on the admin's own row and disappears entirely when `user_auth` is off.
2. **`GET /auth/impersonate/{user}`** (`impersonate`, middleware `signed`) →
   `ImpersonateAction::handle(Session $session, User $target)`: remembers the current
   `Auth::id()`, logs in as the target user, regenerates the session and stores the
   original id under `ImpersonateAction::SESSION_KEY` (`impersonating_admin_id`).
   Then redirects to `/`.
3. **The banner.** `resources/views/partials/navbar.blade.php` renders a warning strip on
   every page while `impersonating_admin_id` is in the session, showing whose account is
   being viewed and a *Return* button.
4. **`POST /auth/impersonation/leave`** (`impersonate.leave`, middleware `auth`) →
   `LeaveImpersonationAction::handle(Session $session)`: pulls the stored id, logs the admin back in and regenerates
   the session, then redirects to the admin panel. If that admin no longer exists, it logs
   out and invalidates the session instead of leaving a half-restored state.

Leaving is always possible from the banner; the session key is what drives it, so closing
the tab and coming back still shows the banner until *Return* is pressed or the session
expires.

---

## Who can do it

- The button is only reachable inside the admin panel, and panel access itself requires
  `super_admin` or the `admin` permission (`User::canAccessPanel()`).
- The route itself is protected by **`signed` only** — not by `auth`, and not by a role
  check. The gate is the signature: the link cannot be forged or edited, and it expires
  after an hour.

⚠️ That means the generated link **is** the credential for that hour. Anyone who obtains it
(a shared screen, a copied URL, a proxy log) becomes that user. Treat an impersonation URL
like a password reset link: do not paste it into tickets or chats. If a project needs a
stricter policy, add `auth` plus a role check to the route — nothing else in the flow
depends on the route being open.

Impersonation is also not logged as an action of its own: rows the impersonated user
touches are attributed to that user in the journals (`docs/starter/logs.md`).

---

## Tests

`tests/Feature/UserAuth/ImpersonationTest.php` covers the whole flow: unsigned and tampered
URLs return 403, a valid link logs in as the target and stores the admin id, leaving
restores the admin, and a missing original admin results in a clean logout.

---

[Back to main readme](../../README.md)
