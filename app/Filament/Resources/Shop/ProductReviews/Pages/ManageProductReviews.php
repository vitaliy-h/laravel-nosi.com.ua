<?php

namespace App\Filament\Resources\Shop\ProductReviews\Pages;

use App\Filament\Resources\Shop\ProductReviews\ProductReviewResource;
use Filament\Resources\Pages\ManageRecords;

class ManageProductReviews extends ManageRecords
{
    protected static string $resource = ProductReviewResource::class;

    protected function getHeaderActions(): array
    {
        return [

        ];
    }

    public function getTitle(): string
    {
        return '';
    }
}
