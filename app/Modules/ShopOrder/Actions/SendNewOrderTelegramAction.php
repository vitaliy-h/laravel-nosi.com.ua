<?php

namespace App\Modules\ShopOrder\Actions;

use App\Modules\Core\Support\Money;
use App\Modules\Notification\Services\TelegramService;
use App\Modules\ShopOrder\Models\Order;
use App\Modules\ShopOrder\Models\OrderProduct;
use App\Modules\Translation\Facade\Trans;
use Illuminate\Support\Arr;

/**
 * Tells the shop managers about an order placed on the site. Recipients come
 * from the dictionary of the main language (group `telegram`, editable in the
 * admin panel) and fall back to TELEGRAM_CHAT_ID; none set — nothing is sent.
 */
readonly class SendNewOrderTelegramAction
{
    public function __construct(
        private TelegramService $telegram,
    ) {}

    public function handle(Order $order): void
    {
        $chatIds = $this->chatIds();

        if ($chatIds === []) {
            return;
        }

        $order->loadMissing('items');
        $text = $this->message($order);

        foreach ($chatIds as $chatId) {
            $this->telegram->queue($chatId, $text);
        }
    }

    /**
     * @return list<string>
     */
    public function chatIds(): array
    {
        $raw = Arr::get(Trans::getDictionary(config('app.locale')), 'telegram.chat_id');

        if (blank($raw)) {
            $raw = config('services.telegram.chat_id');
        }

        return array_values(array_filter(array_map('trim', preg_split('/[\s,;]+/', (string) $raw))));
    }

    /**
     * Managers read it in the site's main language, whatever the customer used.
     */
    private function message(Order $order): string
    {
        $locale = config('app.locale');
        $line = fn (string $key, ?string $value) => filled($value)
            ? '<b>' . __("site.telegram_order.{$key}", locale: $locale) . ':</b> ' . e($value)
            : null;

        $customer = trim($order->user_name . ' ' . $order->user_surname);

        $items = $order->items->map(fn (OrderProduct $item) => '• ' . e($item->product_name)
            . ($item->product_article ? ' (' . e($item->product_article) . ')' : '')
            . ' × ' . $item->quantity
            . ' — ' . e(Money::from($item->total)->format()));

        $lines = [
            '🛒 <b>' . __('site.telegram_order.title', ['number' => e($order->order_number)], $locale) . '</b>',
            '',
            $line('customer', $customer),
            $line('phone', $order->user_phone),
            $line('email', $order->user_email),
            $line('comment', $order->comment),
            '',
            '<b>' . __('site.telegram_order.items', locale: $locale) . ':</b>',
            ...$items->all(),
            '',
            $order->discount_amount > 0
                ? $line('discount', Money::from($order->discount_amount)->format()
                    . ($order->promocode_code ? " ({$order->promocode_code})" : ''))
                : null,
            $line('total', Money::from($order->total_amount)->format()),
        ];

        return implode("\n", array_filter($lines, fn (?string $l) => $l !== null));
    }
}
