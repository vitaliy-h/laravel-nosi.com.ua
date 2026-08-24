<?php

namespace Tests\Unit\Dashboard;

use App\Modules\Core\Queries\Dashboard\GetNeedsAttentionQuery;
use App\Modules\Feedback\Models\Feedback;
use App\Modules\Notification\Models\LogLetter;
use App\Modules\Shop\Models\Review;
use App\Modules\ShopOrders\Enums\PaymentStatusEnum;
use App\Modules\ShopOrders\Models\Order;
use App\Modules\Subscribers\Models\Subscriber;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Carbon;
use Tests\TestCase;

class GetNeedsAttentionQueryTest extends TestCase
{
    use RefreshDatabase;

    private GetNeedsAttentionQuery $query;

    protected function setUp(): void
    {
        parent::setUp();

        Carbon::setTestNow('2026-08-23 12:00:00');

        $this->query = app(GetNeedsAttentionQuery::class);
    }

    protected function tearDown(): void
    {
        Carbon::setTestNow();

        parent::tearDown();
    }

    public function test_counts_everything_waiting_for_an_admin(): void
    {
        Review::factory()->invisible()->count(2)->create();
        Review::factory()->create();

        Feedback::factory()->count(3)->create();
        Feedback::factory()->processed()->create();

        Order::factory()->create([
            'payment_status' => PaymentStatusEnum::UNPAID,
            'created_at' => '2026-08-10 10:00:00',
            'updated_at' => '2026-08-10 10:00:00',
        ]);
        // Recent, so not stale yet.
        Order::factory()->create([
            'payment_status' => PaymentStatusEnum::UNPAID,
            'created_at' => '2026-08-23 09:00:00',
            'updated_at' => '2026-08-23 09:00:00',
        ]);
        // Old but paid.
        Order::factory()->create([
            'payment_status' => PaymentStatusEnum::PAID,
            'created_at' => '2026-08-01 10:00:00',
            'updated_at' => '2026-08-01 10:00:00',
        ]);

        Subscriber::factory()->withError()->count(2)->create();
        Subscriber::factory()->create();

        LogLetter::factory()->failed()->create(['created_at' => '2026-08-23 08:00:00']);
        // Older than 24 hours: no longer actionable.
        LogLetter::factory()->failed()->create(['created_at' => '2026-08-20 08:00:00']);
        LogLetter::factory()->create(['created_at' => '2026-08-23 08:00:00']);

        $counters = $this->query->handle();

        $this->assertSame(2, $counters['pendingReviews']);
        $this->assertSame(3, $counters['unprocessedFeedback']);
        $this->assertSame(1, $counters['staleUnpaidOrders']);
        $this->assertSame(2, $counters['failedSubscribers']);
        $this->assertSame(1, $counters['failedLetters']);
    }

    public function test_empty_database_returns_zeros_not_nulls(): void
    {
        $counters = $this->query->handle();

        foreach ($counters as $key => $value) {
            $this->assertSame(0, $value, "Counter [{$key}] should be zero.");
        }
    }

    public function test_disabled_module_yields_null_instead_of_zero(): void
    {
        config([
            'modules.shop' => false,
            'modules.feedback' => false,
            'modules.subscribers' => false,
        ]);

        $counters = $this->query->handle();

        $this->assertNull($counters['pendingReviews']);
        $this->assertNull($counters['unprocessedFeedback']);
        // shop_orders depends on shop_cart, which depends on shop.
        $this->assertNull($counters['staleUnpaidOrders']);
        $this->assertNull($counters['failedSubscribers']);
        // Letters are part of an always-on module.
        $this->assertSame(0, $counters['failedLetters']);
    }
}
