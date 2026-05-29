# Dynamic Routing Architecture (CMS Modules)

## Overview

This project uses a **database-driven routing system for all GET requests**, allowing URLs to be fully dynamic and editable from the database.

The routing layer acts as a lightweight CMS engine on top of Laravel, where pages are stored in the `pages` table and mapped to application modules.

---

## 1. Routing Strategy

### GET Requests (Dynamic)

All GET requests are dynamically resolved from the database (`pages` table).

* URLs can be changed directly from the database without modifying code.
* Each page is linked to a module via `PageModule` enum.
* The system resolves the correct controller based on the module.

### POST Requests (Static)

All POST (and other non-GET) requests use standard Laravel routing.

* Fully standard Laravel controllers and routes
* Standard middleware stack
* Used for forms, API requests, and secure operations

This separation ensures:

* Flexibility for public pages
* Security and predictability for state-changing operations

---

## 2. Core Routing Entry Point

All dynamic routing is handled in:

```
app/Modules/Core/routes/web.php
```

This route forwards all requests to:

```
App\Modules\Core\Http\Controllers\ModuleRouteController
```

## 2.1 Module Static Routes (POST / Standard Laravel Routes)

In addition to dynamic GET routing, each module can define its own **static Laravel routes** (POST, PUT, DELETE, etc.).

These routes are placed inside the module directory:

```
app/Modules/{ModuleName}/routes/web.php
```

### Examples:

```
app/Modules/Auth/routes/web.php
app/Modules/Feedback/routes/web.php
```


### Purpose

These routes are used for:

- POST requests
- Form submissions
- API-like actions
- Secure operations requiring standard Laravel middleware

### Loading Behavior

All module route files are **automatically loaded by the system** if they exist in the expected path:

```
app/Modules/{ModuleName}/routes/web.php
```

---

## 3. ModuleRouteController Responsibilities

The `ModuleRouteController` is the central router for all dynamic pages.

It is responsible for:

* Resolving the page from the database (`pages` table)
* Determining the module via `PageModule`
* Delegating execution to the correct module controller
* Passing URL segments for nested routing

---

## 4. Modules System

Each page in the `pages` table is assigned a module.

Modules are defined in:

```
app/Modules/Core/Enums/PageModule.php
```

---

## 5. Controller Resolution

The system automatically resolves controllers using the module value:

```
\App\Modules\{ModuleName}\Http\Controllers\Web\IndexController@index
```

Example:

* `news` → `App\Modules\News\Http\Controllers\Web\IndexController@index`
* `index` → `App\Modules\Index\Http\Controllers\Web\IndexController@index`
* `text_pages` → `App\Modules\TextPage\Http\Controllers\Web\IndexController@index`

---

## 6. Request Handling

Each module controller receives:

```php
Request $request,
array $moduleSegments = []
```

### Purpose of `$moduleSegments`

This array contains all URL segments after the module slug.

Example:

```
/news/test-news-9
```

Becomes:

```php
$moduleSegments = ['test-news-9'];
```

This enables nested routing inside modules.

---

## 7. Example: News Module

### Controller Example

```php
public function index(Request $request, array $segments = [])
{
    if (!empty($segments)) {
        $slug = $segments[0];
        return $this->show($slug, $request);
    }

    $news = $this->repository->all();

    return view('pages.news.index', compact('news'));
}

public function show($slug, Request $request)
{
    $newsItem = $this->repository->getBySlug($slug);

    view()->share([
        'meta_title' => $newsItem->meta_title,
        'meta_description' => $newsItem->meta_description,
    ]);

    return view('pages.news.show', compact('newsItem'));
}
```

---

## 8. Module Controller Interface

All module controllers must implement:

```
App\Modules\Core\Interfaces\ModuleIndexControllerInterface
```

### Responsibilities

* Define `index()` method structure
* Define `accessCheck()` method

---

## 9. Access Control System

Each module supports an `accessCheck()` method.

### Purpose

Acts as a **GET-request middleware layer**.

It is executed before rendering module content and can be used for:

* Authentication checks
* Role-based access control
* Subscription validation

### Example Use Case

```php
public function accessCheck(): \Illuminate\Http\Response|\Illuminate\Http\RedirectResponse|null
    {
        if (auth()->check()) {
            return redirect()->to(module_url(PageModule::INDEX));
        }
        return null;
    }
```

If `accessCheck()` Response or RedirectResponse than response will be returned to user. For `null` response script will continue execution.

---

## 10. POST Requests Security Model

POST requests are handled using standard Laravel routing.

* Standard `web.php` routes
* Laravel middleware stack (`auth`, `csrf`, etc.)
* No dynamic resolution

This ensures:

* Security for state-changing actions
* Predictable request handling

---

## 11. URL Generation

A helper function is used for generating module URLs:

```
module_url(PageModule $module, array|string $params = [], array $query = []): string
```

### Parameters

* **$module** → Target module (PageModule enum)
* **$params** → Route parameters (slug segments)
* **$query** → Query string parameters

---

## 12. URL Generation Examples

### Auth Module

```php
module_url(PageModule::AUTH, [], ['login' => null]);
```

Result:

```
http://localhost/auth-test?login
```

---

### News Module

```php
module_url(PageModule::NEWS, [$item->slug]);
```

Result:

```
http://localhost/news/{slug}
```

---

### Shop Module

```php
module_url(PageModule::SHOP, [$category->slug, $product->slug]);
```

Result:

```
http://localhost/shop/{category}/{product}
```

---

[Back to main readme](./README.md)
