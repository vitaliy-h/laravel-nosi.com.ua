# Admin Dashboard

The admin dashboard is the Filament panel's landing page: a global period filter
plus a set of widgets built on top of it.

---

# Where the code lives

All dashboard code lives under `app/Filament`, **never** in `app/Modules`:

```text
app/Filament/
├── Pages/Dashboard.php          # the page, period filter form
├── Widgets/                     # widgets, grouped by domain
│   ├── NeedsAttentionWidget.php
│   ├── Shop/
│   ├── System/
│   └── Users/
└── Dashboard/                   # everything the widgets need
    ├── Concerns/                # FormatsDashboardStats, InteractsWithDashboardPeriod
    ├── Dtos/
    │   ├── DashboardPeriodData.php   # the resolved period filter
    │   ├── Shop/                     # *StatsData, SalesChartData
    │   ├── System/
    │   └── Users/
    ├── Queries/                 # Get*Query — one per widget
    │   ├── Shop/
    │   ├── System/
    │   └── Users/
    └── Support/DashboardSeries.php   # time series helper for charts
```

The subfolders under `Dashboard/Dtos` and `Dashboard/Queries` mirror the widget
folders, so a widget and the query behind it are easy to pair up.

---

# The rule

**Dashboard-only code does not go into `app/Modules`.**

A stats query that exists purely to fill an admin widget is not domain logic: the
frontend, the API and the modules themselves never call it. Putting it in
`app/Modules/Core` (or in `Shop`, `ShopOrders`, …) makes those modules look like
they own behaviour they do not.

What *does* stay in the modules: models, enums, domain queries and actions the
site itself uses. Dashboard queries read them — `Order`, `Product`, `Log`,
`PaymentStatusEnum`, `ModuleGate` — but never live next to them.

---

# Data flow

```text
Dashboard page (filters form: period / from / to)
  → InteractsWithDashboardPeriod  →  DashboardPeriodData
      → widget  →  Get*Query  →  *StatsData
          → FormatsDashboardStats  →  stat cards
```

`DashboardPeriodData` is the only place the raw `$pageFilters` payload is turned
into dates. It never throws: a broken value in the URL or session falls back to
the default 30-day window instead of taking the dashboard down. Alongside the
selected window it carries the previous window of the same length, which the stat
cards use to render the percentage change.

`DashboardPeriodData::cacheKey()` is meant to be part of a widget's cache key —
the cache driver is `database`, tags are unavailable, so the period has to be
spelled out inside the key itself.

---

# Adding a widget

1. **Query** — `app/Filament/Dashboard/Queries/{Domain}/Get…Query.php`, taking
   `DashboardPeriodData` if the number changes with the period.
2. **DTO** — `app/Filament/Dashboard/Dtos/{Domain}/…Data.php`
   (`Spatie\LaravelData\Data`) for the query's result.
3. **Widget** — `app/Filament/Widgets/{Domain}/…Widget.php`. Add
   `InteractsWithDashboardPeriod` only if the widget really recalculates per
   period: `$pageFilters` is reactive, so pulling it in makes the widget
   re-render on every filter change. Add `FormatsDashboardStats` for stat cards.
4. **Module gate** — if the widget belongs to a switchable module, guard it:

   ```php
   public static function canView(): bool
   {
       return ModuleGate::isEnabled('shop_orders');
   }
   ```

   See [Disabling Modules](../starter/modules.md).
5. **Translations** — labels go under the `dashboard` key in `lang/{locale}/admin.php`.
6. **Tests** — query tests in `tests/Unit/Filament/Dashboard`, page and widget
   tests in `tests/Feature/Filament/Dashboard`.

---

[Back to Filament readme](README.md)
