<?php

namespace Tests\Unit\ShopOrder;

use App\Modules\Notification\Jobs\SendTelegramMessageJob;
use App\Modules\ShopCart\Models\Cart;
use App\Modules\ShopCart\Models\CartProduct;
use App\Modules\ShopOrder\Actions\PlaceOrderFromCartAction;
use App\Modules\ShopOrder\Actions\SendNewOrderTelegramAction;
use App\Modules\ShopOrder\Dtos\CustomerData;
use App\Modules\Translation\Enums\Localization;
use App\Modules\Translation\Models\Language;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Queue;
use Tests\TestCase;

class SendNewOrderTelegramActionTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        $this->seedLetterTemplates();
        config(['app.locale' => 'uk', 'services.telegram_bot.chat_id' => null]);
        Queue::fake();
    }

    public function test_placing_an_order_sends_one_message_per_recipient(): void
    {
        config(['services.telegram_bot.chat_id' => '111, -222']);

        $this->placeOrder();

        Queue::assertPushed(SendTelegramMessageJob::class, 2);
        Queue::assertPushed(SendTelegramMessageJob::class, fn ($job) => $job->logTelegram->receiver === '111');
        Queue::assertPushed(SendTelegramMessageJob::class, fn ($job) => $job->logTelegram->receiver === '-222');
    }

    public function test_message_lists_the_customer_and_items_escaped(): void
    {
        config(['services.telegram_bot.chat_id' => '111']);

        $order = $this->placeOrder();

        Queue::assertPushed(SendTelegramMessageJob::class, function (SendTelegramMessageJob $job) use ($order) {
            return str_contains($job->logTelegram->body, 'Нове замовлення №' . $order->order_number)
                && str_contains($job->logTelegram->body, 'John &lt;b&gt;')
                && str_contains($job->logTelegram->body, '+380501234567')
                && str_contains($job->logTelegram->body, 'Socks (SKU-1) × 3');
        });
    }

    public function test_nothing_is_sent_without_a_recipient(): void
    {
        $this->placeOrder();

        Queue::assertNothingPushed();
    }

    public function test_the_dictionary_recipient_wins_over_env(): void
    {
        config(['services.telegram_bot.chat_id' => '111']);
        Language::factory()->forLocalization(Localization::UK)->create([
            'dictionary' => ['telegram' => ['chat_id' => '999']],
        ]);

        $this->assertSame(['999'], app(SendNewOrderTelegramAction::class)->chatIds());
    }

    private function placeOrder()
    {
        $cart = Cart::factory()->create();
        CartProduct::factory()->create([
            'cart_id'         => $cart->id,
            'product_name'    => 'Socks',
            'product_article' => 'SKU-1',
            'selling_price'   => 15,
            'quantity'        => 3,
            'total'           => 45,
        ]);

        return app(PlaceOrderFromCartAction::class)->handle($cart, new CustomerData(
            user_name: 'John <b>',
            user_phone: '+380501234567',
        ));
    }
}
