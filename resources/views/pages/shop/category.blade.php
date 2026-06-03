@extends('layouts.app')

@php use Illuminate\Support\Str; @endphp

@section('content')
    @include('blocks.page_title', [
        'title' => $category->title,
        'items' => array_merge([
            ['name' => 'Main Page', 'url' => module_url(\App\Modules\Core\Enums\PageModule::INDEX)],
            ['name' => $page->title ?? 'Shop', 'url' => module_url(\App\Modules\Core\Enums\PageModule::SHOP)],
        ], array_slice($breadcrumbs, 0, -1)),
    ])

    <div class="container mt-4">
        <div class="row">

            <div class="col-lg-4 sidebar">
                <div class="widgets-container">
                    <form method="GET" action="{{ url()->current() }}">

                        <div class="product-categories-widget widget-item">
                            <h3 class="widget-title">Categories</h3>
                            @include('pages.shop.partials.category-tree', [
                                'categories' => $filter['categories']['data'],
                                'level' => 0,
                            ])
                        </div>
                        @php
                            $priceFilter = $filter['price'] ?? [];
                            $priceData = $priceFilter['data'] ?? [];

                            $priceMin = (float)($priceData['min'] ?? 0);
                            $priceMax = (float)($priceData['max'] ?? 0);

                            $priceFromGet = $priceFilter['from'] ?? $priceMin;
                            $priceToGet = $priceFilter['to'] ?? $priceMax;

                            $priceFromSlider = max($priceMin, min((float)$priceFromGet, $priceMax));
                            $priceToSlider = max($priceMin, min((float)$priceToGet, $priceMax));

                            if ($priceFromSlider > $priceToSlider) {
                                $priceFromSlider = $priceMin;
                                $priceToSlider = $priceMax;
                            }
                        @endphp
                        <div class="pricing-range-widget widget-item">
                            <h3 class="widget-title">Price Range</h3>

                            <div class="price-range-container">
                                <div class="current-range mb-3">
                                    <span class="min-price">{{ $priceFromSlider }}</span>
                                    <span class="max-price float-end">{{ $priceToSlider }}</span>
                                </div>

                                <div class="range-slider">
                                    <div class="slider-track"></div>
                                    <div class="slider-progress"></div>

                                    <input
                                        type="range"
                                        name=""
                                        class="min-range"
                                        min="{{ $priceMin }}"
                                        max="{{ $priceMax }}"
                                        value="{{ $priceFromSlider }}"
                                        step="1"
                                    >

                                    <input
                                        type="range"
                                        name=""
                                        class="max-range"
                                        min="{{ $priceMin }}"
                                        max="{{ $priceMax }}"
                                        value="{{ $priceToSlider }}"
                                        step="1"
                                    >
                                </div>

                                <div class="price-inputs mt-3">
                                    <div class="row g-2">
                                        <div class="col-6">
                                            <div class="input-group input-group-sm">
                                                <span class="input-group-text">₴</span>
                                                <input
                                                    type="number"
                                                    data-name="price_from"
                                                    class="form-control min-price-input"
                                                    value="{{ $priceFromGet }}"
                                                    {{ request()->has('price_from') ? 'name=price_from' : '' }}
                                                >
                                            </div>
                                        </div>

                                        <div class="col-6">
                                            <div class="input-group input-group-sm">
                                                <span class="input-group-text">₴</span>
                                                <input
                                                    type="number"
                                                    data-name="price_to"
                                                    class="form-control max-price-input"
                                                    value="{{ $priceToGet }}"
                                                    {{ request()->has('price_to') ? 'name=price_to' : '' }}
                                                >
                                            </div>
                                        </div>
                                    </div>
                                </div>

                            </div>
                        </div>
                        <script>
                            document.addEventListener('DOMContentLoaded', function () {
                                document.querySelectorAll('.pricing-range-widget').forEach(function (widget) {
                                    const minRange = widget.querySelector('.min-range');
                                    const maxRange = widget.querySelector('.max-range');
                                    const minInput = widget.querySelector('.min-price-input');
                                    const maxInput = widget.querySelector('.max-price-input');

                                    function activatePriceFilter() {
                                        minInput.name = minInput.dataset.name;
                                        maxInput.name = maxInput.dataset.name;
                                    }

                                    minRange.addEventListener('input', function () {
                                        activatePriceFilter();
                                        minInput.value = minRange.value;
                                    });

                                    maxRange.addEventListener('input', function () {
                                        activatePriceFilter();
                                        maxInput.value = maxRange.value;
                                    });

                                    minInput.addEventListener('input', activatePriceFilter);
                                    maxInput.addEventListener('input', activatePriceFilter);
                                });
                            });
                        </script>

                        <div class="brand-filter-widget widget-item">
                            <h3 class="widget-title">Filter by Brand</h3>

                            <div class="brand-filter-content">
                                <?php
                                /*
                                ?>
                                <div class="brand-search">
                                    <input type="text" class="form-control" placeholder="Search brands...">
                                    <i class="bi bi-search"></i>
                                </div>
                                <?php
                                /**/
                                ?>
                                <div class="brand-list">
                                    @foreach($filter['brand']['data'] ?? [] as $brand)
                                        @php
                                            $selectedBrands = collect((array)($filter['brand']['value'] ?? []))
                                            ->map(fn($id) => (int)$id)
                                            ->filter()
                                            ->toArray();
                                        @endphp

                                        <div class="brand-item">
                                            <div class="form-check">
                                                <input
                                                    class="form-check-input"
                                                    type="checkbox"
                                                    name="brand[]"
                                                    value="{{ $brand->id }}"
                                                    id="brand{{ $brand->id }}"
                                                    {{ in_array($brand->id, $selectedBrands) ? 'checked' : '' }}
                                                >

                                                <label class="form-check-label" for="brand{{ $brand->id }}">
                                                    {{ $brand->title }}
                                                    <span class="brand-count">({{ $brand->products_count }})</span>
                                                </label>
                                            </div>
                                        </div>
                                    @endforeach
                                </div>
                                <div class="filter-actions mt-3">
                                    <button type="submit" class="btn btn-sm btn-primary w-100">Apply Filter</button>
                                </div>
                                <div class="brand-actions">
                                    <button type="submit" class="btn btn-sm btn-outline-primary">Apply Filter</button>
                                    <a href="{{ url()->current() }}" class="btn btn-sm btn-link">Clear All</a>
                                </div>
                            </div>
                        </div>

                    </form>







                </div>
            </div>

            <div class="col-lg-8">

                @if($category->text)
                    <div class="mb-4">
                        {!! $category->text !!}
                    </div>
                @endif

                <section id="category-header" class="category-header section">
                    <div class="filter-container mb-4">
                        <div class="row g-3">
                            <div class="col-12 col-md-6 col-lg-4">
                                <div class="filter-item search-form">
                                    <label for="productSearch" class="form-label">Search Products</label>
                                    <div class="input-group">
                                        <input type="text" class="form-control" id="productSearch" placeholder="Search for products...">
                                        <button class="btn search-btn" type="button">
                                            <i class="bi bi-search"></i>
                                        </button>
                                    </div>
                                </div>
                            </div>



                            <div class="col-12 col-md-6 col-lg-2">
                                <div class="filter-item">
                                    <label for="sortBy" class="form-label">Sort By</label>
                                    <select class="form-select" id="sortBy">
                                        <option selected>Featured</option>
                                        <option>Price: Low to High</option>
                                        <option>Price: High to Low</option>
                                        <option>Newest Arrivals</option>
                                    </select>
                                </div>
                            </div>
                        </div>
                    </div>
                </section>

                <section id="category-product-list" class="category-product-list section">
                    <div class="row gy-4">
                        @foreach($products as $product)
                            <div class="col-lg-6">
                                <div class="product-box">
                                    <div class="product-thumb">
                                        @if($product->price2)
                                            <span class="product-label product-label-sale">Sale</span>
                                        @endif

                                        @if($product->image)
                                            <img
                                                src="{{ $product->image }}"
                                                alt="{{ $product->title ?? $product->name }}"
                                                class="main-img"
                                                loading="lazy"
                                            >
                                        @endif

                                        <div class="product-overlay">
                                            <div class="product-quick-actions">
                                                <button type="button" class="quick-action-btn">
                                                    <i class="bi bi-heart"></i>
                                                </button>
                                                <button type="button" class="quick-action-btn">
                                                    <i class="bi bi-arrow-repeat"></i>
                                                </button>
                                                <a
                                                    href="{{ module_url(\App\Modules\Core\Enums\PageModule::SHOP, [$category->slug, $product->slug. '-' . $product->id]) }}"
                                                    class="quick-action-btn"
                                                >
                                                    <i class="bi bi-eye"></i>
                                                </a>
                                            </div>

                                            <div class="add-to-cart-container">
                                                <button type="button" class="add-to-cart-btn">Add to Cart</button>
                                            </div>
                                        </div>
                                    </div>

                                    <div class="product-content">
                                        <div class="product-details">
                                            <h3 class="product-title">
                                                <a href="{{ module_url(\App\Modules\Core\Enums\PageModule::SHOP, [$category->slug, $product->slug. '-' . $product->id]) }}">
                                                    {{ $product->title ?? $product->name }}
                                                </a>
                                            </h3>

                                            <div class="product-price">
                                                @if($product->price2)
                                                    <span class="original">{{ $product->price }}</span>
                                                    <span class="sale">{{ $product->price2 }}</span>
                                                @elseif($product->price)
                                                    <span>{{ $product->price }}</span>
                                                @endif
                                            </div>
                                        </div>

                                        <div class="product-rating-container">
                                            <div class="rating-stars">
                                                <i class="bi bi-star-fill"></i>
                                                <i class="bi bi-star-fill"></i>
                                                <i class="bi bi-star-fill"></i>
                                                <i class="bi bi-star-fill"></i>
                                                <i class="bi bi-star"></i>
                                            </div>
                                            <span class="rating-number">4.0</span>
                                        </div>

                                        <div class="product-color-options">
                                            <span class="color-option active" style="background-color: #3b82f6;"></span>
                                            <span class="color-option" style="background-color: #22c55e;"></span>
                                            <span class="color-option" style="background-color: #f97316;"></span>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        @endforeach
                    </div>

                    <div class="mt-4">
                        {{ $products->links() }}
                    </div>
                </section>

            </div>
        </div>
    </div>
@endsection
