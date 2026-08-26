<?php

namespace Tests\Unit\Core;

use App\Modules\Core\Dtos\ActionResult;
use PHPUnit\Framework\TestCase;

class ActionResultTest extends TestCase
{
    public function test_ok_without_payload(): void
    {
        $result = ActionResult::ok();

        $this->assertTrue($result->success);
        $this->assertNull($result->message);
        $this->assertNull($result->data);
    }

    public function test_ok_with_payload_and_message(): void
    {
        $payload = new \stdClass;

        $result = ActionResult::ok($payload, 'done');

        $this->assertTrue($result->success);
        $this->assertSame('done', $result->message);
        $this->assertSame($payload, $result->data);
    }

    public function test_ok_with_message_only(): void
    {
        $result = ActionResult::ok(message: 'saved');

        $this->assertTrue($result->success);
        $this->assertSame('saved', $result->message);
        $this->assertNull($result->data);
    }

    public function test_fail_carries_message_and_no_payload(): void
    {
        $result = ActionResult::fail('nope');

        $this->assertFalse($result->success);
        $this->assertSame('nope', $result->message);
        $this->assertNull($result->data);
    }
}
