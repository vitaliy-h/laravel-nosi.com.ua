### Architecture notes

* koten4ik: Remove Observers — they're magic that hurts code readability and are an anti-pattern at this point. Replace them with events that get dispatched from actions.

* koten4ik: Rename Actions to Commands.
  * sokol: keep "Action". In Laravel, "Command" is already associated with Artisan console commands (`Illuminate\Console\Command`, project already has `Console/Commands/*Command.php` — `ImagesClearCommand`, `GeoDownloadCommand`, etc). Naming business logic "Command" too collides with that — `CreateOrderCommand` reads ambiguous (Artisan command or business op?). "Action" is the established Laravel convention instead (Jetstream/Fortify use it). Chose Action for this reason.

* koten4ik: Data going from a command to a view should go through a ViewModel — it's fed an Eloquent model and outputs an array of prepared fields ready for rendering.

* koten4ik: Remove DTOs as well — in Laravel they're overkill, this is also usually used in enterprise-leaning projects.

* koten4ik: All models and controllers should extend a base Model and a base Controller.
  * sokol: open question — what logic exactly would live in the base Model? Not specified yet, proposal has no content without it.
  * sokol: base Model does not replace traits here. Current model traits are selective, not universal — none is used by every model: `HasVisibility` (8 models), `HasSeoFields` (5: `Product`, `Brand`, `Category`, `News`, `Page`), `HasImage` (5), `HasUuid` (2: `Order`, `Basket`), `CascadesDeletes` (2), `HasBuilderContent` (1, only `Page`). Baking e.g. `HasSeoFields` into base Model would give SEO fields to every model including ones that don't need it (`GeoCity`, `Basket`...), or force a `BaseModel → BaseSeoModel → BaseVisibilitySeoModel...` chain per combination — worse than traits. Package traits (`NodeTrait` from nestedset, `HasRoles`/`HasApiTokens` from spatie, `Notifiable`, `HasFactory`) can't be folded into a base Model at all regardless. Traits exist in PHP precisely for this — mixin composition without multiple inheritance; base Model solves a different problem (shared contract for *all* models). Base Model only makes sense for logic every single model needs — none found in the current codebase.
  * sokol: base Controller already exists (`App\Http\Controllers\Controller`), all controllers already extend it, `BaseApiController` is already a layer on top — this part of the point is already satisfied unless specific shared logic is meant (then name it).

* sokol: For list/collection Query classes (e.g. `GetProductsByCategoryQuery`, `GetBestSellersQuery`, `GetFeaturedProductsQuery`) consider consolidating into fewer "search" Query classes that accept a filters DTO and perform the selection, instead of many very simple single-purpose Query classes as currently in `app/Modules/Shop/Queries`. Keep single-record finder queries (`FindProductByIdQuery`, `FindProductBySlugQuery`, `FindCategoryBySlugQuery`) as-is — wrapping a single `WHERE` in a filter DTO adds indirection without benefit.

* sokol: Nest Query classes by entity subfolder (`Queries/Product/`, `Queries/Category/`) instead of a flat `Queries/` directory — on the `app/Modules/Shop/Queries` example it's already hard to orient with 13 files in one folder. Pair with a strict naming pattern `{Verb}{Entity}By{Criteria}Query` (e.g. `GetProductsByCategoryQuery`, `FindCategoryBySlugQuery`) so files sort/search predictably by entity first, then action — makes IDE symbol search (`Cmd/Ctrl+Shift+O`) fast regardless of file count, without needing a separate index file.

* sokol: `config('modules.*')` module on/off switch works badly today — every place that cares about a module has to remember to add its own `if (config('modules.x'))`, and it's checked in some places but not others:
  - `DatabaseSeeder::run()` manually lists every module seeder behind an `if` — forget to add the `if`, or forget the seeder call itself when scaffolding a module, and it breaks (this is exactly what happened with `NewsSeeder` — class wasn't resolvable and there was no guard preventing the crash).
  - `AdminPanelProvider` repeats `config('modules.x') ? NavigationItem::make(...) : null` per module, by hand, for every nav entry.
  - `ModuleServiceProvider::loadModuleRoutes()` and `loadModuleCommands()` don't check `config('modules.*')` at all — routes/commands for a "disabled" module still load, only migrations do (`loadModuleMigrations()`).
  - Disabling a module only via config leaves it half-off; deleting the module folder entirely still leaves dangling config keys and hardcoded nav entries pointing at classes that no longer exist → fatal error instead of silently hiding the menu item.

  **Proposal — single source of truth for "is module active", auto-resolved from disk + config, no per-module `if` anywhere:**

  1. One helper that both facts (folder exists, config flag not explicitly `false`) go through:
     ```php
     // app/Modules/Core/Support/Modules.php
     class Modules
     {
         public static function enabled(string $name): bool
         {
             $name = strtolower($name);

             if (!File::isDirectory(app_path("Modules/" . Str::studly($name)))) {
                 return false; // folder deleted -> module is off, no config edit needed
             }

             return config("modules.{$name}", true); // default enabled if no explicit flag
         }
     }
     ```
     Every place that currently does `config('modules.shop')` switches to `Modules::enabled('shop')` — one call instead of two things to keep in sync (config key + folder presence).

  2. **Migrations/routes/commands** (`ModuleServiceProvider`) — add the same missing check to `loadModuleRoutes()`/`loadModuleCommands()` that `loadModuleMigrations()` already has, via `Modules::enabled($moduleName)`, instead of only migrations being guarded.

  3. **Seeders — auto-discover instead of a manual call list.** Convention: each module's main seeder is named `{Module}Seeder` inside `app/Modules/{Module}/database/seeders/`. `DatabaseSeeder` stops listing modules by hand:
     ```php
     foreach (File::directories(app_path('Modules')) as $module) {
         $name = basename($module);

         if (!Modules::enabled($name)) {
             continue;
         }

         $class = "App\\Modules\\{$name}\\database\\seeders\\{$name}Seeder";

         if (class_exists($class)) {
             $this->call($class);
         }
     }
     ```
     Adding a new module with a `{Module}Seeder` gets seeded automatically — no `DatabaseSeeder` edit, no forgetting the `if`.

     **Order problem:** plain directory scan order isn't reliable for dependencies (e.g. `Shop` needs `Category`/`Brand` before `Product` before `Order`, and `RolesAndPermissionsSeeder` has to run before anything that assigns roles). Auto-discovery alone can silently seed in the wrong order. Fix: let each module seeder optionally declare its own priority, default it if it doesn't care, sort before running:
     ```php
     // app/Modules/Shop/database/seeders/ShopSeeder.php
     class ShopSeeder extends Seeder
     {
         public static int $priority = 20; // lower runs first, default = 100

         public function run(): void
         {
             $this->call(ShopCategorySeeder::class);
             $this->call(ShopBrandSeeder::class);
             $this->call(ShopProductSeeder::class);
             DB::statement('SET FOREIGN_KEY_CHECKS=0;'); // ...
             $this->call(ShopOrderSeeder::class);
             DB::statement('SET FOREIGN_KEY_CHECKS=1;');
         }
     }
     ```
     ```php
     $seeders = collect(File::directories(app_path('Modules')))
         ->map(fn ($module) => basename($module))
         ->filter(fn ($name) => Modules::enabled($name))
         ->map(fn ($name) => "App\\Modules\\{$name}\\database\\seeders\\{$name}Seeder")
         ->filter(fn ($class) => class_exists($class))
         ->sortBy(fn ($class) => $class::$priority ?? 100)
         ->each(fn ($class) => $this->call($class));
     ```
     Inner ordering within one module (categories → brands → products → orders, FK toggling) stays hand-written inside that module's own `{Module}Seeder::run()` — same as today, just moved out of the central `DatabaseSeeder`. Cross-module ordering becomes one `$priority` number per module instead of hand-arranging `if` blocks in a shared file. Global seeders that must run before all modules (`RolesAndPermissionsSeeder`, `LanguageSeeder`...) stay explicit at the top of `DatabaseSeeder`, unaffected by the loop.

  4. **Admin nav** — same idea: instead of a hand-written `config('modules.x') ? NavigationItem::make(...) : null` per entry in `AdminPanelProvider`, wrap the group builder so a disabled/missing module's entry is dropped automatically:
     ```php
     ->navigationItems(array_filter([
         Modules::enabled('news') ? NavigationItem::make(__('admin.tables.news'))->url(NewsResource::getUrl()) : null,
         Modules::enabled('shop') ? ... : null,
         Modules::enabled('geo') ? ... : null,
     ]))
     ```
     Still one line per item (Filament nav items aren't auto-discoverable without a bigger registry), but now it's `Modules::enabled()` everywhere — one place to fix if the on/off logic ever changes (e.g. adding a "coming soon, greyed out" state), and deleting a module folder alone is enough to hide it, no leftover config key to remember.

  Net effect: turning a module off, or physically deleting its folder, is enough on its own — nothing else to update, nothing crashes instead of just not rendering.

### TODO

* Add endpoints for the Telegram bot and the corresponding libraries.

* Upload/update the price list from an Excel file.
 
* User roles in the admin panel. 

* Dynamic product attributes.

* Multilingual support on the website.

* Run migrations and other console commands from the web interface.

* Image rotation.

* Cron jobs.

* URL redirects.

* Canonical URLs.

* Structured data markup.

* Add language handling as the first URL slug to support future multilingual websites.
  Touches `App\Providers\ModuleServiceProvider::registerWebRoute()` (prefix
  resolution) and each module's `Http/Routes/web.php`.



