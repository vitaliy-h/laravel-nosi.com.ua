# Аудит документації проти реального коду

Перший аудит: 2026-09-03. **Другий (цей) аудит: 2026-09-04, задача #50.**

Перевірено: `docs/**` (30 файлів), `.claude/**` (3 role-guide + 4 скіли), `CLAUDE.md`,
кореневий `README.md` — проти `app/` (411 php-файлів), `config/`, `routes/`, `resources/views/`,
`tests/` (117 тест-класів), `composer.json`, `compose.yaml`, `phpunit.xml`.
Перевірка йшла у два боки: «документ → код» (застаріле й невірне) і «код → документ»
(недокументоване). Джерело істини — код і `composer.json`, не документація.

## Легенда

| Тег | Значення |
|---|---|
| `[docs]` | проблема документації — код правильний, опис ні |
| `[code]` | проблема коду — документація описує те, як має бути |
| `[gap]` | функціонал є в коді, документації немає взагалі |

| Пріоритет | Критерій |
|---|---|
| **P1** | вводить в оману так, що ламає роботу: неробочий код, генерація неправильного коду по інструкції, обіцянка, якої код не виконує |
| **P2** | застаріло: неправильний шлях/ім'я класу/версія, відсутній розділ про наявний функціонал |
| **P3** | косметика, неповнота, стиль |

---

## 0. Головне

1. **Планувальник викликав неіснуючу команду** — виправлено окремим комітом (див. §1.1).
2. **Обіцянка «вимкнений модуль можна видалити» не виконується кодом**: `AppServiceProvider`
   жорстко реєструє обсерверів шести модулів (§1.2, §9.A2).
3. **`composer.json` вимагає `php ^8.3`, а реально проєкт не встановиться нижче 8.4.1** (§1.3).
   Попередній аудит пропонував правити документацію — правити треба composer.
4. **Шар `Queries/` (21 клас у 9 модулях) не описаний ніде**, хоча всі читання проходять через нього (§8.1).
5. **Сім модулів не документовані жодним рядком**: Geo, Subscriber, Feedback, Profile, Index,
   PageText, `_debug` (§8.2). Разом з ними — імперсонація і адмінська сторінка DebugTools,
   яка вміє запускати `migrate:fresh` з вебу (§8.3, §8.4).
6. Інструкції `.claude/` посилаються на класи й шари, яких у проєкті немає (Repository, PageModule
   у Core) — вони найнебезпечніші, бо по них генерується код (§3).

---

## 1. Проблеми коду

- [x] **1.1 `[code]` P1 — планувальник викликав неіснуючу команду.** `bootstrap/app.php:39`
  містив `basket:clear-abandoned`, а команда оголошена як `cart:clear-abandoned`
  (`app/Modules/ShopCart/Console/Commands/ClearAbandonedCartsCommand.php:10`, атрибут `#[Signature]`).
  Нічні прибирання покинутих гостьових кошиків не виконувались.
  **Виправлено** комітом `f30c318`: ім'я команди, слово `basket` у коментарі на рядку 31,
  плюс `tests/Feature/Console/ScheduleTest.php` — перевіряє, що кожна запланована команда
  зареєстрована в Artisan.

- [ ] **1.2 `[code]` P1 — вимкнений модуль не можна видалити, хоча документація це обіцяє.**
  `docs/starter/modules.md:124-138` стверджує, що після `'shop' => false` папку модуля можна
  видалити і поза нею на модуль ніхто не посилається (крім двох методів на `User`).
  Реально `app/Providers/AppServiceProvider.php:62-67` жорстко реєструє обсерверів
  `Page`, `Order`, `OrderProduct`, `Cart`, `CartProduct`, `ProductReview` — видалення будь-якої з
  цих папок дає фатальну помилку на бутстрапі, ще до того, як `ModuleGate` встигне щось вимкнути.
  Рішення (одне з двох, це рішення розробника):
  - реєструвати обсерверів у самому модулі (`ModuleServiceProvider` уже обходить папки і знає
    про `ModuleGate`) — тоді обіцянка стає правдою;
  - або переписати `modules.md` §4 і чесно перелічити всі зовнішні посилання.
  Див. також §9.A2.

- [ ] **1.3 `[code]` P2 — `composer.json` бреше про мінімальну версію PHP.**
  `composer.json:9` — `"php": "^8.3"`. Реально `composer install` неможливий нижче **8.4.1**:
  `phpunit/phpunit ^13.0` тягне `php >= 8.4.1`, а весь `sebastian/*` — `>= 8.4`
  (перевірено `composer why-not php 8.3.0`). Sail-рантайм теж 8.4
  (`compose.yaml:5` → `runtimes/8.4`). Підняти `require.php` до `^8.4`.
  ⚠️ Це скасовує пункт попереднього аудиту, який пропонував замість цього виправити
  «PHP >= 8.4» у `docs/README.md` — документація тут якраз права.

- [ ] **1.4 `[code]` P3 — `pestphp/pest-plugin` у `config.allow-plugins`.**
  `composer.json` дозволяє плагін Pest, тоді як `.claude/testing.md:6` забороняє Pest у проєкті.
  Прибрати рядок.

- [ ] **1.5 `[code]` P2 — `Paginator::useBootstrap()` при Tailwind 4.**
  `app/Providers/AppServiceProvider.php:56` вмикає bootstrap-розмітку пагінації, тоді як фронт —
  Tailwind CSS 4 (`.claude/frontend.md:6`, `package.json`). У `resources/views/partials/pagination/`
  лежать власні шаблони. Визначитись: або власні шаблони через `Paginator::defaultView()`,
  або прибрати виклик; зараз стан суперечливий і ніде не описаний.

---

## 2. Статус пунктів попереднього аудиту (2026-09-03)

Кожен пункт перевірено по коду заново.

| Пункт аудиту 1 | Статус |
|---|---|
| Планувальник `basket:clear-abandoned` | **зроблено** цим аудитом (§1.1) |
| Скіли: шлях `Core/Enums/PageModule.php` | **актуально**, не зроблено (§3.1) |
| make-api: прибрати Repository | **актуально**, не зроблено (§3.2) |
| make-filament-resource: приклад `Basket` | **актуально**, не зроблено (§3.3) |
| make-model: не кожен модуль — PageModule | **актуально** (§3.4) |
| plan-task: `cavecrew-investigator`, PHPUnit 12 | **актуально** (§3.5) |
| `.claude/testing.md`: середовище запуску | **переформульовано** — див. §3.6, твердження попереднього аудиту про «OpenServer/PHP 8.4 на хості» для цієї машини невірне (хостовий `php -v` = 7.4.3, тести йдуть тільки через Sail) |
| `.claude/testing.md`: PHPUnit 12 → 13 | **актуально** (composer: `^13.0`) |
| `.claude/testing.md`: трейт `InteractsWithModules` | **актуально** (§3.6) |
| `.claude/testing.md`: таблиця Test structure | **актуально** (§3.6) |
| `.claude/testing.md`: Filament-тести, приклад Unit, таблиця фабрик | **актуально** (§3.6) |
| `frontend.md` правило 3 vs `user_auth/partials/forms/` | **актуально** (§3.7) |
| `backend.md`: додати абзац про `Queries/` | **актуально**, розширено до окремої сторінки (§8.1) |
| `routing.md` §4 приклад контролера | **актуально** (§4.3) |
| `routing.md` §2 unmapped-модулі | **актуально** (§4.3) |
| `architecture.md` Routing / Module Structure | **актуально** (§4.1) |
| `sitemap.md` шляхи в Core | **актуально** (§4.5) |
| `uuid.md` Repository і роут `/order/{uuid}` | **актуально** (§4.6) |
| `cart.md` ForkCart/`carts`/checkout/промокоди | **актуально** (§4.4) |
| `docs/starter/testing.md` шляхи прикладів і `^12.5` | **актуально** (§4.9) |
| `letters.md` `lang/uk`, `NotificationSourceEnum` | **актуально** (§4.7) |
| `logs.md` Sources | **актуально** (§4.8) |
| `claude.md` дерево скілів, PHPUnit 12 | **актуально** (§4.10) |
| `docs/starter/todo.md` — викреслити зроблене | **актуально й ширше**: у списку висить уже реалізоване (§7.3) |
| `docs/README.md` версія PHP | **інверсія**: правити `composer.json`, не доки (§1.3) |
| `docs/README.md` WSL2 `copy` → `cp` | **актуально** (§7.1) |
| Кореневий README: посилання на dashboard.md | **актуально й ширше** (§7.1) |
| `docs/api/README.md` postman-колекція | **актуально** (§5.1) |
| `docs/api/auth.md` logout | **актуально** (§5.2) |
| `docs/api/shop.md` фільтри | **актуально**, уточнено ім'я класу (§5.3) |
| `docs/project/README.md` заглушка | **актуально** (§7.2) |
| `docs/project/product_items.md` `ShopOrders/` | **актуально й ширше** (§7.2) |
| `docs/project/todo.md` порожній | **актуально** (§7.2) |
| Git: додати `product_attributes.md`, `product_items.md` | **виконано** — обидва файли в індексі |
| Тести в корені сьютів | **актуально** (§9.A15) |
| composer `allow-plugins` pest | **актуально** (§1.4) |

---

## 3. `.claude/` — інструкції, по яких генерується код

Найвищий пріоритет: помилка тут перетворюється на неправильний код у наступній задачі.

- [ ] **3.1 `[docs]` P1 — неіснуючий шлях до `PageModule`.** Енум живе в
  `app/Modules/Page/Enums/PageModule.php` (перенесено комітом `cc37b50`). Виправити:
  `.claude/skills/make-api/SKILL.md:30`, `.claude/skills/make-model/SKILL.md:17` і `:39`,
  `.claude/skills/make-filament-resource/SKILL.md:24`.
- [ ] **3.2 `[docs]` P1 — make-api вчить репозиторіям, яких у проєкті немає.**
  `SKILL.md:102` інжектить `{Module}Repository` у шаблон Action, `SKILL.md:296` посилається на
  `ShopRepository`. Реальний шар читання — `Queries/` (21 клас). Переписати Step 7 і
  «Reuse checklist» під Query-класи. Заодно `SKILL.md:267-268`: приклади імен
  (`GetCategoriesAction`, `ShowCategoryAction`, `ShopIndexCategoriesRequest`) не відповідають
  реальним (`SearchCategoriesQuery`, `FindCategoryByIdQuery`, `ShopCategoriesRequest`).
- [ ] **3.3 `[docs]` P2 — make-filament-resource: приклад `Basket`.**
  `SKILL.md:94` — модуля `Basket` немає. Реальний приклад:
  `app/Filament/Resources/Shop/Cart/CartResource.php`.
- [ ] **3.4 `[docs]` P2 — make-model: не кожен новий модуль отримує `PageModule`.**
  Модулі без публічної CMS-сторінки (`ShopOrder`, `Subscriber`, `Sitemap`, `Translation`,
  `Notification`) кейса в енумі не мають; їх роути монтуються за kebab-назвою папки або через
  `MODULE_PREFIX_OVERRIDES` (`ModuleServiceProvider:34-36,143-156`). Додати крок «чи має модуль
  вимикатись через `config/modules.php`».
- [ ] **3.5 `[docs]` P3 — plan-task.** Прибрати агента `cavecrew-investigator` (у проєкті лишити
  `Explore`), «PHPUnit 12» → «PHPUnit 13» (`SKILL.md:37,121`).
- [ ] **3.6 `[docs]` P1/P2 — `.claude/testing.md` розійшовся з реальністю за сімома пунктами:**
  - `:5` «PHPUnit 12 only» → PHPUnit 13 (`composer.json:29` — `^13.0`);
  - `:9` «Unit — Actions, DTOs, **repositories**, helpers» → репозиторіїв немає, це Queries;
  - `:10` перелічено два трейти, реально їх три — додати `InteractsWithModules`
    (`tests/Concerns/InteractsWithModules.php`) і `TestCase::skipUnlessModules()`
    з автоматичним пропуском тестів вимкненого модуля за неймспейсом (`tests/TestCase.php:25-63`);
  - `:14-16` «Tests must run inside Sail… local PHP 8.3 will fail on Symfony 8» — причина
    неточна; правильно: хостовий PHP має бути ≥ 8.4.1 (`composer why-not php 8.3.0`),
    на цій машині він 7.4, тож єдиний робочий шлях — `./vendor/bin/sail artisan test`;
  - `:46` `Feature/Auth/` → `Feature/UserAuth/`; у таблиці немає рядків для реальних папок
    `Feature/Console`, `Feature/Feedback`, `Feature/Index`, `Feature/News`, `Feature/ShopCart`,
    `Feature/ShopOrder`, `Feature/Sitemap`, `Feature/Subscriber`, `Unit/Core`,
    `Unit/Filament/Dashboard`, `Unit/Notification`, `Unit/**/ViewModels`;
  - `:52` рядок `Unit/` згадує `ShopRepository` (немає) і відносить `CreateOrderAction` до Shop
    (реально `App\Modules\ShopOrder\Actions\CreateOrderAction`); те саме в прикладі `:326-332`
    (неймспейси `App\Modules\Shop\Actions\CreateOrderAction`, `App\Modules\Shop\Dtos\CreateOrderData`
    не існують — має бути `ShopOrder`, а неймспейс тесту `Tests\Unit\ShopOrder`);
  - `:184` «Each Filament resource gets its own subdirectory» → реально підпапка = модуль
    (`tests/Feature/Filament/{Users,Shop,Logs,Geo}/`), клас `{Resource}ResourceTest`;
  - `:142-160` таблиця фабрик неповна: у `database/factories/` 25 файлів, у таблиці 17.
    Бракує `AuthLogFactory`, `CartFactory`, `CartProductFactory`, `NewsFactory`,
    `ProductReviewFactory`, `PromocodeFactory`, `SocialProfileFactory`, `SubscriberFactory`;
  - `:166-174` таблиця сидерів не згадує `RolesAndPermissionsSeeder`, `LanguageSeeder`,
    `SmsTemplateSeeder` і модульні `{Module}Seeder` (`DatabaseSeeder:20-26,36-57`).
- [ ] **3.7 `[docs|code]` P2 — `frontend.md` правило 3 vs реальні шаблони.**
  Правило (`:42-48`) дозволяє в модулі рівно три місця (корінь, `partials/`, `layouts/`), але в
  коді є `resources/views/modules/user_auth/partials/forms/`. Плюс правило нічого не каже про
  глобальні підпапки `resources/views/partials/{builder,pagination}/`, які теж існують.
  Обрати одне: перенести форми пласко в `partials/` і поправити `@include`, або дозволити
  підпапки всередині `partials/` і описати це.
- [ ] **3.8 `[docs]` P2 — `backend.md` не знає про шар `Queries/`.** Файл описує Actions, DTO,
  ViewModels, Resources, але не читання. Додати абзац з правилом розміщення
  (`app/Modules/{Module}/Queries/`, іменування `Find*Query` / `Get*Query` / `Search*Query`,
  вхід — скаляри або DTO, не `Request`) і посиланням на нову сторінку з §8.1.
- [ ] **3.9 `[docs]` P3 — `CLAUDE.md`.** Файл із трьох посилань і одного правила. Додати рядок
  про `docs/README.md` як точку входу в документацію проєкту (зараз Claude знаходить `docs/`
  лише випадково) і про заборону Pest.

---

## 4. `docs/starter/`

- [ ] **4.1 `[docs]` P2 — `architecture.md`.**
  - `:116-123` «Routes remain in standard Laravel folders для POST and API» — `routes/web.php` і
    `routes/api.php` порожні (7 і 1 рядок коментарів), усі 86 роутів приходять з модулів;
  - `:83-93` «Typical contents» містить `Repositories` і `Policies`, яких у модулях немає
    (єдина політика — `app/Policies/UserPolicy.php`), і не містить реальних `Queries`,
    `ViewModels`, `Observers`, `Services`, `Http/Requests`, `Http/Resources`;
  - `:17-26` приклад дерева модулів показує 7 із 18 — або позначити як фрагмент, або дати повний список.
- [ ] **4.2 `[docs]` P3 — `modules.md`.** Текст точний (звірено з `config/modules.php` і
  `ModuleGate`), крім §4 — див. §1.2. Додати, що `_debug` теж не має тумблера,
  хоча це саме той модуль, який на проді зайвий.
- [ ] **4.3 `[docs]` P1 — `routing.md`.**
  - `:57-59`: «модулі без `PageModule` реєструються без префікса» — невірно. Без префікса лише
    `Core` і `Sitemap` (`ROOT_LEVEL_MODULES`), решта отримує kebab-префікс від імені папки
    або override (`ShopOrder` → `/orders`). У списку вказано `Notification`, у якого роутів
    немає взагалі, і немає `Subscriber` (`/subscriber`) та `_debug` (`/_debug`);
  - `:87-108` приклад контролера суперечить `.claude/backend.md:9` і `BaseWebController`:
    показує `view()`, `compact()` і `view()->share([...meta...])` замість
    `$this->render('modules.news.index', $page->toViewData())` з ViewModel. Реальний
    `News\Http\Controllers\Web\IndexController` уже написаний правильно — приклад відстав;
  - додати, що `withSchedule()`/DB-префікси означають: `route:cache` фіксує префікси, отримані
    на момент кешування (наслідок §9.A3).
- [ ] **4.4 `[docs]` P1 — `cart.md` описує попередню версію модуля.**
  - таблиця `carts` → **`shop_carts`** (`0001_01_01_000019_create_shop_carts_table.php`);
    у `:168` правило валідації показано як `exists:carts,uuid`, у коді `exists:shop_carts,uuid`;
  - `ForkCartAction` → **`DuplicateCartAction`** (`:47,118,141`);
  - `:127-128,204-207` «checkout — статичний мокап» і «`CreateOrderAction` не підключений до
    кошика» — неправда: є `ShopOrder\Actions\PlaceOrderFromCartAction`, роут
    `POST /orders/create` (`order.create`) і форма оформлення в `shop_cart`;
  - немає жодного слова про промокоди, хоча це третина модуля: `PromocodeController`,
    роути `cart/promocode/apply|remove`, `ValidatePromocodeAction`, `ApplyPromocodeToCartAction`,
    `RemovePromocodeFromCartAction`, `CalculateDiscountAction`, `IncrementPromocodeUsageAction`,
    `FindPromocodeByCodeQuery`, таблиця `shop_promocodes`, `PromocodeSeeder`, тумблер
    `shop_promocodes` у `config/modules.php`;
  - `:171-176` перелічено лише `CartProductObserver`, у коді ще й `CartObserver`
    (`AppServiceProvider:65`);
  - `:74` назву команди вже можна лишати як є — після §1.1 вона збігається.
- [ ] **4.5 `[docs]` P2 — `sitemap.md` вказує на неіснуючі файли.** Контролер і роути живуть у
  власному модулі: `app/Modules/Sitemap/Http/Controllers/SitemapController.php`,
  `app/Modules/Sitemap/Http/Routes/web.php` (не в `Core`). Поправити вступ `:122` і таблицю
  «Files» `:200-208`. Решта (PER_PAGE = 500, набір URL, гейти по `Page`) звірена — вірна.
- [ ] **4.6 `[docs]` P2 — `uuid.md`.**
  - `:64-77` «Put lookup logic in a repository» + `app/Modules/Shop/Repository/OrderRepository.php`
    — такого класу і шару немає. Реально `App\Modules\ShopOrder\Queries\FindOrderByUuidQuery`;
  - `:88` роут `/order/{uuid}` у Shop → `/orders/{uuid}` (`order.show`) у `ShopOrder`;
  - `:94-96` «модулі без `PageModule` мають шарити layout самі» — є
    `PageContextSharer::shareWithoutPage()` (`app/Modules/Page/Services/PageContextSharer.php:34`);
  - `:110-115` приклад тесту репозиторію переписати на Query.
- [ ] **4.7 `[docs]` P2 — `letters.md`.**
  - `:79-81` `lang/uk/letter_templates.php` — каталогу `lang/uk` не існує, у проєкті лише `lang/en`;
  - `:197-204` джерела «admin, system, api, custom values» → реальний `NotificationSourceEnum`
    має рівно три кейси: `console`, `admin`, `system`;
  - `:65-72` приклад «додай кейс `ORDER_CREATED`» описує кейс, який уже є
    (`LetterEnum`: `feedback`, `password_reset`, `registration`, `order_created`) — взяти
    вигаданий приклад, щоб інструкція лишалась інструкцією.
- [ ] **4.8 `[docs]` P2 — `logs.md`** (загалом найточніший документ у наборі, три розходження):
  - `:186-190` розділ «Sources»: `admin, api, console, client` → реальний `LogSourceEnum`:
    `admin_panel`, `api`, `console`, `system`, `client`;
  - `:42` у прикладі JSON `"source": "admin|api|console"` — те саме;
  - `:94-110` знімок `config/log.php` застарів: немає `retention_days` (`LOG_RETENTION_DAYS`),
    `enabled` показано літералом замість `env('LOG_ENABLED', true)`, `excluded_models` показано
    порожнім, хоча там `Log::class` і `AuthLog::class`.
- [ ] **4.9 `[docs]` P2 — `docs/starter/testing.md`.** `:3` `phpunit/phpunit ^12.5` → `^13.0`;
  `:173-180` розділ Concerns перелічує два трейти замість трьох (`InteractsWithModules`);
  шляхи прикладів `tests/Unit/Shop/OrderDtoTest.php`, `tests/Unit/Shop/CreateOrderActionTest.php`
  → `tests/Unit/ShopOrder/…`.
- [ ] **4.10 `[docs]` P3 — `claude.md`.** У дереві `.claude/skills/` немає `plan-task/`;
  `:103` «PHPUnit 12» → 13; `:133` «make-filament-resource → Filament Resource + tests» — скіл
  тестів не генерує; опис `CLAUDE.md` («project overview, stack») замінити на «точка входу
  з посиланнями на role-guides».
- [ ] **4.11 `[docs]` P2 — `commands.md` (16 рядків) не містить жодної команди проєкту.**
  Додати реальні: `cart:clear-abandoned {days=30}`, `logs:prune {--dry-run}`, `images:clear
  {preset?} {entity?}`, `geo:download`, `geo:import`, `make:admin-user`, і зазначити, що
  модульні команди підхоплюються автоматично з `app/Modules/{Module}/Console/Commands`
  та вимикаються разом з модулем.
- [ ] **4.12 `[docs]` P3 — `view-models.md`, `images.md`, `oauth.md`, `sms.md`, `translations.md`
  розходжень не виявлено.** Звірено: список ViewModels і `toViewData()`, пресети
  `config/image_sizes.php` + роут `/storage/{preset}/{path}`, `config/social.php` і роути
  `auth/social/*`, `SmsProviderEnum`/`LogSmsStatusEnum`, `t()` і кеш словників.

---

## 5. `docs/api/`

- [ ] **5.1 `[docs]` P3 — `README.md:54-56`** обіцяє postman-колекцію в `docs/api` — файлу немає.
  Або додати `*.postman_collection.json`, або прибрати абзац.
- [ ] **5.2 `[docs]` P2 — `auth.md` не описує `POST /api/auth/logout`** (`auth:sanctum`,
  `api.logout`, `app/Modules/UserAuth/Http/Routes/api.php:11-13`).
- [ ] **5.3 `[docs|code]` P2 — `shop.md`, List Products: чотири з семи параметрів не валідуються.**
  Документовані `brand`, `price_from`, `price_to`, `search` реально читаються з `Request` у
  `Shop\Queries\Product\PrepareShopFilterQuery:33-47`, але `ShopProductsRequest` валідує лише
  `category_id` і `sort`. Або додати правила в request (`brand` — рядок з id через кому,
  `price_from|price_to` — numeric, `search` — string|max), або прибрати їх з документації.
  Рекомендація: додати правила — параметри реально працюють.
  ⚠️ У попередньому аудиті клас названо `PrepareShopFilterAction` — його перейменовано на Query.
- [ ] **5.4 `[docs]` P3 — README не згадує загальний формат помилки валідації.** `auth.md:155-165`
  показує відповідь із ключем `errors`, якого немає в описі конверта
  (`{success, message, data}`) у `README.md:33-39`. Звірити з `Core\Traits\Api\ApiResponse`.

---

## 6. `docs/filament/`

- [ ] **6.1 `[docs]` P3 — `README.md` перелічує 4 документи, а `dashboard.md` у списку є,
  у кореневому `README.md` — ні** (див. §7.1).
- [ ] **6.2 `[gap]` P2 — половина `app/Filament/` не описана.** Поза документацією лишились:
  `Builder/Blocks/` (конструктор контенту сторінок: `TextBlock`, `ImageBlock`, `GalleryBlock`,
  `VideoBlock`, `TeasersBlock` — саме він створює ті «product teasers», через які `modules.md`
  забороняє вимикати shop), `Generators/` (генератори класів ресурсів),
  `Support/{ArtisanCommandRunner,NestedSetFields}`, `Traits/{HasDefaultTable,HasSeoFields,
  CanGenerateGetTitleMethod}`, `Actions/{BaseEditAction,ClearLogAction,ViewLogsAction}`,
  `Filters/UserSelectFilter`, `Forms/UserSelect`.
  Мінімум: розділ «Спільні будівельні блоки адмінки» у `docs/filament/README.md`.
- [ ] **6.3 `[docs]` P3 — `resource-generation.md`** звірено, розходжень немає
  (`--simple --generate`, `RecordActionsPosition::BeforeColumns`, модальне редагування,
  `ViewAction` як зовнішнє посилання). `dashboard.md` теж повністю відповідає
  `app/Filament/Dashboard/**` і `app/Filament/Widgets/**`.

---

## 7. `docs/README.md`, кореневий `README.md`, `docs/project/`

- [ ] **7.1 `[docs]` P2 — точки входу.**
  - `docs/README.md:13` «PHP >= 8.4 (minimum)» — лишити, натомість правити composer (§1.3);
  - `docs/README.md`, WSL2 крок 7: `copy .env.example .env` → `cp .env.example .env` (це bash);
  - кореневий `README.md` не посилається на `docs/filament/dashboard.md`,
    `docs/starter/todo.md`, `docs/project/product_attributes.md`, `docs/project/product_items.md`,
    `docs/project/docs_audit.md`;
  - у блоці Starter немає `docs/starter/modules.md`… — є, звірено; бракує саме перелічених вище.
- [ ] **7.2 `[docs]` P2 — `docs/project/`.**
  - `README.md` — заглушка з трьох рядків; зробити індексом розділу;
  - `todo.md` — порожній файл; заповнити або видалити;
  - `product_items.md`: `app/Modules/ShopOrders/` → `ShopOrder` (рядки 52, 73, 421, 438, 465, 468,
    597, 615, 634-637, 644, 661-662), `tests/Unit/Dashboard/` → `tests/Unit/Filament/Dashboard/`
    (рядок 664), `app/Modules/Shop/Queries/Product/GetCatalogStatsQuery.php` і `GetTopProductsQuery.php`
    (рядки 324, 345, 623) → `app/Filament/Dashboard/Queries/Shop/`;
  - обидва файли специфікацій (`product_items.md`, `product_attributes.md`) описують ще не
    реалізовану роботу — додати на початок кожного статус-рядок «специфікація, не реалізовано»,
    щоб їх не читали як опис поточного коду.
- [ ] **7.3 `[docs]` P2 — `docs/starter/todo.md` містить уже зроблене.**
  «Run migrations and other console commands from the web interface» реалізовано
  (`app/Filament/Pages/DebugTools/DebugTools.php` + `ArtisanCommandRunner`), «Dynamic product
  attributes» і «shop_product_items» мають специфікації в `docs/project/`. Викреслити або
  перенести з позначкою «в роботі».

---

## 8. Функціонал у коді без документації

- [ ] **8.1 `[gap]` P1 — шар `Queries/` не описаний ніде.** 21 клас у 9 модулях
  (`Page`, `Shop`, `ShopCart`, `ShopOrder`, `News`, `PageText`, `Profile`, `Translation`, `User`)
  плюс окреме сімейство `app/Filament/Dashboard/Queries/`. Це основний шар читання даних, через
  нього ходять усі контролери, і про нього немає ні сторінки, ні абзацу — тільки згадка в
  `actions.md:46` («A genuine two-state question belongs in a Query class»). Завести
  `docs/starter/queries.md`: межа Action/Query, іменування, що приймає на вхід (не `Request`),
  де живе (`app/Modules/{Module}/Queries/`), і зв'язок з ViewModels.
- [ ] **8.2 `[gap]` P2 — сім модулів без жодного рядка документації.**
  `Geo` (2 команди `geo:download`/`geo:import`, `config/geo.php` зі списком країн, 3 моделі,
  імпортер/завантажувач, Filament-ресурси), `Subscriber` (`SubscribeAction`, `ActionResult`,
  роут `POST /subscriber`, адмінка), `Feedback`, `Profile`, `Index`, `PageText`,
  `_debug` (протокол «ABC-response», на який спирається JS кошика — `cart.md:145-147`
  посилається на «ABC-response runner», ніде не описаний).
  Мінімум по абзацу в `architecture.md` або окремі сторінки для `Geo` і `_debug`.
- [ ] **8.3 `[gap]` P2 — імперсонація користувачів не документована.**
  `GET /auth/impersonate/{user}` (`impersonate`), `POST /auth/impersonation/leave`,
  `ImpersonateAction`, `LeaveImpersonationAction`, `GenerateImpersonationTokenAction`,
  кнопка в `UserResource`, банер у `resources/views/partials/navbar.blade.php`.
  Функція з прямим впливом на безпеку — описати, хто має право і як лишити режим.
- [ ] **8.4 `[gap]` P1 — адмінська сторінка DebugTools не документована.**
  `app/Filament/Pages/DebugTools/DebugTools.php` + `ArtisanCommandRunner` дозволяють
  супер-адміну з вебу запускати `migrate`, `migrate:rollback`, **`migrate:fresh`,
  `migrate:fresh --seed`, `db:seed`**, чистки кешу. Доступ обмежений
  `canAccess(): auth()->user()?->isSuperAdmin()`. Це найнебезпечніша кнопка в проєкті і
  про неї не написано ніде. Описати в `docs/filament/`: список дозволених команд, хто має
  доступ, і рекомендацію вимикати сторінку на проді.
- [ ] **8.5 `[gap]` P3 — конфіги без опису.** `config/geo.php` (список країн імпорту),
  `config/permission.php` + `RolesAndPermissionsSeeder` + `Gate::before` для `super_admin`
  (`AppServiceProvider:59`) — модель ролей і прав ніде не описана, хоча
  `docs/starter/todo.md` досі числить «User roles in the admin panel» як план.
- [ ] **8.6 `[gap]` P3 — `.env.example` ніде не пояснений.** Ключі `LOG_ENABLED`,
  `LOG_RETENTION_DAYS`, `AUTH_LOG_ENABLED`, `AUTH_COOLDOWN_SECONDS`, `IMAGE_DRIVER`,
  соціальні провайдери — розкидані по різних сторінках або відсутні. Зібрати таблицю
  в `docs/README.md`.

---

## 9. Архітектура і дизайн класів

Тут не про документацію, а про код. Кожен пункт — з файлом і рядком, з ціною зміни.
Пункти A1-A3 варті окремих задач, решта — на розсуд.

- [ ] **9.A1 P1 — каскадне видалення живе всередині реєстрації лог-слухачів.**
  `ModuleServiceProvider::registerLogListeners():240-275` вішає wildcard-слухачі на
  `eloquent.created: *`, `eloquent.updated: *`, `eloquent.deleting: *` — і всередині обробника
  `deleting` виконує каскадне видалення дітей за трейтом `CascadesDeletes`.
  Три проблеми в одному методі:
  1. **назва бреше** — метод про логи, а робить видалення даних;
  2. **кожен запис будь-якої моделі** тягне синхронний `INSERT` у `logs`
     (`LogService` фільтрує вже після виклику) — на масових операціях це подвоєння запитів;
  3. каскад видаляє дітей по одному (`->each(fn($child) => $child->delete())`) — N запитів
     на N дітей, всередині транзакції видалення батька.
  Пропозиція: каскад перенести в сам трейт `CascadesDeletes` (`static::deleting()` у `boot`),
  логування лишити окремою реєстрацією. Ціна: одна правка провайдера + трейта, поведінка
  не змінюється, тести на `HasImageConversion`/каскади вже є.

- [ ] **9.A2 P1 — `AppServiceProvider` знає про шість модулів поіменно.**
  `app/Providers/AppServiceProvider.php:62-67` — прямі виклики `Order::observe(...)`,
  `Cart::observe(...)` тощо. Це ламає обіцянку `modules.md` §4 (див. §1.2) і робить ядро
  залежним від модулів, тоді як `ModuleServiceProvider` уже вміє обходити модулі з урахуванням
  `ModuleGate`. Пропозиція: домовленість «модуль реєструє своїх обсерверів сам» —
  або через власний `{Module}ServiceProvider`, або через конвенцію
  `app/Modules/{Module}/Observers/*` + автопідключення в `ModuleServiceProvider`.
  Ціна: середня (6 модулів), виграш: тумблери модулів починають працювати по-справжньому.

- [ ] **9.A3 P2 — бут коштує запитів до БД і сканувань ФС на кожен запит.**
  `ModuleServiceProvider::boot()` тричі обходить `app/Modules` (`loadModuleRoutes`,
  `loadModuleCommands`, `loadModuleMigrations`), а `registerWebRoute()` для кожного модуля з
  `PageModule` робить запит `FindPageByModuleQuery` (`:158-165`), загорнутий у
  `catch (Throwable)`, який ковтає будь-яку помилку — включно з помилкою підключення до БД.
  Плюс `loadModuleCommands()` робить `File::allFiles()` по кожній папці команд і викликає
  `Artisan::starting()` у циклі на кожен файл.
  Наслідки: зайві запити на кожному HTTP-запиті без `route:cache`; при `route:cache`
  префікси «застигають» у стані на момент кешування, і зміна слага сторінки в адмінці
  мовчки не діє, доки не перекешувати роути — це ніде не написано.
  Пропозиція: кешований маніфест модулів (шляхи роутів, команд, міграцій + мапа
  `PageModule → slug`), який перебудовується на `optimize`/зміні `pages.slug`;
  `catch (Throwable)` звузити до `QueryException`. Ціна: середня, ефект помітний.

- [ ] **9.A4 P2 — HTTP-шар протікає в доменний.**
  `Shop\Queries\Product\PrepareShopFilterQuery::handle(?Category $category, Request $request)`
  читає `$request->query(...)` всередині Query; `UserAuth\Actions\ImpersonateAction::handle(Request,
  User)` і `LeaveImpersonationAction::handle(Request)` приймають `Request` в Action.
  Це суперечить власному правилу «DTO для входу» (`.claude/backend.md:46`) і робить ці класи
  невикористовуваними з консолі/черги/тестів без підробки запиту.
  Пропозиція: `ShopFilterRequestData` (spatie/laravel-data) на вході Query; для імперсонації
  передавати те, що реально потрібно (id сесії/гварда), а не весь `Request`.

- [ ] **9.A5 P2 — межа Action/Query розмита.**
  За власним правилом (`actions.md:46`) читання — це Query. Але в `Actions/` живуть
  `FindCartAction`, `GetOrCreateCartAction` (`ShopCart`) і `GetTranslationsAction` (`Translation`),
  причому в тому ж модулі є `Queries/GetTranslationsByLocaleQuery` — два класи для одного
  читання. Пропозиція: `FindCartAction` → `FindCartQuery`; `GetOrCreateCartAction` лишити
  Action (він створює); `GetTranslationsAction` злити з Query. Ціна: низька, але зачіпає
  ShareModulePageContext і контролери кошика — робити разом з тестами.

- [ ] **9.A6 P3 — `Services/` і `Support/` без визначеної межі.**
  У `Core/Services/`: `LogService`, `AuthLogService`, `AuthCooldownService`,
  `ImageConversionService`, `ImageResizer`, `UrlHelper`. У `Core/Support/`: `ModuleGate`,
  `LogSourceResolver`, `AuthLogSourceResolver`, `UserAgentParser`. `UrlHelper` і `ImageResizer` —
  за характером те саме, що `UserAgentParser` (чисті хелпери без стану), але лежать в іншій папці.
  Пропозиція: одне речення правила в `backend.md` («Services — робота зі станом/БД/зовнішнім
  світом; Support — чисті хелпери й резолвери») і перенести `UrlHelper`/`ImageResizer` у `Support`.

- [ ] **9.A7 P3 — порожні каталоги-абстракції.** `app/Modules/Core/Actions/` і
  `app/Modules/Core/Interfaces/` порожні. Видалити: порожня папка в ядрі читається як
  «сюди щось кладуть», і рано чи пізно туди покладуть доменну логіку, якій там не місце.

- [ ] **9.A8 P3 — рядкові аліаси в контейнері + фасади замість DI.**
  `AppServiceProvider:38-48`: `singleton('url-helper', ...)`, `singleton('translation', ...)`,
  `singleton('letter-template-service', fn() => new LetterTemplateService)` (повз контейнер,
  тож залежності сервісу не інжектяться), плюс фасади `UrlFacade`, `LetterTemplateFacade`,
  `SmsTemplateFacade`. Це ховає залежності й ускладнює підміну в тестах — недарма
  `.claude/testing.md:102` окремо дозволяє мокати саме `LetterTemplateFacade`.
  Пропозиція: біндити по імені класу, ін'єктити через конструктор, фасади лишити тільки там,
  де їх викликають із blade.

- [ ] **9.A9 P2 — розклад завдань розкиданий по двох місцях і невидимий для тестів.**
  `bootstrap/app.php:38-40` (`withSchedule`) і `routes/console.php:12` (`Schedule::command`).
  При цьому `withSchedule()` реєструється через `Artisan::starting()`
  (`vendor/laravel/framework/.../ApplicationBuilder.php:375-386`), тому в тесті розклад порожній,
  доки не піднято консольне ядро — саме через це `ScheduleTest` викликає `Artisan::all()`.
  Пропозиція: тримати весь розклад у `routes/console.php`.

- [ ] **9.A10 P3 — команди живуть за двома різними конвенціями.**
  Модульні команди використовують атрибути Laravel 13 (`#[Signature]`, `#[Description]`) і
  підхоплюються `ModuleServiceProvider`; `app/Console/Commands/{PruneLogsCommand,MakeAdminUser}.php`
  — старий `protected $signature` і поза модулями, хоча `logs:prune` за змістом належить `Core`,
  а `make:admin-user` — `User`. Наслідок: ці дві команди не вимикаються разом з модулем і не
  видно, що вони частина модуля.

- [ ] **9.A11 P3 — `BaseWebController::render()` не додає поведінки.**
  Метод (`Core/Abstract/BaseWebController.php:100-106`) робить рівно те саме, що `view()`.
  Це домовленість, загорнута в клас. Або лишити як є і чесно назвати це конвенцією в доках,
  або дати методу сенс: приймати `BaseViewModel` і самому робити `toViewData()`, тоді правило
  «в blade їдуть ViewModels» перестає бути усною угодою:
  `protected function render(string $view, BaseViewModel|array $data = [])`.

- [ ] **9.A12 P3 — `ModuleGate` ділить простір імен з ключем `dependencies`.**
  `ModuleGate::isEnabled():19` читає `config("modules.{$module}", true)`, а `dependencies` —
  такий самий ключ верхнього рівня в тому ж масиві. Модуль з іменем `dependencies` неможливий,
  а рекурсія `:25` не має захисту від циклу (`a → b → a` = нескінченна рекурсія).
  Пропозиція: винести перемикачі під ключ `modules` окремо від `dependencies` або додати
  guard зі списком уже перевірених модулів.

- [ ] **9.A13 P3 — логіка підготовки даних у контролері.**
  `Shop\Http\Controllers\Api\ShopController::attachVisibleChildren():55-70` вантажить усе
  дерево категорій і в пам'яті розвішує `children` — це робота Query або Resource, а не
  «тонкого HTTP-адаптера» (`.claude/backend.md:45`). Метод правильний по суті (прибирає N+1),
  але місце йому в `SearchCategoriesQuery`.

- [ ] **9.A14 P3 — модель прав описана тільки кодом.** `Gate::before` дає `super_admin` обхід
  усіх політик (`AppServiceProvider:59`), єдина політика — `UserPolicy`, права роздає
  `RolesAndPermissionsSeeder`, доступ до адмінки перевіряє `admin`-permission. Для стартера,
  на якому будують інші проєкти, це має бути описано (див. §8.5).

- [ ] **9.A15 P3 — п'ять тестів лежать у корені сьютів усупереч власній конвенції.**
  Перенести з оновленням неймспейсів:
  `tests/Feature/{HasImageConversionTest,ImagePreviewControllerTest,ImagesClearCommandTest}.php`
  → `tests/Feature/Core/`; `tests/Unit/{ImageConversionServiceTest,ImageResizerTest}.php`
  → `tests/Unit/Core/`. ⚠️ Після перенесення `TestCase::skipIfModuleDisabled()` почне читати
  сегмент `Core` як ім'я модуля — `ModuleGate::isEnabled('core')` повертає `true`, тож тести
  не пропустяться, але це варто перевірити прогоном.

- [ ] **9.A16 P3 — `_debug` не вимикається.** Модуль з тестовим протоколом ABC-response
  монтує роути `/_debug/*` на будь-якому середовищі: тумблера в `config/modules.php` немає,
  умови на `app()->environment()` теж. Додати тумблер або обмежити локальним середовищем.

---

## 10. Порядок робіт

1. **Зроблено:** §1.1 — баг планувальника, окремий коміт `f30c318` з тестом.
2. **Далі — код, що ламає обіцянки:** §1.2 + §9.A2 (обсервери модулів), §1.3 (`composer.json`),
   §1.4, §1.5.
3. **Інструкції для Claude** (§3) — вони найшвидше окуповуються: доки правляться раз,
   а генерований по них код пишеться щодня.
4. **Документація по зонах:** §4 (starter) → §5 (api) → §6 (filament) → §7 (README, project).
5. **Прогалини** (§8): спершу §8.1 (`queries.md`) і §8.4 (DebugTools), далі решта.
6. **Архітектура** (§9): A1 і A3 окремими задачами з тестами; A4-A16 — по мірі дотику
   до відповідного коду, не одним заходом.
7. Після §9.A15 обов'язково прогнати `./vendor/bin/sail artisan test`.
