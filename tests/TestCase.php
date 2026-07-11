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

    private function skipIfModuleDisabled(): void
    {
        $segments = explode('\\', static::class);

        // Tests\{Feature|Unit}\{Module}\...
        $segment = $segments[2] ?? null;

        if ($segment === null) {
            return;
        }

        $module = Str::snake($segment);

        if (!ModuleGate::isToggleable($module)) {
            return;
        }

        if (!ModuleGate::isEnabled($module)) {
            $this->markTestSkipped("Module [{$module}] is disabled.");
        }
    }
}
