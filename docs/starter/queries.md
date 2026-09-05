# Queries — the read layer

Every read goes through a **Query** class in `app/Modules/{Module}/Queries/`. There are no
repositories in this project: a controller that needs data injects a Query, and that Query
is the only place the Eloquent call for that read lives.

The split is by intent, not by table:

| | Action | Query |
|---|---|---|
| Answers | "do this" | "what is this?" |
| Changes state | yes | never |
| Returns | `void`, a domain value, or `ActionResult<T>` | a model, `Collection`, `LengthAwarePaginator` or array |
| Lives in | `app/Modules/{Module}/Actions/` | `app/Modules/{Module}/Queries/` |

An Action may call a Query when it needs to read before it writes. A Query never calls an
Action. See `docs/starter/actions.md` for the Action side.

---

## Naming

| Prefix | Returns | Examples |
|---|---|---|
| `Find*Query` | one record, or `null` / `firstOrFail()` | `FindOrderByUuidQuery`, `FindCategoryBySlugQuery`, `FindUserByEmailQuery` |
| `Get*Query` | a known set, no caller-supplied criteria | `GetNewsListQuery`, `GetAllPagesQuery`, `GetUserOrdersQuery` |
| `Search*Query` | a set built from criteria | `SearchProductsQuery`, `SearchCategoriesQuery` |
| `Build*Query` | a query **builder** for another Query to finish | `BuildProductsQuery` |

`Build*` is the one case where returning a builder is allowed — the name says so out loud.
Everything else returns data.

---

## Rules

1. **One read per class**, a single public `handle()`.
2. **Input is scalars, enums, models or a DTO — never a `Request`.** Several criteria go
   into a DTO, so the same Query serves web and API:
   ```php
   SearchCategoriesQuery::handle(CategorySearchDto $criteria): Collection
   GetUserOrdersQuery::handle(int $userId, OrderFilterData $filter): LengthAwarePaginator
   ```
   No Query in the codebase takes a `Request` — `PrepareShopFilterQuery` was the last one
   and now takes `ShopFilterCriteriaDto`, built by the controller.
3. **Eager-load inside the Query**, not in the caller: the Query knows what its result is
   for. `GetUserOrdersQuery` loads `items.product.category` because the order list links
   each line back to the catalogue.
4. **A ViewModel never queries.** It is fed what a Query returned
   (`docs/starter/view-models.md`).
5. **Composition over parameters.** When a second caller needs a different shape, add a
   Query beside the first (`SearchProductsQuery` builds on `BuildProductsQuery`) instead of
   growing one class into a switchboard.

---

## Layout

Flat inside `Queries/` for a small module:

```text
app/Modules/News/Queries/
├── FindNewsBySlugQuery.php
└── GetNewsListQuery.php
```

Grouped per entity once a module has many reads:

```text
app/Modules/Shop/Queries/
├── Category/     # FindCategoryById, FindCategoryBySlug, GetAllCategories,
│                 # GetCategoryBreadcrumbs, SearchCategories
├── Product/      # BuildProducts, FindProductById, PrepareShopFilter, SearchProducts
└── ProductReview/# GetProductRatingBreakdown, GetReviewsByProduct
```

24 Query classes currently live in 9 modules: `News`, `Page`, `PageText`, `Profile`,
`Shop`, `ShopCart`, `ShopOrder`, `Translation`, `User`.

Admin dashboard reads are a separate family under `app/Filament/Dashboard/Queries/`
(`Shop/`, `System/`, `Users/`) — same rules, but they belong to the panel rather than to a
module, because a stats query that exists only to fill a widget is not domain logic. See
`docs/filament/dashboard.md`.

---

## Caching

Caching belongs inside the Query that owns the read, not in its callers.

`GetAllPagesQuery` is the worked example: it caches the visible pages for 24h under
`pages.all`, keeps a per-request copy in a property, and is bound as a **singleton** in
`AppServiceProvider` so one request hits the cache once. Its `clear()` forgets the entry
and runs `route:clear` + `cache:clear`, and `PageObserver` calls that on every page
create/update/delete — which is what keeps DB-driven route prefixes honest
(`docs/starter/routing.md`).

If a Query caches, it owns the invalidation too.

---

## Testing

Query tests are plain Unit tests next to the module they belong to —
`tests/Unit/{Module}/Queries/`, e.g. `tests/Unit/ShopOrder/Queries/FindOrderByUuidQueryTest.php`
and `tests/Unit/Shop/Queries/{Category,Product}/`. They use `RefreshDatabase`, seed with
factories, and assert on what came back rather than on the SQL. Dashboard query tests live
in `tests/Unit/Filament/Dashboard/`. See `docs/starter/testing.md`.

---

[Back to main readme](../../README.md)
