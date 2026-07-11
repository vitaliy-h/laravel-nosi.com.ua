<?php

namespace App\Modules\ShopCart\Console\Commands;

use App\Modules\ShopCart\Models\Cart;
use Illuminate\Console\Attributes\Description;
use Illuminate\Console\Attributes\Signature;
use Illuminate\Console\Command;

#[Signature('cart:clear-abandoned {days=30 : Age in days after which an unattached cart is deleted}')]
#[Description('Delete guest carts (without a user) that were created more than N days ago')]
class ClearAbandonedCartsCommand extends Command
{
    public function handle(): int
    {
        $days = (int) $this->argument('days');

        $carts = Cart::whereNull('user_id')
            ->where('created_at', '<', now()->subDays($days))
            ->get();

        $carts->each(fn (Cart $cart) => $cart->delete());

        $this->info("Deleted {$carts->count()} abandoned cart(s) older than {$days} days.");

        return self::SUCCESS;
    }
}
