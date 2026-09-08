<?php

namespace App\Modules\ShopOrder\Actions;

use App\Modules\Core\Support\Money;
use App\Modules\Notification\Dtos\SendLetterTemplateData;
use App\Modules\Notification\Enums\LetterEnum;
use App\Modules\Notification\Services\LetterTemplateService;
use App\Modules\ShopCart\Actions\IncrementPromocodeUsageAction;
use App\Modules\ShopCart\Models\Promocode;
use App\Modules\ShopOrder\Dtos\CreateOrderData;
use App\Modules\ShopOrder\Models\Order;
use Illuminate\Support\Facades\DB;

readonly class CreateOrderAction
{
    public function __construct(
        private RecalculateOrderTotalAction $recalculate,
        private IncrementPromocodeUsageAction $incrementPromocodeUsage,
        private LetterTemplateService $letters,
    ) {}

    public function handle(CreateOrderData $dto): Order
    {
        $order = DB::transaction(function () use ($dto) {
            $order = Order::create([
                'user_id'         => $dto->user_id,
                'payment_status'  => $dto->payment_status,
                'payment_type'    => $dto->payment_type,
                'total_amount'    => 0,
                'promocode_id'    => $dto->promocode_id,
                'promocode_code'  => $dto->promocode_code,
                'discount_amount' => $dto->discount_amount,
                'user_name'       => $dto->user_name,
                'user_surname'    => $dto->user_surname,
                'user_email'      => $dto->user_email,
                'user_phone'      => $dto->user_phone,
                'comment'         => $dto->comment,
            ]);

            foreach ($dto->items as $item) {
                $order->items()->create([
                    'shop_product_id' => $item->shop_product_id,
                    'product_name'    => $item->product_name,
                    'product_article' => $item->product_article,
                    'selling_price'   => $item->selling_price,
                    'quantity'        => $item->quantity,
                    'total'           => Money::from($item->selling_price * $item->quantity)->value(),
                ]);
            }

            $this->recalculate->handle($order);

            // A promocode counts as used once the order exists, not when it was
            // applied to a cart that may never be checked out.
            if ($dto->promocode_id !== null && $promocode = Promocode::find($dto->promocode_id)) {
                $this->incrementPromocodeUsage->handle($promocode);
            }

            return $order->fresh();
        });

        if ($order->user_email) {
            DB::afterCommit(fn () => $this->letters->send(
                new SendLetterTemplateData(
                    key: LetterEnum::ORDER_CREATED,
                    replace: [
                        'order_number' => $order->order_number,
                        'total_amount' => Money::from($order->total_amount)->format(),
                        'order_url'    => save_route('order.show', $order->uuid),
                    ],
                    to: $order->user_email,
                )
            ));
        }

        return $order;
    }
}
