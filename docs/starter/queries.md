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

## Why data, not a builder

A tempting alternative is for every Query to assemble criteria and hand back the builder,
leaving `first()` / `get()` / `count()` to the caller. This project deliberately does not
work that way, and the reasons are worth writing down because the question keeps coming
back.

**A builder cannot be cached.** `GetAllPagesQuery` caches the visible pages for 24h and
`PageObserver` invalidates them — see «Caching» below. Only an executed read has a result
to store, so «caching belongs inside the Query» and «Queries return builders» cannot both
be true.

**Eager-loading would move to the caller.** `GetUserOrdersQuery` loads
`items.product.category` because it knows what its result is for (rule 3 below). That is
also what makes rule 4 safe: a ViewModel never queries, so it depends on the Query having
loaded everything the screen touches. Hand out a builder and every caller has to remember
its own `->with()`; the one that forgets ships an N+1 inside a blade loop.

**A builder is a write surface.** `->delete()`, `->update()`, `->increment()`. «A Query
never changes state» holds exactly as long as the builder does not leave the class.

**Not every read is a builder.** `GetTranslationsByLocaleQuery` returns a cached `array`,
`GetProductRatingBreakdownQuery` an aggregate `array`, `GetPageBreadcrumbsQuery` walks a
nested set, `CountCartItemsQuery` returns an `int`. A builder-only rule would either break
these or split the family into two incompatible kinds, at which point it stops being a
rule.

**Web and API would drift.** The DTO input exists so one Query serves both
(`SearchProductsQuery` ← `ShopController` and `Shop\...\Web\IndexController`). If the
caller finishes the read, one paginates by 12 and the other by 24 and nobody notices. The
page size is passed *in* through `ProductSearchData::$perPage`, while the list of offered
sizes stays inside the Query — so a made-up `?c=1000` cannot turn a listing into a full
table scan, whoever the caller is.

**«One read per class» would become «one table per class».** `FindCartQuery` and
`CountCartItemsQuery` would collapse into a single `BuildCartQuery` everyone finishes
differently. That is a repository, and this project does not have them.

### When a builder is the right answer

When the code that finishes the read is *code*, not a person reading a controller:

- **Composition inside the read layer.** `BuildProductsQuery` is consumed only by
  `SearchProductsQuery` and `PrepareShopFilterQuery` — no controller injects it. Facet
  counts rebuild the same query N times, each time excluding one filter; on materialised
  data that is impossible.
- **Filament.** `modifyQueryUsing` takes a builder; a Collection will not do.
- **`chunk()` / `lazy()`** for an export that must not be materialised.

The discipline: a builder is allowed, the **name has to say so out loud** (`Build*Query`),
and its consumer is another Query or a framework surface that demands a builder — never a
controller. A controller gets data.

---

## Rules

1. **One read per class**, a single public `handle()`.
2. **Input is scalars, enums, models or a DTO — never a `Request`.** Several criteria go
   into a DTO, so the same Query serves web and API:
   ```php
   SearchCategoriesQuery::handle(CategorySearchData $criteria): Collection
   GetUserOrdersQuery::handle(int $userId, OrderFilterData $filter): LengthAwarePaginator
   ```
   No Query in the codebase takes a `Request` — `PrepareShopFilterQuery` was the last one
   and now takes `ShopFilterCriteriaData`, built by the controller.
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
