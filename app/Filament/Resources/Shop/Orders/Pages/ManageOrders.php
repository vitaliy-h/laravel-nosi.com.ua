<?php

namespace App\Filament\Resources\Shop\Orders\Pages;

use App\Filament\Resources\Shop\Orders\OrderResource;
use Filament\Resources\Pages\ManageRecords;
use Illuminate\Database\Eloquent\Builder;

class ManageOrders extends ManageRecords
{
    protected static string $resource = OrderResource::class;

    protected function getHeaderActions(): array
    {
        return [];
    }

    public function getTitle(): string
    {
        return '';
    }

    protected function applySearchToTableQuery(Builder $query): Builder
    {
        $search = $this->getTableSearch();

        if (blank($search)) {
            return $query;
        }

        return $query->where(function (Builder $query) use ($search) {
            $query
                ->where('order_number', 'like', "%{$search}%")
                ->orWhereHas('user', function (Builder $query) use ($search) {
                    $query->where(fn(Builder $query) => $query->where('name', 'like', "%{$search}%")
                        ->orWhere('email', 'like', "%{$search}%"));
                })
                ->orWhereHas('items', function (Builder $query) use ($search) {
                    $query
                        ->where('product_name', 'like', "%{$search}%")
                        ->orWhere('product_sku', 'like', "%{$search}%");
                });
        });
    }
}
