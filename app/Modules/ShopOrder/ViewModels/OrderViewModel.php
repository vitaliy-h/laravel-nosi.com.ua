<?php

namespace App\Modules\ShopOrder\ViewModels;

use App\Modules\Core\Abstract\BaseViewModel;
use App\Modules\Core\Support\Money;
use App\Modules\ShopOrder\Models\Order;
use App\Modules\ShopOrder\Models\OrderProduct;
use Illuminate\Support\Collection;

class OrderViewModel extends BaseViewModel
{
    /**
     * @param  Collection<int, OrderItemViewModel>  $items
     */
    public function __construct(
        public readonly int $id,
        public readonly string $uuid,
        public readonly string $url,
        public readonly ?string $order_number,
        public readonly ?string $created_at_formatted,
        public readonly Collection $items,
        public readonly int $items_count,
        public readonly string $payment_status,
        public readonly string $payment_status_label,
        public readonly ?string $payment_type_label,
        public readonly bool $can_pay,
        public readonly ?string $customer_name,
        public readonly ?string $customer_email,
        public readonly ?string $customer_phone,
        public readonly ?string $comment,
        public readonly ?string $promocode_code,
        public readonly bool $has_discount,
        public readonly string $subtotal_amount_formatted,
        public readonly string $discount_amount_formatted,
        public readonly string $total_amount_formatted,
    ) {}

    /**
     * Needs `items.product.category` eager-loaded — the lines link back to the
     * catalogue.
     */
    public static function fromOrder(Order $order): self
    {
        $items = $order->items->map(fn (OrderProduct $item) => OrderItemViewModel::from($item))->values();
        $customerName = trim($order->user_name . ' ' . $order->user_surname);

        return new self(
            id: $order->id,
            uuid: (string) $order->uuid,
            url: route('order.show', $order->uuid),
            order_number: $order->order_number,
            created_at_formatted: $order->created_at?->format('d M, Y'),
            items: $items,
            items_count: $items->count(),
            payment_status: $order->payment_status->value,
            payment_status_label: $order->payment_status->label(),
            payment_type_label: $order->payment_type?->label(),
            can_pay: $order->permissions->canPay(),
            customer_name: $customerName !== '' ? $customerName : null,
            customer_email: $order->user_email,
            customer_phone: $order->user_phone,
            comment: $order->comment,
            promocode_code: $order->promocode_code,
            has_discount: Money::from($order->discount_amount)->isPositive(),
            subtotal_amount_formatted: Money::from($order->subtotal_amount)->format(),
            discount_amount_formatted: Money::from($order->discount_amount)->format(),
            total_amount_formatted: Money::from($order->total_amount)->format(),
        );
    }
}
