<?php

namespace Tests\Unit\Filament;

use App\Filament\Support\ArtisanCommandRunner;
use App\Modules\User\Models\User;
use InvalidArgumentException;
use PHPUnit\Framework\Attributes\DataProvider;
use Tests\TestCase;

class ArtisanCommandRunnerTest extends TestCase
{
    public static function destructiveCommandProvider(): array
    {
        return [
            ['migrate:rollback'],
            ['migrate:fresh'],
            ['migrate:fresh --seed'],
            ['db:seed'],
        ];
    }

    public static function safeCommandProvider(): array
    {
        return [
            ['migrate'],
            ['migrate:status'],
            ['cache:clear'],
            ['config:clear'],
            ['route:clear'],
            ['view:clear'],
            ['queue:restart'],
            ['optimize:clear'],
        ];
    }

    #[DataProvider('destructiveCommandProvider')]
    public function test_destructive_commands_are_unavailable_in_production(string $key): void
    {
        $this->app->detectEnvironment(fn () => 'production');

        $this->assertTrue(ArtisanCommandRunner::isAllowed($key));
        $this->assertTrue(ArtisanCommandRunner::isDestructive($key));
        $this->assertFalse(ArtisanCommandRunner::isAvailable($key));
    }

    #[DataProvider('destructiveCommandProvider')]
    public function test_destructive_commands_are_available_outside_production(string $key): void
    {
        $this->app->detectEnvironment(fn () => 'testing');

        $this->assertTrue(ArtisanCommandRunner::isAvailable($key));
    }

    #[DataProvider('safeCommandProvider')]
    public function test_safe_commands_stay_available_in_production(string $key): void
    {
        $this->app->detectEnvironment(fn () => 'production');

        $this->assertTrue(ArtisanCommandRunner::isAvailable($key));
    }

    public function test_unknown_command_is_never_available(): void
    {
        $this->assertFalse(ArtisanCommandRunner::isAvailable('env'));
    }

    public function test_run_refuses_a_destructive_command_in_production(): void
    {
        $this->app->detectEnvironment(fn () => 'production');

        $actor = new User(['email' => 'super@example.com']);

        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage('cannot be run in production');

        app(ArtisanCommandRunner::class)->run('migrate:fresh', $actor);
    }

    public function test_run_refuses_a_command_outside_the_whitelist(): void
    {
        $actor = new User(['email' => 'super@example.com']);

        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage('not whitelisted');

        app(ArtisanCommandRunner::class)->run('env', $actor);
    }
}
