<?php

namespace Tests\Feature\Infrastructure;

use App\Modules\Page\Enums\PageModule;
use App\Modules\Page\Queries\GetAllPagesQuery;
use App\Providers\ModuleServiceProvider;
use Illuminate\Database\QueryException;
use Illuminate\Support\Facades\Artisan;
use PDOException;
use ReflectionMethod;
use RuntimeException;
use Tests\TestCase;

class ModuleBootTest extends TestCase
{
    private function callPrivate(string $method): mixed
    {
        $provider = new ModuleServiceProvider($this->app);

        $reflection = new ReflectionMethod($provider, $method);

        return $reflection->invoke($provider);
    }

    public function test_enabled_modules_are_resolved_once_and_reused(): void
    {
        $provider = new ModuleServiceProvider($this->app);
        $method = new ReflectionMethod($provider, 'enabledModules');

        $first = $method->invoke($provider);
        $second = $method->invoke($provider);

        $this->assertNotEmpty($first);
        $this->assertSame($first, $second);
    }

    public function test_enabled_modules_skips_a_disabled_module(): void
    {
        config(['modules.list.news' => false]);

        $names = array_map('basename', $this->callPrivate('enabledModules'));

        $this->assertNotContains('News', $names);
        $this->assertContains('Page', $names);
    }

    public function test_module_commands_are_registered(): void
    {
        $this->assertArrayHasKey('cart:clear-abandoned', Artisan::all());
    }

    public function test_a_database_failure_leaves_the_module_without_a_page(): void
    {
        $this->mock(GetAllPagesQuery::class)
            ->shouldReceive('handle')
            ->andThrow(new QueryException('mysql', 'select 1', [], new PDOException('no connection')));

        $provider = new ModuleServiceProvider($this->app);
        $method = new ReflectionMethod($provider, 'resolveModulePage');

        $this->assertNull($method->invoke($provider, PageModule::NEWS));
    }

    public function test_a_non_database_failure_is_not_swallowed(): void
    {
        $this->mock(GetAllPagesQuery::class)
            ->shouldReceive('handle')
            ->andThrow(new RuntimeException('something else broke'));

        $provider = new ModuleServiceProvider($this->app);
        $method = new ReflectionMethod($provider, 'resolveModulePage');

        $this->expectException(RuntimeException::class);

        $method->invoke($provider, PageModule::NEWS);
    }
}
