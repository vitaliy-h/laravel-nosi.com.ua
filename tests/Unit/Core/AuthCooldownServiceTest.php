<?php

namespace Tests\Unit\Core;

use App\Modules\Core\Exceptions\AuthCooldownException;
use App\Modules\Core\Services\AuthCooldownService;
use Tests\TestCase;

class AuthCooldownServiceTest extends TestCase
{
    private AuthCooldownService $service;

    protected function setUp(): void
    {
        parent::setUp();

        config(['auth_log.cooldown_seconds' => 5]);

        $this->service = app(AuthCooldownService::class);
    }

    public function test_first_attempt_is_not_blocked(): void
    {
        $this->service->assertNotBlocked('user@example.com');

        $this->assertTrue(true);
    }

    public function test_attempt_after_a_failure_is_blocked(): void
    {
        $this->service->hit('user@example.com');

        $this->expectException(AuthCooldownException::class);

        $this->service->assertNotBlocked('user@example.com');
    }

    public function test_blocked_exception_reports_remaining_seconds(): void
    {
        $this->service->hit('user@example.com');

        try {
            $this->service->assertNotBlocked('user@example.com');
            $this->fail('Expected AuthCooldownException');
        } catch (AuthCooldownException $exception) {
            $this->assertGreaterThan(0, $exception->seconds);
            $this->assertLessThanOrEqual(5, $exception->seconds);
        }
    }

    public function test_clear_lifts_the_cooldown(): void
    {
        $this->service->hit('user@example.com');
        $this->service->clear('user@example.com');

        $this->service->assertNotBlocked('user@example.com');

        $this->assertTrue(true);
    }

    public function test_cooldown_is_case_insensitive_for_the_login(): void
    {
        $this->service->hit('User@Example.com');

        $this->expectException(AuthCooldownException::class);

        $this->service->assertNotBlocked('user@example.com');
    }

    public function test_another_login_is_not_affected(): void
    {
        $this->service->hit('user@example.com');

        $this->service->assertNotBlocked('other@example.com');

        $this->assertTrue(true);
    }

    public function test_same_login_from_another_ip_is_not_blocked(): void
    {
        $this->assertNotSame(
            $this->service->key('user@example.com', '10.0.0.1'),
            $this->service->key('user@example.com', '10.0.0.2'),
        );
    }

    public function test_zero_seconds_disables_the_cooldown(): void
    {
        config(['auth_log.cooldown_seconds' => 0]);

        $this->service->hit('user@example.com');

        $this->service->assertNotBlocked('user@example.com');

        $this->assertTrue(true);
    }
}
