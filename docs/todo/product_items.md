# Модифікації товару (`shop_product_items`) — інструкція до реалізації

> **Статус: специфікація, не реалізовано.** Перевірено 2026-09-05: таблиці
> `shop_product_items` і супутніх класів у проєкті немає.
> Документ описує, що саме треба зробити, у яких файлах і чому, щоб інша сесія
> (або ця ж пізніше) могла виконати задачу без повторного дослідження коду.
> Читати як план робіт, а не як опис поточної поведінки.
>
> **2026-10-27:** `price2` прибрано, а «скільки штук» вирішено простіше — наборами
> (`shop_products.packs`, до 3 штук, рядок кошика = товар + `pack_quantity`), див.
> `docs/project/shop.md`. План нижче (окрема таблиця модифікацій з фото й артикулом)
> перед стартом треба переглянути під це.

## 1. Мета

Ввести таблицю модифікацій товару `shop_product_items` (варіанти: розмір/колір/
комплектація тощо) і закласти масштабування наперед: усі запити, кошик і
замовлення одразу працюють через модифікації, тож щоб у майбутньому розширити
функціонал, достатньо буде правити лише картку товару й адмін-форму.

Ключові вимоги від користувача:

- `price` (і `price2`) переїжають з `shop_products` у `shop_product_items`.
- Фото товару тепер належать модифікації: **одне** поле множинного завантаження
  (замість поточних двох — `img` + `imgs`), перше фото — головне/обкладинка.
- У формі редагування товару — репітер модифікацій, мінімум 1 елемент.
- У модифікації є чекбокс «показувати» (`display`), щоб її можна було приховати.
- У списку товарів (каталог, картки, віджети) ціна й фото беруться з **видимої
  модифікації з найменшою ціною**.
- Сторінка товару: якщо модифікація одна — візуально нічого не міняємо; якщо
  дві або більше — показуємо радіобатони вибору, яку саме модифікацію класти
  в кошик.
- При додаванні в кошик передається ще `item_id` (id модифікації).
- Сідери оновити під нову структуру.

## 2. Поточний стан (для контексту)

- `shop_products` (`app/Modules/Shop/database/migrations/0001_01_01_000016_create_shop_products_table.php`):
  містить `price`, `price2`, `img`, `imgs` (json) прямо на товарі.
- `App\Modules\Shop\Models\Product` (`app/Modules/Shop/Models/Product.php`) використовує
  трейт `HasImage` (`app/Modules/Core/Traits/Models/HasImage.php`) з `imgArrayFields = ['imgs']`
  — цей трейт керує переміщенням файлів з `temp/`, видаленням старих файлів і
  eager-конверсіями (`ImageConversionService`).
- Ціна читається напряму (`$product->price`) у багатьох місцях:
  `AddOrUpdateCartItemAction`, `ProductResource` (API), `CreateOrderAction` (через `OrderItemData`),
  `product.blade.php`, `product-card.blade.php`, `GetCatalogStatsQuery::incomplete()`.
- Ціна також використовується **на рівні SQL** (не просто як PHP-атрибут):
  - `RangeFilterData::apply()` (`app/Modules/Shop/Dtos/Filters/RangeFilterData.php`) —
    `$query->where('price', '>=', ...)` / `'<='`.
  - `PrepareShopFilterQuery::prepareRangeFilter()` — `$query->min('price')` / `->max('price')`.
  - `SortOptionEnum::orderBy()` (`app/Modules/Shop/Enums/SortOptionEnum.php`) — сортування
    `['price', 'asc'|'desc']`, застосовується в `SearchProductsQuery::applySorting()`.
  - `GetCatalogStatsQuery::incomplete()` — `whereNull('img')->orWhereNull('price')`.

  Це важливо: коли `price`/`img` фізично зникнуть з `shop_products`, ці конкретні
  місця **перестануть працювати**. Рішення — §5.
- Кошик/замовлення прив'язані до товару через `shop_product_id`:
  `shop_cart_products` (`app/Modules/ShopCart/database/migrations/0001_01_01_000020_create_shop_cart_products_table.php`),
  `shop_order_products` (`app/Modules/ShopOrder/database/migrations/0001_01_01_000018_create_shop_order_products_table.php`).
  Обидві таблиці зберігають знімок (`product_name`, `product_article`, `selling_price`, `quantity`, `total`)
  на момент додавання/оформлення — це не змінюємо, лише додаємо посилання на модифікацію.

## 2.1. Підхід до міграцій: правимо існуючі, нових не додаємо

Це стартер, а не робочий проєкт з даними — тому **нові міграційні файли не
створюємо взагалі**, а редагуємо існуючі та накатуємо через
`php artisan migrate:fresh --seed`. Data-міграція старих даних не потрібна.

Додатковий аргумент за такий підхід: `shop_product_items` мусить існувати
**раніше** за `shop_order_products` (`...000018`) і `shop_cart_products`
(`...000020`), бо вони отримають на неї зовнішній ключ. Новий файл у кінці
списку (`...000029`) зламав би порядок, а перенумерувати існуючі — гірше, ніж
дописати таблицю в той самий файл, де вже створюється `shop_products`.

Разом виходить **3 відредаговані файли міграцій і жодного нового**:

| Файл | Що робимо |
|------|-----------|
| `app/Modules/Shop/database/migrations/0001_01_01_000016_create_shop_products_table.php` | прибрати `price`, `price2`, `img`, `imgs` з `shop_products`; у цьому ж файлі створити `shop_product_items` |
| `app/Modules/ShopOrder/database/migrations/0001_01_01_000018_create_shop_order_products_table.php` | додати колонку `shop_product_item_id` |
| `app/Modules/ShopCart/database/migrations/0001_01_01_000020_create_shop_cart_products_table.php` | додати колонку `shop_product_item_id` |

## 3. Нова таблиця `shop_product_items`

Створюється **в існуючому файлі**
`app/Modules/Shop/database/migrations/0001_01_01_000016_create_shop_products_table.php`,
одразу після `Schema::create('shop_products', ...)` (у `down()` — дропати у
зворотному порядку: спершу `shop_product_items`, потім `shop_products`).

Сам `shop_products` у цьому ж файлі втрачає рядки:

```php
// видалити з Schema::create('shop_products', ...):
$table->decimal('price', 10, 2)->nullable();
$table->decimal('price2', 10, 2)->nullable();
$table->string('img')->nullable();
$table->json('imgs')->nullable();
```

```php
Schema::create('shop_product_items', function (Blueprint $table) {
    $table->id();
    $table->timestamps();
    $table->foreignId('shop_product_id')->constrained('shop_products')->cascadeOnDelete();
    $table->boolean('display')->default(true);   // чекбокс "показувати"
    $table->integer('rank')->nullable();          // порядок модифікацій у репітері й радіобатонах
    $table->string('title')->nullable();          // підпис модифікації в радіобатоні ("XL", "Синій")
    $table->decimal('price', 10, 2)->nullable();
    $table->decimal('price2', 10, 2)->nullable();
    $table->json('imgs')->nullable();              // єдине поле фото модифікації, imgs[0] = головне фото
    // під вибірку "найдешевша видима модифікація товару"
    $table->index(['shop_product_id', 'display', 'price']);
});
```

Модель: `app/Modules/Shop/Models/ProductItem.php`

```php
namespace App\Modules\Shop\Models;

use App\Modules\Core\Traits\Models\HasImage;
use App\Modules\Core\Traits\Models\HasVisibility;
use Illuminate\Database\Eloquent\Model;

class ProductItem extends Model
{
    use HasVisibility;
    use HasImage;

    protected array $imgFields = [];          // немає окремого одиночного "img"
    protected array $imgArrayFields = ['imgs'];

    protected $table = 'shop_product_items';

    protected $fillable = [
        'shop_product_id',
        'display',
        'rank',
        'title',
        'price',
        'price2',
        'imgs',
    ];

    protected $casts = [
        'display' => 'boolean',
        'price' => 'float',
        'price2' => 'float',
        'imgs' => 'array',
    ];

    public function product()
    {
        return $this->belongsTo(Product::class, 'shop_product_id');
    }
}
```

`HasImage` очікує одиночне поле `img` для `getImageAttribute()`/`imageUrl()`
(бере `$this->img`). У `ProductItem` такого поля нема — треба або:

- (а) додати в `HasImage` fallback «якщо `imgFields` порожній, а `imgArrayFields`
  не порожній — брати перший елемент першого `imgArrayFields` як `img`», або
- (б) перевизначити `getImageAttribute()` / `imageUrl()` прямо в `ProductItem`,
  повертаючи `Storage::url($this->imgs[0] ?? null)` з тим самим фолбеком на
  `no_image.svg`, що і в трейті.

Рекомендація: (б) — не чіпати спільний трейт заради одного класу, щоб не
зламати поведінку `Product`, `Category`, `Brand` та інших моделей, які й далі
використовують класичний одиночний `img`.

## 4. Зміни в `App\Modules\Shop\Models\Product`

Колонки `price`, `price2`, `img`, `imgs` прибираються прямо з
`Schema::create('shop_products', ...)` (§3) — жодного `dropColumn` і жодної
додаткової міграції.

У моделі `Product`:

- Прибрати `price`, `price2`, `img`, `imgs` з `$fillable` і `$casts`.
- Прибрати трейт `HasImage` (файли зображень тепер належать `ProductItem`,
  разом з усім lifecycle — переміщення з `temp/`, видалення старих файлів,
  eager-конверсії).
- Додати відношення:

```php
public function items()
{
    return $this->hasMany(ProductItem::class, 'shop_product_id')->orderBy('rank');
}

/**
 * Видимі модифікації, найдешевша перша — саме в такому порядку вони
 * рендеряться радіобатонами на сторінці товару.
 */
public function visibleItems()
{
    return $this->hasMany(ProductItem::class, 'shop_product_id')
        ->visible()
        ->orderBy('price')
        ->orderBy('id');
}

/**
 * Видима модифікація з найменшою ціною — джерело ціни й фото для всіх
 * списків товару (каталог, картки, віджети, API).
 */
public function cheapestItem()
{
    return $this->hasOne(ProductItem::class, 'shop_product_id')
        ->ofMany(
            ['price' => 'min', 'id' => 'min'],   // 'id' — тайбрейкер, якщо ціни рівні
            fn ($query) => $query->where('display', true)->whereNotNull('price'),
        );
}
```

  `ofMany` дозволяє eager-load (`with('cheapestItem')`) без N+1 у списках товарів.
  Другий ключ `'id' => 'min'` обов'язковий: без нього дві модифікації з однаковою
  ціною дадуть дубль рядка в підзапиті.

  Якщо в товару немає жодної видимої модифікації з непорожньою ціною —
  `cheapestItem` буде `null`, і акцесори нижче повернуть `null` / `no_image.svg`.
  Такі товари якраз і ловить фільтр «неповні» (§5).

- Додати проксі-акцесори, щоб **весь PHP-код, що читає атрибути товару**
  (не SQL-запити — про них §5), продовжував працювати без правок:

```php
protected $appends = ['price', 'price2', 'image', 'images'];

public function getPriceAttribute(): ?float
{
    return $this->cheapestItem?->price;
}

public function getPrice2Attribute(): ?float
{
    return $this->cheapestItem?->price2;
}

public function getImageAttribute(): string
{
    return $this->cheapestItem?->image ?? asset('/assets/images/no_image.svg');
}

public function getImagesAttribute(): array
{
    return $this->cheapestItem?->images ?? [];
}

public function imageUrl(string $preset): string
{
    return $this->cheapestItem
        ? $this->cheapestItem->imageUrl($preset)
        : asset('/assets/images/no_image.svg');
}
```

Це покриває: `AddOrUpdateCartItemAction` (`$product->price`), API `ProductResource`
(`$this->price`, `$this->image`), `product-card.blade.php` (`$product->imageUrl('thumb')`,
`$product->price`/`price2`), і т. д. — **без змін у цих файлах**.

Виняток — `product.blade.php:23`: `@if($product->img)` перевіряє існування
одиночного поля, якого більше нема. Замінити на `@if($product->images)`
(непорожній масив). Правка тривіальна й не змінює верстку.

> **Увага до `$appends`.** `price`/`image` тепер обчислювані, і при серіалізації
> моделі без завантаженого `cheapestItem` кожен рядок списку зробить окремий
> запит (N+1). Тому скрізь, де товари вибираються списком, `cheapestItem`
> має бути в `with(...)` — це закладено в `BuildProductsQuery` (§5).

## 5. SQL-запити, що читають `price`/`img` напряму

Акцесори (§4) не рятують місця, де `price` використовується в `WHERE`,
`ORDER BY`, `MIN()`/`MAX()` на рівні білдера — там колонки фізично не існує.
Додаємо в `Product` скоуп, який підмішує ціну найдешевшої видимої модифікації
як звичайну колонку `price`:

```php
public function scopeWithCheapestItemPrice($query)
{
    return $query
        ->leftJoinSub(
            ProductItem::query()
                ->select('shop_product_id')
                ->selectRaw('MIN(price) as price')
                ->where('display', true)
                ->whereNotNull('price')
                ->groupBy('shop_product_id'),
            'cheapest_item',
            'cheapest_item.shop_product_id',
            '=',
            'shop_products.id',
        )
        ->select('shop_products.*')
        ->addSelect('cheapest_item.price as price');
}
```

Чому саме `leftJoinSub` з агрегатом, а не join по `cheapestItem`: нам потрібна
одна ціна на товар для `WHERE`/`ORDER BY`, і `MIN(price)` з `GROUP BY` дає
рівно один рядок на товар — без ризику дублів у пагінації.

`price2` та `imgs` у join **не тягнемо**: `MIN(price2)` семантично неправильний
(це не «друга ціна найдешевшої модифікації»), а по фото ніде не фільтруємо й
не сортуємо. Обидва беруться з акцесорів через `cheapestItem` (§4).

Куди застосувати:

- `app/Modules/Shop/Queries/Product/BuildProductsQuery.php` — додати
  `->withCheapestItemPrice()` і `cheapestItem` у дефолтний `$with`:
  `$with = ['brand', 'category', 'cheapestItem']`.
  Це автоматично «полагодить» усе, що будується поверх нього:
  `RangeFilterData::apply()` (`where('price', ...)`),
  `PrepareShopFilterQuery::prepareRangeFilter()` (`min('price')`/`max('price')`),
  `SearchProductsQuery::applySorting()` (`orderBy('price', ...)` через `SortOptionEnum`).
  Ці три файли **не чіпаємо взагалі**.

- ⚠️ `app/Modules/Shop/Queries/Product/PrepareShopFilterQuery.php::prepareBrandFilter()` —
  **потребує правки**. Зараз там `$query->selectRaw('brand_id, COUNT(*) as total')->groupBy('brand_id')`,
  і це працює лише тому, що `BuildProductsQuery` не задає жодного `select`.
  Після скоупа в списку select уже будуть `shop_products.*` + `cheapest_item.price`,
  і `selectRaw` допише свої колонки поверх — під `ONLY_FULL_GROUP_BY` запит
  впаде. Виправлення: явно скинути select перед агрегатом —
  `$query->select('brand_id')->selectRaw('COUNT(*) as total')->groupBy('brand_id')`
  (`select()` замінює попередні колонки, `selectRaw()` — дописує).
  `prepareRangeFilter()` правити не треба: `min()`/`max()` в Query Builder і так
  очищають список колонок перед агрегатом.

- `app/Filament/Dashboard/Queries/Shop/GetCatalogStatsQuery.php::incomplete()` —
  переписати без залежності від join (цей самий метод використовується як
  фільтр таблиці в Filament, де скоупа немає):

```php
public static function incomplete(Builder $query): Builder
{
    return $query->where(fn (Builder $q) => $q
        ->whereDoesntHave('items', fn (Builder $i) => $i->where('display', true)->whereNotNull('price'))
        ->orWhereDoesntHave('items', fn (Builder $i) => $i->where('display', true)->whereNotNull('imgs')));
}
```

  Тобто «неповний» товар — це товар без жодної видимої модифікації з ціною
  **або** без жодної видимої модифікації з фото. Нюанс: `whereNotNull('imgs')`
  не відсіює порожній масив `'[]'` — за потреби додати `->where('imgs', '!=', '[]')`.

- `app/Modules/Shop/Queries/Product/FindProductByIdQuery.php` — сторінці товару
  join не потрібен, потрібні самі модифікації для радіобатонів:
  `->with(['brand', 'cheapestItem', 'visibleItems'])`.

- `app/Filament/Dashboard/Queries/Shop/GetTopProductsQuery.php` — читає лише `id`
  товару, у join не бере участі; **не чіпаємо**. Але віджет
  `app/Filament/Widgets/Shop/TopProductsWidget.php` може виводити колонку ціни —
  перевірити: якщо там `TextColumn::make('price')`, то це вже акцесор, і колонка
  стане несортовною по SQL; за потреби додати `->withCheapestItemPrice()` до
  білдера у `GetTopProductsQuery::handle()`.

## 6. Адмінка: форма товару (`ProductResource`)

Файл: `app/Filament/Resources/Shop/Products/ProductResource.php`.

- Прибрати з форми поля `price`, `price2`, `FileUpload::make('img')`,
  `FileUpload::make('imgs')`.
- Додати `Repeater::make('items')->relationship('items')`:

```php
use App\Modules\Shop\Models\ProductItem;
use Filament\Forms\Components\Repeater;

Repeater::make('items')
    ->relationship('items')
    ->columnSpanFull()
    ->minItems(1)
    ->defaultItems(1)
    ->orderColumn('rank')          // drag&drop пише порядок у rank
    ->addActionLabel(__('admin.fields.add_modification')) // новий ключ у lang/*/admin.php
    ->schema([
        TextInput::make('title')
            ->columnSpan(6),
        TextInput::make('price')
            ->numeric()
            ->columnSpan(2),
        TextInput::make('price2')
            ->numeric()
            ->columnSpan(2),
        Toggle::make('display')
            ->label(__('admin.fields.display'))
            ->default(true)
            ->inline(false)
            ->columnSpan(2),
        FileUpload::make('imgs')
            ->image()
            ->multiple()
            ->reorderable()
            ->panelLayout('grid')
            ->imagePreviewHeight('150')
            ->visibility('public')
            ->columnSpanFull()
            ->getUploadedFileNameForStorageUsing(function ($file, $record) {
                return ($record ?? new ProductItem)->getFilePath(
                    $file->getClientOriginalName(),
                    'imgs',
                );
            }),
    ])
    ->columns(12),
```

`minItems(1)` + `defaultItems(1)` дають вимогу «як мінімум одна модифікація».
Яка модифікація потрапляє у списки товарів, визначає ціна (§4) — окремого
поля-перемикача у формі немає.

Колонка ціни в таблиці ресурсу (`TextColumn::make('price')->money()->sortable()`)
тепер читає акцесор. Оскільки Filament сортує по SQL, `->sortable()` на ній
зламається — або прибрати `sortable()`, або в `ProductResource::table()` додати
до `$table->modifyQueryUsing(fn ($query) => $query->withCheapestItemPrice())`.
Друге краще: колонки `price` й сортування працюють як раніше.

## 7. Кошик і замовлення: `item_id`

### Міграції

Колонка дописується прямо в існуючі `Schema::create(...)`, одразу після
`shop_product_id` (нових файлів не створюємо, див. §2.1):

- `app/Modules/ShopCart/database/migrations/0001_01_01_000020_create_shop_cart_products_table.php`
- `app/Modules/ShopOrder/database/migrations/0001_01_01_000018_create_shop_order_products_table.php`

```php
$table->foreignId('shop_product_item_id')
    ->nullable()
    ->constrained('shop_product_items')
    ->nullOnDelete();
```

`nullable` — щоб видалення модифікації не ламало історичні рядки кошика/
замовлень (знімок `product_name`/`selling_price` там і так вже є).

Обидва файли (`...000018`, `...000020`) виконуються **після** `...000016`, де
тепер створюється `shop_product_items`, тож FK коректно резолвиться.

### Моделі

- `App\Modules\ShopCart\Models\CartProduct` і `App\Modules\ShopOrder\Models\OrderProduct` —
  додати `shop_product_item_id` в `$fillable`, relation `productItem()` →
  `belongsTo(ProductItem::class, 'shop_product_item_id')`.

### Валідація

`app/Modules/ShopCart/Http/Requests/ChangeCartRequest.php` — додати:

```php
'item_id' => ['required', 'integer', 'exists:shop_product_items,id'],
```

(разом з існуючим `id` товару; у контролері перевірити, що `item_id` дійсно
належить товару `id`, а не довільній модифікації іншого товару — див. нижче
`$product->items()->findOrFail(...)`).

### Дії/контролер

- `app/Modules/ShopCart/Actions/AddOrUpdateCartItemAction.php` — сигнатура
  `handle(Cart $cart, Product $product, ProductItem $item, int $quantity)`,
  `selling_price` рахувати з `$item->price` (а не `$product->price`),
  `updateOrCreate` ключ — `['cart_id' => ..., 'shop_product_id' => ..., 'shop_product_item_id' => $item->id]`
  — окремий рядок кошика на кожну модифікацію.
- `app/Modules/ShopCart/Http/Controllers/Web/AjaxCartController.php::change()` —
  дістати `$item = $product->items()->findOrFail($request->input('item_id'))`
  (саме через relation товару — це і є перевірка приналежності) і прокинути в
  усі `handle*` виклики поряд з `$product`.
- `app/Modules/ShopOrder/Actions/CreateOrderAction.php` +
  `app/Modules/ShopOrder/Dtos/OrderItemData.php` — додати `shop_product_item_id`
  в DTO і в `$order->items()->create([...])`. Джерело значення — рядки кошика
  (перевірити `app/Modules/ShopOrder/Actions/PlaceOrderFromCartAction.php`, що
  мапить `CartProduct` → `OrderItemData`, і додати туди поле).
- `app/Modules/ShopCart/Actions/RemoveCartItemAction.php` — зараз видаляє
  `->where('shop_product_id', $product->id)`, тобто **всі** модифікації товару.
  Оскільки модифікацій тепер може бути кілька в одному кошику, метод має
  приймати `ProductItem $item` і фільтрувати ще й по `shop_product_item_id`.
  Відповідно у `AjaxCartController` виклики `handleDeleteModal`/`handleDeleteCheckout`
  теж отримують `$item`, а селектор рядка в `handleDeleteCheckout`
  (`.checkout_item[data-product="..."]`) має адресувати рядок по id модифікації,
  а не товару — інакше видалення однієї модифікації прибере з DOM іншу.
- `app/Modules/ShopCart/Actions/MergeCartOnAuthAction.php`,
  `DuplicateCartAction.php` — перевірити місця, що дедуплікують рядки по
  `shop_product_id`: ключем має стати пара `shop_product_id + shop_product_item_id`.

### Фронт (Blade)

- `resources/views/modules/shop/partials/product_card.blade.php` — у форму «в кошик»
  додати `<input type="hidden" name="item_id" value="{{ $product->cheapestItem?->id }}">`
  (картка каталогу завжди кладе найдешевшу модифікацію — ту саму, чию ціну
  й фото показує).
- `resources/views/modules/shop/product.blade.php` — див. §8 (радіобатони або
  приховане поле).
- Будь-де ще, де є форма з `action="{{ save_route('change_product_count') }}"`
  (перевірити `product-box.blade.php`, `cart_modal_item`, сторінку кошика й
  чекаут через `grep -rl "change_product_count" resources/views`) — там треба
  прокидати `item_id` з рядка кошика (`$item->shop_product_item_id`), а не
  найдешевшу модифікацію.

## 8. Сторінка товару: одна модифікація — без змін, кілька — радіобатони

`app/Modules/Shop/Http/Controllers/Web/IndexController.php::product()` отримує
`$product` з підвантаженими `cheapestItem` і `visibleItems` (§5). Шаблон і далі
читає `$product->price`, `$product->price2`, `$product->image` через акцесори —
для товару з однією модифікацією верстка не змінюється взагалі, крім
`@if($product->img)` → `@if($product->images)` (§4).

У формі додавання в кошик (`product.blade.php`, приблизно рядки 92–142):

```blade
@php($items = $product->visibleItems)

@if($items->count() > 1)
    <div class="product-modifications mb-4">
        <h6 class="option-title">{{ t('shop.modification') }}:</h6>

        @foreach($items as $item)
            <label class="modification-option">
                <input
                    type="radio"
                    name="item_id"
                    value="{{ $item->id }}"
                    data-price="{{ $item->price }}"
                    data-price2="{{ $item->price2 }}"
                    data-image="{{ $item->image }}"
                    @checked($loop->first)
                >
                <span>
                    {{ $item->title ?: $product->title }}
                    — {{ t('shop.currency') }}{{ $item->price }}
                </span>
            </label>
        @endforeach
    </div>
@else
    <input type="hidden" name="item_id" value="{{ $items->first()?->id }}">
@endif
```

Перший радіобатон (найдешевший, бо `visibleItems` відсортовані по ціні) —
обраний за замовчуванням, тож ціна у блоці `.product-price-container` збігається
з тією, що показана в каталозі.

**Обов'язково додати JS** на зміну радіобатона: інакше користувач обере дорожчу
модифікацію, а на сторінці й далі висітиме ціна найдешевшої — це буде виглядати
як помилка ціни. Мінімум — оновлювати `.current-price` / `.original-price`
з `data-price`/`data-price2`; за бажанням ще й `.main-image` з `data-image`.
Місце для скрипта — там же, де вже живе логіка `.quantity-btn` на цій сторінці
(знайти через `grep -rn "quantity-btn" resources/js resources/views`).

Ще одна деталь у контролері: приватний метод `cartQuantityFor(int $productId)`
шукає кількість у кошику по товару. Оскільки рядків кошика на товар може бути
кілька (по одному на модифікацію), метод має приймати ще й `item_id` і фільтрувати
по `shop_product_item_id` — інакше в поле кількості потрапить значення від
іншої модифікації. Найпростіше: брати кількість для тієї модифікації, яка
обрана за замовчуванням (`$items->first()`), або `1`, якщо її в кошику немає.

## 9. Сідери й фабрики

- `app/Modules/Shop/database/seeders/ShopProductSeeder.php` — `Product::create([...])`
  більше не отримує `price`/`img`; натомість створюємо модифікації. Щоб одразу
  перевірити обидві гілки UI, частині товарів дати 1 модифікацію, а частині —
  2–3 з різними цінами:

```php
$product = Product::create([
    'title' => fake()->words(3, true),
    'slug' => fake()->slug(),
    'category_id' => fake()->randomElement([2,3,4,5,7,8,9,10]),
    'brand_id' => fake()->numberBetween(1, 4),
]);

// кожен третій товар — з кількома модифікаціями, решта — з однією
$itemsCount = $i % 3 === 0 ? fake()->numberBetween(2, 3) : 1;

for ($j = 0; $j < $itemsCount; $j++) {
    $product->items()->create([
        'display' => true,
        'rank' => $j,
        'title' => $itemsCount > 1 ? fake()->randomElement(['S', 'M', 'L', 'XL']) : null,
        'price' => fake()->numberBetween(30, 300),
        'imgs' => [$dir . $filename],
    ]);
}
```

  (файли переносити в `shop_product_items/{item_id}/imgs/...` замість
  `shop_products/{product_id}/img/...`; `Storage::disk('public')->deleteDirectory(...)`
  на початку сідера теж має чистити `shop_product_items`, а не тільки
  `shop_products`. Узгодити зі `getFilePath()` у `ProductItem`.)

  Стежити, щоб у товарів з кількома модифікаціями `title` не був `null` —
  інакше радіобатони підпишуться однаковою назвою товару.

- `database/factories/ProductFactory.php` — прибрати `price`/`price2` з
  `definition()`; додати нову `database/factories/ProductItemFactory.php` і
  стейт/хук, який створює хоча б одну модифікацію:
  `afterCreating(fn ($product) => ProductItem::factory()->for($product, 'product')->create())`
  або окремий явний стейт `Product::factory()->withItem()` — обрати за тим, що
  зручніше існуючим тестам (частина з них створює товари й одразу читає ціну).
- `app/Modules/ShopOrder/database/seeders/ShopOrderSeeder.php:96` —
  `selling_price: $product->price` формально працюватиме через акцесор (§4),
  але правильніше брати `$product->cheapestItem->price` і одразу класти
  `shop_product_item_id`, щоб демо-замовлення мали заповнений FK.
- `database/factories/CartProductFactory.php` і
  `database/factories/OrderProductFactory.php` — додати `shop_product_item_id`
  (`ProductItem::factory()` або `null`, якщо конкретна модифікація тесту не важлива).

## 10. Чек-лист файлів (створити/змінити)

**Нові файли:**

- `app/Modules/Shop/Models/ProductItem.php`
- `database/factories/ProductItemFactory.php`

**Змінити (міграції — існуючі файли, нових не додаємо):**

- `app/Modules/Shop/database/migrations/0001_01_01_000016_create_shop_products_table.php`
- `app/Modules/ShopOrder/database/migrations/0001_01_01_000018_create_shop_order_products_table.php`
- `app/Modules/ShopCart/database/migrations/0001_01_01_000020_create_shop_cart_products_table.php`

**Змінити (код):**

- `app/Modules/Shop/Models/Product.php`
- `app/Modules/Shop/Queries/Product/BuildProductsQuery.php`
- `app/Modules/Shop/Queries/Product/PrepareShopFilterQuery.php` (скинути select у `prepareBrandFilter`, §5)
- `app/Filament/Dashboard/Queries/Shop/GetCatalogStatsQuery.php`
- `app/Modules/Shop/Queries/Product/FindProductByIdQuery.php`
- `app/Filament/Resources/Shop/Products/ProductResource.php`
- `app/Filament/Widgets/Shop/TopProductsWidget.php` (перевірити колонку ціни, §5)
- `app/Modules/Shop/Http/Controllers/Web/IndexController.php` (`cartQuantityFor`, §8)
- `app/Modules/ShopCart/Http/Requests/ChangeCartRequest.php`
- `app/Modules/ShopCart/Http/Controllers/Web/AjaxCartController.php`
- `app/Modules/ShopCart/Actions/AddOrUpdateCartItemAction.php`
- `app/Modules/ShopCart/Actions/RemoveCartItemAction.php`
- `app/Modules/ShopCart/Actions/MergeCartOnAuthAction.php`, `DuplicateCartAction.php` (дедуплікація по парі id, §7)
- `app/Modules/ShopCart/Models/CartProduct.php`
- `app/Modules/ShopOrder/Models/OrderProduct.php`
- `app/Modules/ShopOrder/Actions/CreateOrderAction.php`
- `app/Modules/ShopOrder/Actions/PlaceOrderFromCartAction.php`
- `app/Modules/ShopOrder/Dtos/OrderItemData.php`
- `resources/views/modules/shop/product.blade.php`
- `resources/views/modules/shop/partials/product_card.blade.php`
- `resources/views/modules/shop/partials/product_box.blade.php` (перевірити, чи є там форма кошика)
- решта в'юх з `change_product_count` (кошик, чекаут, `cart_modal_item`) — прокинути `item_id`
- JS сторінки товару (перемикання ціни/фото по радіобатону, §8)
- `app/Modules/Shop/database/seeders/ShopProductSeeder.php`
- `app/Modules/ShopOrder/database/seeders/ShopOrderSeeder.php`
- `database/factories/ProductFactory.php`
- `database/factories/CartProductFactory.php`
- `database/factories/OrderProductFactory.php`
- `lang/*/admin.php` — ключі `add_modification` тощо
- `lang/en/dictionary.php` — секція `shop`, ключ `modification` (саме звідти
  `t('shop.*')` бере фолбек; поверх нього Translation-модуль накладає переклади
  з БД, тож після правки чистити кеш `translations.{locale}`)

**Тести, що варто перевірити/оновити** (не забути додати нові тестові файли в git —
правило з `CLAUDE.md`):

- `tests/Feature/Filament/Shop/ProductResourceTest.php`
- `tests/Unit/ShopCart/AddOrUpdateCartItemActionTest.php`
- `tests/Unit/ShopCart/RemoveCartItemActionTest.php`
- `tests/Unit/ShopCart/MergeCartOnAuthActionTest.php`
- `tests/Feature/ShopCart/AjaxCartControllerTest.php`
- `tests/Unit/ShopOrder/CreateOrderActionTest.php`
- `tests/Unit/ShopOrder/OrderDtoTest.php`
- `tests/Feature/Shop/ProductReviewsTest.php` (створює товари фабрикою)
- `tests/Unit/Filament/Dashboard/GetCartStatsQueryTest.php`
- новий тест на вибір найдешевшої модифікації (`cheapestItem` + `withCheapestItemPrice`)

## 11. Що свідомо НЕ робимо зараз

- Не чіпаємо `article` — лишається на `Product`, не переїжає в модифікацію.
- Не робимо складніший UI вибору (свотчі кольорів, залежні селекти) — тільки
  плоский список радіобатонів.
- Не пишемо data-міграцію для перенесення існуючих `price`/`img` у модифікації:
  це стартер, база перезаливається через `migrate:fresh --seed` (§2.1).
- Не додаємо нових файлів міграцій — правимо існуючі три (§2.1).
- Не робимо облік залишків/наявності по модифікаціях — поле `display` поки
  єдиний спосіб приховати варіант.

## 12. Порядок виконання (коли даватимемо команду реалізовувати)

1. Правка міграції `...000016`: прибрати `price/price2/img/imgs` з `shop_products`,
   створити там же `shop_product_items`; модель `ProductItem` + фабрика.
2. Правка міграцій `...000018` і `...000020`: колонка `shop_product_item_id`.
3. `Product` — прибрати старі поля, додати `items()`/`visibleItems()`/`cheapestItem()`,
   акцесори та скоуп `withCheapestItemPrice()`.
4. Полагодити SQL-місця (§5): `BuildProductsQuery`, `PrepareShopFilterQuery`,
   `GetCatalogStatsQuery`, `FindProductByIdQuery`.
5. Filament-форма товару (репітер модифікацій) + `modifyQueryUsing` для сортування по ціні.
6. Кошик/замовлення: моделі, `ChangeCartRequest`, `AjaxCartController`,
   `AddOrUpdateCartItemAction`, `RemoveCartItemAction`, `CreateOrderAction`/`OrderItemData`.
7. Blade: `product-card`, `product`, радіобатони + JS перемикання ціни, решта форм з `item_id`.
8. Сідери й фабрики (частина товарів — з кількома модифікаціями).
9. `php artisan migrate:fresh --seed` — перевірити, що все піднімається з нуля,
   і що каталог показує ціну найдешевшої модифікації.
10. Прогнати й актуалізувати тести (`php artisan test`, без Sail — локальний PHP 8.4 хост).

## 13. Відкриті питання — уточнити ПЕРЕД стартом

### A. Блокуючі (впливають на схему й на всі запити)

**A1. За якою ціною рахувати «найдешевшу» модифікацію — `price` чи `price2`?**

У вітрині `price2` — це **актуальна знижена** ціна, а `price` — закреслена стара
(`product-card.blade.php:24-29`, `product.blade.php:65-77`, `product-box.blade.php:57-60`:
`@if($product->price2)` → `current-price = price2`, `original-price = price`).

При цьому `AddOrUpdateCartItemAction` кладе в кошик `selling_price = $product->price`,
тобто **стару** ціну. Тобто вже зараз при заповненому `price2` покупець бачить одну
ціну, а в кошик потрапляє інша — це існуюче розходження, і зараз воно стає
критичним, бо від вибору ціни залежить, яка модифікація вважається найдешевшою.

- (а) фактична ціна = `price2 ?? price`: `cheapestItem` шукає `MIN(COALESCE(price2, price))`,
  скоуп `withCheapestItemPrice()` теж рахує по `COALESCE`, у кошик іде `price2 ?? price`.
  Заодно лагодимо розходження ціни на вітрині й у кошику.
- (б) лишити як є: рахуємо все по `price`, розходження не чіпаємо.

**Рекомендація: (а).** Але це виходить за межі задачі «модифікації» (правимо
існуючу поведінку кошика), тому потрібне явне рішення.

**A2. `article` (SKU) — лишити на товарі чи перенести в модифікацію?**

Зараз `article` на `shop_products`. У класичній схемі варіантів SKU належить
саме варіанту (різні розміри — різні артикули), і `shop_cart_products.product_article`
зберігає його знімок. Якщо переносити — це ще одна колонка в `shop_product_items`
і правка `ProductResource` (таблиця/фільтри) та `AddOrUpdateCartItemAction`.

**Рекомендація: перенести** — раз уже робимо «на виріст». Але це не було в
початковій вимозі, тож питаю.

**A3. Чи можуть кілька модифікацій одного товару лежати в кошику одночасно?**

- (а) окремі рядки: `updateOrCreate` по парі `shop_product_id + shop_product_item_id`;
- (б) одна модифікація на товар: вибір іншої замінює рядок.

**Рекомендація: (а)** — інакше сама ідея модифікацій у кошику не має сенсу.
Від цього залежить §7 (ключ `updateOrCreate`, `RemoveCartItemAction`,
`MergeCartOnAuthAction`) і §8.

### B. Форма й дані

**B4. `price` у модифікації — обов'язкова?** Якщо так (`->required()` у репітері),
то «неповний товар» (§5) спрощується до «немає фото», і `cheapestItem` ніколи не
буде `null` у товару з модифікаціями.

**B5. Фото — обов'язкові в кожній модифікації?** І що показувати, якщо в обраної
модифікації фото немає — фолбек на фото найдешевшої/першої модифікації товару,
чи одразу `no_image.svg`?

**B6. `title` модифікації — обов'язковий, коли модифікацій ≥ 2?** Зараз nullable.
Якщо його не заповнити при двох модифікаціях, радіобатони підпишуться однаково
(назвою товару) — користувач не зрозуміє, що обирає. Варіанти: зробити
`required()` завжди; валідувати лише при 2+; або підписувати радіобатон
`title ?: ціна`.

### C. Фронт

**C7. Що саме перемикати при виборі радіобатона** — тільки ціну, чи ще й головне
фото (і галерею) на фото обраної модифікації? (§8; на ціну — обов'язково, інакше
буде виглядати як помилка ціни).

**C8. Кошик і чекаут: рядки зараз адресуються по id товару.**
`cart/index.blade.php:40,52,71,78` і `cart_modal_item.blade.php:22,40` використовують
`data-product="{{$item->product->id}}"` / `name="id"`, а `AjaxCartController`
будує селектор `.js_product_checkout_total_{$product->id}`. З двома модифікаціями
одного товару в кошику ці селектори колізять (дві однакові `data-product`) —
треба перевести їх на `shop_product_item_id`. Сам JS (`public/assets/js/app.js`)
загальний і правок не потребує. Підтвердити, що йдемо цим шляхом (залежить від A3).

### D. Розширення контракту (можна відкласти)

**D9. API `ProductResource`** (`app/Modules/Shop/Http/Resources/ProductResource.php`) —
додавати масив модифікацій (`items: [{id, title, price, images}]`), чи лишити
плоскі `price`/`image` від найдешевшої? Мобільному застосунку/фронту модифікації
знадобляться, але це зміна контракту API.

**D10. Filament `CartResource` / `OrderResource`** — показувати в рядках назву
обраної модифікації? Зараз показують `product_name`. Мінімально — не чіпаємо.
