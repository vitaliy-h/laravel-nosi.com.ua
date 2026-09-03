# Аудит документації та інструкцій для Claude

Дата перевірки: 2026-09-03. Список розходжень між `docs/`, `.claude/`, `CLAUDE.md` і реальним кодом.
Кожен пункт — одна конкретна правка. Відмічати `[x]` по мірі виконання.

---

## 1. Баги в коді, які документація приховує

- [ ] **Планувальник викликає неіснуючу команду.** `bootstrap/app.php:39` — `basket:clear-abandoned`,
  сигнатура команди в `app/Modules/ShopCart/Console/Commands/ClearAbandonedCartsCommand.php` —
  `cart:clear-abandoned`. Замінити на `cart:clear-abandoned`, у коментарі на рядку 31 слово `basket` → `cart`.
  Додати Feature-тест, що розклад містить саме цю команду.

---

## 2. Скіли `.claude/skills/`

- [ ] **Шлях до PageModule.** Замінити `app/Modules/Core/Enums/PageModule.php` →
  `app/Modules/Page/Enums/PageModule.php` у:
  - `.claude/skills/make-api/SKILL.md:30`
  - `.claude/skills/make-model/SKILL.md:17` і `:39`
  - `.claude/skills/make-filament-resource/SKILL.md:24`
- [ ] **make-api: прибрати Repository.** Step 7 (шаблон Action інжектить `{Module}Repository`) і розділ
  "Reuse checklist" (`ShopRepository`) переписати під `Queries/` — читання даних через `Query`-клас,
  Action викликає Query, а не репозиторій. Репозиторіїв у проєкті немає.
- [ ] **make-filament-resource: приклад `Basket`.** У Step 3 і далі замінити на реальний приклад
  `Shop/Cart` → `app/Filament/Resources/Shop/Cart/CartResource.php`.
- [ ] **make-model: новий модуль не завжди PageModule.** Уточнити, що модуль без публічної CMS-сторінки
  (`ShopOrder`, `Subscriber`, `Sitemap`) не отримує case в `PageModule`, а роути монтуються з
  kebab-префіксом папки (`ModuleServiceProvider::registerUnmappedModuleRoute()`). Додати крок:
  чи має модуль вимикатись через `config/modules.php`.
- [ ] **plan-task.** Прибрати згадку агента `cavecrew-investigator` (у проєкті його немає, лишити `Explore`).
  "PHPUnit 12" → "PHPUnit 13".

---

## 3. `.claude/testing.md`

- [ ] **Середовище запуску.** Розділ "Run tests (Sail)" переписати: тести запускаються `php artisan test`
  на хості (PHP 8.4, OpenServer) або через `./vendor/bin/sail artisan test`, якщо проєкт у Sail.
  Прибрати твердження, що локальний PHP 8.3 впаде (composer вимагає `^8.3`).
- [ ] **Версія PHPUnit.** "PHPUnit 12" → "PHPUnit 13" (composer: `phpunit/phpunit ^13.0`).
- [ ] **Concerns.** Додати третій трейт `InteractsWithModules` (`withModules([...])`) і метод
  `TestCase::skipUnlessModules()` / автоматичний пропуск тестів вимкненого модуля по namespace.
- [ ] **Таблиця "Test structure".** `Feature/Auth/` → `Feature/UserAuth/`. Додати рядки для
  `Feature/Console`, `Feature/Feedback`, `Feature/Index`, `Feature/News`, `Feature/ShopCart`,
  `Feature/ShopOrder`, `Feature/Sitemap`, `Feature/Subscriber`, `Unit/Core`, `Unit/Filament/Dashboard`,
  `Unit/**/ViewModels`. У рядку `Unit/` прибрати `ShopRepository`, `CreateOrderAction` віднести до `ShopOrder`.
- [ ] **Filament-тести.** Замінити "Each Filament resource gets its own subdirectory" на реальне правило:
  підпапка = модуль (`Filament/Shop/`, `Filament/Logs/`, `Filament/Geo/`), клас `{Resource}ResourceTest`.
- [ ] **Приклад Unit: Action.** Namespace `App\Modules\Shop\Actions\CreateOrderAction` і
  `App\Modules\Shop\Dtos\CreateOrderData` → `App\Modules\ShopOrder\...`, namespace тесту `Tests\Unit\ShopOrder`.
- [ ] **Таблиця фабрик.** Додати `AuthLogFactory`, `CartFactory`, `CartProductFactory`, `NewsFactory`,
  `ProductReviewFactory`, `PromocodeFactory`, `SocialProfileFactory`, `SubscriberFactory` з їх станами.
- [ ] **Windows fallback.** `docker compose exec laravel.test php artisan test` лишити лише як варіант для Sail.

---

## 4. `.claude/frontend.md` і `.claude/backend.md`

- [ ] **frontend.md, правило 3 vs код.** Або перенести `resources/views/modules/user_auth/partials/forms/*`
  у `partials/` пласко (`form_login.blade.php`, …) і оновити `@include`, або дозволити підпапки всередині
  `partials/` у правилі. Обрати одне.
- [ ] **backend.md.** Додати абзац про `Queries/` (читання даних: `Find*Query`, `Search*Query`, DTO для
  пошуку) — зараз шар описаний лише в actions.md як "належить Query-класу", без правил розміщення.

---

## 5. `docs/starter/`

- [ ] **routing.md, §4 приклад контролера.** Переписати на `$this->render('modules.news.index', $page->toViewData())`
  з ViewModel; прибрати `view()`, `compact()`, `view()->share([...meta...])`.
- [ ] **routing.md, §2 unmapped-модулі.** Описати реальну логіку: kebab-префікс з назви папки,
  `MODULE_PREFIX_OVERRIDES` (`ShopOrder` → `/orders`), без префікса лише `Core` і `Sitemap`
  (`ROOT_LEVEL_MODULES`). Прибрати `Notification` зі списку (роутів немає), додати `Subscriber` (`/subscriber`).
- [ ] **architecture.md, "Routing".** Прибрати "Routes remain in routes/web.php, routes/api.php for POST and API" —
  обидва файли порожні, всі роути в модулях (`Http/Routes/{web,api}.php`).
- [ ] **architecture.md, "Module Structure".** Прибрати `Repositories` і `Policies` зі списку типового вмісту,
  додати `Queries`, `ViewModels`, `Observers`, `Services`.
- [ ] **sitemap.md.** Контролер і роути: `app/Modules/Sitemap/Http/Controllers/SitemapController.php`,
  `app/Modules/Sitemap/Http/Routes/web.php` (а не `Core`). Виправити таблицю "Files" і вступ.
- [ ] **uuid.md.** Розділ "Fetching by UUID": замість `app/Modules/Shop/Repository/OrderRepository.php`
  показати `App\Modules\ShopOrder\Queries\FindOrderByUuidQuery`. Роут `/order/{uuid}` у Shop →
  `/orders/{uuid}` у `ShopOrder`. Прибрати "модулі без PageModule мають шарити layout самі" —
  є `PageContextSharer::shareWithoutPage()`.
- [ ] **cart.md.** `ForkCartAction` → `DuplicateCartAction`; таблиця `carts` → `shop_carts`.
  Прибрати "checkout — статичний мокап" і "CreateOrderAction у Shop не підключений": описати
  `ShopOrder\Actions\PlaceOrderFromCartAction`, форму `order.create` у `shop_cart/index.blade.php`.
  Додати розділ про промокоди: `PromocodeController`, роути `promocode.apply` / `promocode.remove`,
  `ValidatePromocodeAction`, `ApplyPromocodeToCartAction`, `RemovePromocodeFromCartAction`,
  `CalculateDiscountAction`, `IncrementPromocodeUsageAction`. Виправити назву команди на `cart:clear-abandoned`
  після фіксу з п. 1.
- [ ] **testing.md.** Шляхи прикладів `tests/Unit/Shop/OrderDtoTest.php`, `tests/Unit/Shop/CreateOrderActionTest.php`
  → `tests/Unit/ShopOrder/...`. `phpunit/phpunit ^12.5` → `^13.0`.
- [ ] **letters.md.** Прибрати `lang/uk/letter_templates.php` (папки `lang/uk` немає). Розділ "Source tracking":
  значення `NotificationSourceEnum` — `console`, `admin`, `system` (без `api`, "custom values").
- [ ] **logs.md.** Розділ "Sources": перелічити всі case `LogSourceEnum` — `admin_panel`, `api`, `console`,
  `system`, `client`.
- [ ] **claude.md.** У дереві `.claude/skills/` додати `plan-task/`. Опис `CLAUDE.md` замінити на
  "вхідна точка з посиланнями на role-guides і базові правила". Прибрати "make-filament-resource → тести"
  (скіл тестів не генерує). "PHPUnit 12, Sail" → "PHPUnit 13".
- [ ] **todo.md.** Викреслити або перенести в `docs/project/` пункти, які вже в роботі
  (`shop_product_items`, "Dynamic product attributes").

---

## 6. `docs/README.md` та кореневий `README.md`

- [ ] **Версія PHP.** "PHP >= 8.4 (minimum)" узгодити з composer (`^8.3`): або підняти вимогу в composer
  до `^8.4`, або виправити текст.
- [ ] **WSL2, крок 7.** `copy .env.example .env` → `cp .env.example .env` (bash).
- [ ] **Кореневий README.** Додати посилання на `docs/filament/dashboard.md` у блок Filament.

---

## 7. `docs/api/`

- [ ] **README.md.** Прибрати згадку Postman-колекції або додати файл `docs/api/*.postman_collection.json`.
- [ ] **auth.md.** Додати `POST /api/auth/logout` (`auth:sanctum`).
- [ ] **shop.md, List Products.** Звірити параметри `brand`, `price_from`, `price_to`, `search` з
  `PrepareShopFilterAction` — `ShopProductsRequest` валідує лише `category_id` і `sort`. Виправити назви
  або додати правила валідації в request.

---

## 8. `docs/project/`

- [ ] **README.md.** Замінити заглушку на індекс: посилання на `product_attributes.md`, `product_items.md`,
  `docs_audit.md`, `todo.md`.
- [ ] **product_items.md.** `app/Modules/ShopOrders/` → `app/Modules/ShopOrder/` (усі входження, рядки 52, 73,
  421, 438, 465 та інші). `tests/Unit/Dashboard/` → `tests/Unit/Filament/Dashboard/`.
- [ ] **todo.md.** Файл порожній — заповнити або видалити.
- [ ] **Git.** Додати `product_attributes.md` і `product_items.md` у git.

---

## 9. Структура коду vs власні конвенції

- [ ] **Тести в корені suite.** Перенести в модульні папки:
  - `tests/Feature/HasImageConversionTest.php` → `tests/Feature/Core/`
  - `tests/Feature/ImagePreviewControllerTest.php` → `tests/Feature/Core/`
  - `tests/Feature/ImagesClearCommandTest.php` → `tests/Feature/Core/`
  - `tests/Unit/ImageConversionServiceTest.php` → `tests/Unit/Core/`
  - `tests/Unit/ImageResizerTest.php` → `tests/Unit/Core/`

  Оновити namespace класів.
- [ ] **composer.json.** Прибрати `pestphp/pest-plugin` з `config.allow-plugins` — Pest у проєкті заборонений.

---

## Порядок

1. Пункт 1 (баг у планувальнику) — окремим комітом.
2. Пункти 2–4 — інструкції для Claude, бо саме вони впливають на генерований код.
3. Пункти 5–8 — документація.
4. Пункт 9 — переміщення тестів, після нього прогнати `php artisan test`.
