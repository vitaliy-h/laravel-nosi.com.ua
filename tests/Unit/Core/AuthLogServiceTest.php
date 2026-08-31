<?php

namespace Tests\Unit\Core;

use App\Modules\Core\Enums\AuthLogActionEnum;
use App\Modules\Core\Enums\AuthLogResultEnum;
use App\Modules\Core\Enums\AuthLogSourceEnum;
use App\Modules\Core\Models\AuthLog;
use App\Modules\Core\Services\AuthLogService;
use App\Modules\Core\Support\UserAgentParser;
use App\Modules\User\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class AuthLogServiceTest extends TestCase
{
    use RefreshDatabase;

    private AuthLogService $service;

    protected function setUp(): void
    {
        parent::setUp();

        $this->service = app(AuthLogService::class);
    }

    public function test_success_is_written_with_the_user(): void
    {
        $user = User::factory()->create(['email' => 'user@example.com']);

        $this->service->success(AuthLogActionEnum::PASSWORD, $user->email, $user);

        $log = AuthLog::query()->firstOrFail();

        $this->assertSame($user->id, $log->user_id);
        $this->assertSame('user@example.com', $log->login);
        $this->assertSame(AuthLogActionEnum::PASSWORD, $log->action);
        $this->assertSame(AuthLogResultEnum::SUCCESS, $log->result);
        $this->assertInstanceOf(AuthLogSourceEnum::class, $log->source);
    }

    public function test_failure_keeps_the_submitted_login_without_a_user(): void
    {
        $this->service->failure(AuthLogActionEnum::PASSWORD, 'ghost@example.com');

        $log = AuthLog::query()->firstOrFail();

        $this->assertNull($log->user_id);
        $this->assertSame('ghost@example.com', $log->login);
        $this->assertSame(AuthLogResultEnum::FAILURE, $log->result);
    }

    public function test_blocked_is_written(): void
    {
        $this->service->blocked(AuthLogActionEnum::PASSWORD, 'user@example.com');

        $this->assertSame(
            AuthLogResultEnum::BLOCKED,
            AuthLog::query()->firstOrFail()->result,
        );
    }

    public function test_nothing_is_written_when_the_journal_is_disabled(): void
    {
        config(['auth_log.enabled' => false]);

        $this->service->failure(AuthLogActionEnum::PASSWORD, 'user@example.com');

        $this->assertSame(0, AuthLog::query()->count());
    }

    public function test_user_agent_parser_reads_browser_and_platform(): void
    {
        $parsed = UserAgentParser::parse(
            'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/120.0 Safari/537.36'
        );

        $this->assertSame('Chrome', $parsed['browser']);
        $this->assertSame('Windows', $parsed['platform']);
    }

    public function test_user_agent_parser_handles_an_empty_string(): void
    {
        $this->assertSame(
            ['browser' => null, 'platform' => null],
            UserAgentParser::parse(null),
        );
    }
}
