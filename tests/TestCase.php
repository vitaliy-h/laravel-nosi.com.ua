<?php

namespace Tests;

use App\Modules\Core\Support\ModuleGate;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Foundation\Testing\TestCase as BaseTestCase;
use Illuminate\Support\Str;
use Tests\Concerns\InteractsWithPages;
use Tests\Concerns\InteractsWithRolesAndPermissions;

abstract class TestCase extends BaseTestCase
{
    use InteractsWithPages;
    use InteractsWithRolesAndPermissions;

    protected function setUp(): void
    {
        parent::setUp();

        Factory::guessFactoryNamesUsing(
            fn (string $modelName) => 'Database\\Factories\\'.class_basename($modelName).'Factory'
        );

        $this->skipIfModuleDisabled();
    }

    /**
     * A module's own tests cannot pass while the module is off — its routes,
     * migrations and commands never load — so they get skipped instead.
     *
     * Segments that name no module at all (Errors, Smoke, Filament, ...) need
     * no special case: nothing switches them off, so they always read as
     * enabled and their tests always run.
     */
    private function skipIfModuleDisabled(): void
    {
        $segments = explode('\\', static::class);

        // Tests\{Feature|Unit}\{Module}\...
        $segment = $segments[2] ?? null;

        if ($segment === null) {
            return;
        }

        $this->skipUnlessModules(Str::snake($segment));
    }

    /**
     * Skip unless every named module is on. A test that touches a module's
     * tables or routes cannot pass while that module is off, and a test class
     * outside the module's own namespace has no other way to say so — call
     * this from setUp() for the whole class, or from a single test method.
     */
    protected function skipUnlessModules(string ...$modules): void
    {
        foreach ($modules as $module) {
            if (!ModuleGate::isEnabled($module)) {
                $this->markTestSkipped("Module [{$module}] is disabled.");
            }
        }
    }
}
