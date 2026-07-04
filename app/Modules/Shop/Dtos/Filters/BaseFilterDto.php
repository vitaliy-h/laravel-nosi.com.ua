<?php

namespace App\Modules\Shop\Dtos\Filters;

use Illuminate\Database\Eloquent\Builder;
use Spatie\LaravelData\Data;

abstract class BaseFilterDto extends Data
{
    abstract public function apply(Builder $query): void;
}
