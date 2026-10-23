<?php

namespace App\Modules\Shop\Dtos;

readonly class CategorySearchData
{
    public function __construct(
        public bool $root = false,
        public bool $headerMenu = false,
        public bool $footerMenu = false,
        public bool $hasImage = false,
        public bool $onlyWithProducts = false,
        public bool $withProductsInTree = false,
        public bool $random = false,
        public ?int $limit = null,
    ) {}
}
