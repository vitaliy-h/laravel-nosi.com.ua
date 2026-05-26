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

                    <div class="product-categories-widget widget-item">
                        <h3 class="widget-title">Categories</h3>

                        <ul class="category-tree list-unstyled mb-0">
                            @foreach($breadcrumbs as $breadcrumb)
                                <li class="category-item">
                                    <div class="d-flex justify-content-between align-items-center category-header">
                                        <a href="{{ $breadcrumb['url'] }}" class="category-link">
                                            {{ $breadcrumb['name'] }}
                                        </a>
                                    </div>
                                </li>
                            @endforeach
                        </ul>
                    </div>

                    <div class="pricing-range-widget widget-item">
                        <h3 class="widget-title">Price Range</h3>

                        <div class="price-range-container">
                            <div class="current-range mb-3">
                                <span class="min-price">$0</span>
                                <span class="max-price float-end">$500</span>
                            </div>

                            <div class="range-slider">
                                <div class="slider-track"></div>
                                <div class="slider-progress" style="left: 0%; width: 50%;"></div>
                                <input type="range" class="min-range" min="0" max="1000" value="0" step="10">
                                <input type="range" class="max-range" min="0" max="1000" value="500" step="10">
                            </div>

                            <div class="price-inputs mt-3">
                                <div class="row g-2">
                                    <div class="col-6">
                                        <div class="input-group input-group-sm">
                                            <span class="input-group-text">$</span>
                                            <input type="number" class="form-control min-price-input" placeholder="Min" value="0">
                                        </div>
                                    </div>

                                    <div class="col-6">
                                        <div class="input-group input-group-sm">
                                            <span class="input-group-text">$</span>
                                            <input type="number" class="form-control max-price-input" placeholder="Max" value="500">
                                        </div>
                                    </div>
                                </div>
                            </div>

                            <div class="filter-actions mt-3">
                                <button type="button" class="btn btn-sm btn-primary w-100">Apply Filter</button>
                            </div>
                        </div>
                    </div>

                    <div class="brand-filter-widget widget-item">
                        <h3 class="widget-title">Filter by Brand</h3>

                        <div class="brand-filter-content">
                            <div class="brand-search">
                                <input type="text" class="form-control" placeholder="Search brands...">
                                <i class="bi bi-search"></i>
                            </div>

                            <div class="brand-list">
                                <div class="brand-item">
                                    <div class="form-check">
                                        <input class="form-check-input" type="checkbox" id="brand1">
                                        <label class="form-check-label" for="brand1">
                                            Brand example
                                            <span class="brand-count">(0)</span>
                                        </label>
                                    </div>
                                </div>
                            </div>

                            <div class="brand-actions">
                                <button class="btn btn-sm btn-outline-primary">Apply Filter</button>
                                <button class="btn btn-sm btn-link">Clear All</button>
                            </div>
                        </div>
                    </div>

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
                                    <label for="priceRange" class="form-label">Price Range</label>
                                    <select class="form-select" id="priceRange">
                                        <option selected>All Prices</option>
                                        <option>Under $25</option>
                                        <option>$25 to $50</option>
                                        <option>$50 to $100</option>
                                        <option>$100 to $200</option>
                                        <option>$200 & Above</option>
                                    </select>
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
                                                    href="{{ module_url(\App\Modules\Core\Enums\PageModule::SHOP, [$category->slug, $product->slug]) }}"
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
                                                <a href="{{ module_url(\App\Modules\Core\Enums\PageModule::SHOP, [$category->slug, $product->slug]) }}">
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
