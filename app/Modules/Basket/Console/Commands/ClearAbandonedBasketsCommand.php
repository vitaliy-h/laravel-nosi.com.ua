<?php

namespace App\Modules\Basket\Console\Commands;

use App\Modules\Basket\Models\Basket;
use Illuminate\Console\Attributes\Description;
use Illuminate\Console\Attributes\Signature;
use Illuminate\Console\Command;

#[Signature('basket:clear-abandoned {days=30 : Age in days after which an unattached basket is deleted}')]
#[Description('Delete guest baskets (without a user) that were created more than N days ago')]
class ClearAbandonedBasketsCommand extends Command
{
    public function handle(): int
    {
        $days = (int) $this->argument('days');

        $baskets = Basket::whereNull('user_id')
            ->where('created_at', '<', now()->subDays($days))
            ->get();

        $baskets->each(fn (Basket $basket) => $basket->delete());

        $this->info("Deleted {$baskets->count()} abandoned basket(s) older than {$days} days.");

        return self::SUCCESS;
    }
}
