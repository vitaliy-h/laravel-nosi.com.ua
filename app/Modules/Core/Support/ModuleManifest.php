<?php

namespace App\Modules\Core\Support;

use App\Modules\Page\Enums\PageModule;
use App\Modules\Page\Models\Page;
use App\Modules\Page\Queries\FindPageByModuleQuery;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\QueryException;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Str;
use Throwable;
use ValueError;

/**
 * What `ModuleServiceProvider` needs to know about every enabled module, worked
 * out once and cached to `bootstrap/cache/modules.php`.
 *
 * Booting used to do this on every request: list `app/Modules`, stat four paths
 * per module, walk `Console/Commands` even for an HTTP request that will never
 * run one, and ask the database for the page slug behind each module's route
 * prefix. The last one is the reason `route:cache` was a trap — the prefixes
 * are data, and a cached route table freezes whatever the database said at the
 * moment it was written.
 *
 * With a manifest the resolution happens when someone runs `modules:cache`, and
 * the deploy can cache routes right after it. Nothing is cached by default: with
 * no manifest file this builds the same array in memory, so a fresh clone works
 * before anything has been run.
 *
 * @phpstan-type ModuleEntry array{
 *     web: array{path: string, prefix: string, page_module: string|null}|null,
 *     api: string|null,
 *     migrations: string|null,
 *     commands: array<int, class-string>,
 *     observers: array<class-string, class-string<Model>>,
 * }
 */
class ModuleManifest
{
    /**
     * Modules that must stay mounted at the app root instead of getting an
     * auto-derived folder-name prefix: Core owns `/storage/{preset}/{path}`
     * (image previews) and Sitemap owns the SEO-standard `/sitemap*.xml` paths.
     * Prefixing either would break those conventions.
     */
    private const array ROOT_LEVEL_MODULES = ['Core', 'Sitemap'];

    /**
     * Folder-name overrides for the URL prefix of unmapped modules, so renaming
     * a module folder doesn't silently change its public URLs.
     */
    private const array MODULE_PREFIX_OVERRIDES = [
        'ShopOrder' => 'orders',
    ];

    /**
     * @var array<string, ModuleEntry>|null
     */
    private ?array $loaded = null;

    /**
     * @param  bool  $useCache  False makes `load()` ignore a manifest file that
     *                          is already on disk. The manifest embeds route
     *                          prefixes read from the database, so one built
     *                          against the development database must never boot
     *                          a test run against the test database — the pages
     *                          are different rows, and so are the URLs.
     */
    public function __construct(
        private readonly string $modulesPath,
        private readonly string $cachePath,
        private readonly bool $useCache = true,
    ) {}

    /**
     * The manifest to boot from: the cached file when there is one, a freshly
     * built array otherwise. Resolved once per process.
     *
     * @return array<string, ModuleEntry>
     */
    public function load(): array
    {
        if ($this->loaded !== null) {
            return $this->loaded;
        }

        return $this->loaded = $this->read() ?? $this->build();
    }

    public function isCached(): bool
    {
        return File::exists($this->cachePath);
    }

    public function path(): string
    {
        return $this->cachePath;
    }

    /**
     * Build and write the manifest, returning what was written.
     *
     * @return array<string, ModuleEntry>
     */
    public function write(): array
    {
        $manifest = $this->build();

        File::ensureDirectoryExists(dirname($this->cachePath));
        File::put($this->cachePath, '<?php return ' . var_export($manifest, true) . ';' . PHP_EOL);

        return $this->loaded = $manifest;
    }

    public function clear(): void
    {
        File::delete($this->cachePath);

        $this->loaded = null;
    }

    /**
     * Rebuild the cached manifest after the data behind it changed — a page slug
     * is a route prefix, so editing one in the admin moves URLs.
     *
     * Does nothing when nothing is cached, which is the normal local setup.
     */
    public function refresh(): void
    {
        if (!$this->isCached()) {
            $this->loaded = null;

            return;
        }

        $this->write();
    }

    /**
     * @return array<string, ModuleEntry>|null
     */
    private function read(): ?array
    {
        if (!$this->useCache || !File::exists($this->cachePath)) {
            return null;
        }

        try {
            $manifest = require $this->cachePath;
        } catch (Throwable) {
            return null;
        }

        return is_array($manifest) ? $manifest : null;
    }

    /**
     * @return array<string, ModuleEntry>
     */
    public function build(): array
    {
        if (!File::exists($this->modulesPath)) {
            return [];
        }

        $manifest = [];

        foreach (File::directories($this->modulesPath) as $path) {
            $name = basename($path);

            if (!ModuleGate::isEnabled(Str::snake($name))) {
                continue;
            }

            $manifest[$name] = [
                'web'        => $this->webRoute($name, $path),
                'api'        => $this->fileOrNull($path . '/Http/Routes/api.php'),
                'migrations' => $this->fileOrNull($path . '/database/migrations'),
                'commands'   => $this->commands($path),
                'observers'  => $this->observers($path),
            ];
        }

        return $manifest;
    }

    /**
     * The module's web routes together with where they mount.
     *
     * A module whose folder maps to a `PageModule` is prefixed with that page's
     * slug from the database and gets the `share.page` middleware. `PageText` is
     * the exception: its N pages each have their own slug, so there is no single
     * prefix to resolve and it registers a fallback route instead. Everything
     * else is prefixed from its own folder name, so an unmapped module can never
     * collide with a page-driven prefix.
     *
     * @return array{path: string, prefix: string, page_module: string|null}|null
     */
    private function webRoute(string $name, string $path): ?array
    {
        $file = $this->fileOrNull($path . '/Http/Routes/web.php');

        if ($file === null) {
            return null;
        }

        $pageModule = PageModule::tryFrom(Str::snake($name));

        if ($pageModule === null || $pageModule === PageModule::TEXT) {
            return [
                'path'        => $file,
                'prefix'      => $pageModule === PageModule::TEXT ? '' : $this->unmappedPrefix($name),
                'page_module' => null,
            ];
        }

        $prefix = $this->resolveModulePage($pageModule)?->slug;

        if ($prefix === null || $prefix === '') {
            $prefix = $pageModule === PageModule::INDEX ? '' : Str::lower($pageModule->value);
        }

        return [
            'path'        => $file,
            'prefix'      => $prefix,
            'page_module' => $pageModule->value,
        ];
    }

    private function unmappedPrefix(string $name): string
    {
        if (in_array($name, self::ROOT_LEVEL_MODULES, true)) {
            return '';
        }

        return self::MODULE_PREFIX_OVERRIDES[$name] ?? Str::kebab($name);
    }

    /**
     * Only a database failure and an unknown `pages.module` string are
     * swallowed — a missing `pages` table on a fresh install, no connection yet,
     * or a row still holding a module value that predates a PageModule rename.
     * The latter would otherwise throw out of the enum cast while the app boots,
     * `artisan migrate` included, so the very migration that repairs the rows
     * could never run. Anything else (a broken query class, a bad cache driver)
     * must surface instead of silently mounting every module under its fallback
     * prefix.
     */
    private function resolveModulePage(PageModule $pageModule): ?Page
    {
        try {
            return app(FindPageByModuleQuery::class)->handle($pageModule);
        } catch (QueryException|ValueError) {
            return null;
        }
    }

    /**
     * @return array<int, class-string>
     */
    private function commands(string $path): array
    {
        $commandsPath = $path . '/Console/Commands';

        if (!File::exists($commandsPath)) {
            return [];
        }

        $classes = [];

        foreach (File::allFiles($commandsPath) as $file) {
            $class = $this->classFromFile($file->getPathname());

            if (class_exists($class)) {
                $classes[] = $class;
            }
        }

        return $classes;
    }

    /**
     * Observers by convention: `{Module}/Observers/{Model}Observer.php` belongs
     * to `App\Modules\{Module}\Models\{Model}`. Resolved here rather than at boot
     * so a cached manifest is a plain list of pairs.
     *
     * An observer whose matching model class doesn't exist is skipped, so a
     * non-model listener must not live in `Observers/`
     * (`Core\Listeners\LogListener` is the one such listener, wired globally by
     * `ModuleServiceProvider`).
     *
     * @return array<class-string, class-string<Model>>
     */
    private function observers(string $path): array
    {
        $observersPath = $path . '/Observers';

        if (!File::exists($observersPath)) {
            return [];
        }

        $pairs = [];

        foreach (File::files($observersPath) as $file) {
            $observer = $this->classFromFile($file->getPathname());

            if (!class_exists($observer)) {
                continue;
            }

            $model = str($observer)
                ->replace('\\Observers\\', '\\Models\\')
                ->replaceMatches('/Observer$/', '')
                ->toString();

            if (class_exists($model) && is_subclass_of($model, Model::class)) {
                $pairs[$observer] = $model;
            }
        }

        return $pairs;
    }

    private function fileOrNull(string $path): ?string
    {
        return File::exists($path) ? $path : null;
    }

    private function classFromFile(string $path): string
    {
        $appPath = str_replace(['/', '\\'], DIRECTORY_SEPARATOR, app_path());
        $normalizedPath = str_replace(['/', '\\'], DIRECTORY_SEPARATOR, $path);

        $relative = str($normalizedPath)
            ->after($appPath . DIRECTORY_SEPARATOR)
            ->replace(DIRECTORY_SEPARATOR, '\\')
            ->replace('.php', '')
            ->toString();

        return 'App\\' . $relative;
    }
}
