@extends('layouts.app')

@php use Illuminate\Support\Str; @endphp

@section('content')
    @include('blocks.page_title')

    <div class="container mt-4">
        <div class="row">

            <div class="col-lg-4 sidebar">
                <div class="widgets-container">
                    <form method="GET" action="{{ url()->current() }}">
                        <input type="hidden" name="search" value="{{ request('search') }}">
                        <input type="hidden" name="sort" value="{{ request('sort') }}">
                        <input type="hidden" name="items" value="{{ request('items') }}">


                        <div class="product-categories-widget widget-item">
                            <h3 class="widget-title">Categories</h3>
                            @include('pages.shop.partials.category-tree', [
                                'categories' => $categories,
                                'level' => 0,
                            ])
                        </div>
                        @php
                        /*todo remove @php blocks from templates*/
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
                            {{--todo add translations here and in the product template--}}
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

                        <div class="brand-filter-widget widget-item">
                            <h3 class="widget-title">Filter by Brand</h3>

                            <div class="brand-filter-content">
                                {{--
                                <div class="brand-search">
                                    <input type="text" class="form-control" placeholder="Search brands...">
                                    <i class="bi bi-search"></i>
                                </div>
                                --}}
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
                                {{--
                                --}}
                                <div class="brand-actions">
                                    {{--
                                    <button type="submit" class="btn btn-sm btn-outline-primary">Apply Filter</button>
                                    --}}
                                    <div class="filter-actions mt-3" style="width: 70% !important">
                                        <button type="submit" class="btn btn-sm btn-primary w-100">Apply Filter</button>
                                    </div>

                                    <a href="{{ url()->current() }}" class="btn btn-sm btn-link">Clear All</a>
                                </div>
                            </div>
                        </div>

                    </form>







                </div>
            </div>

            <div class="col-lg-8">

                @if(isset($category) && $category->text)
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
                                        <input type="text" class="form-control" id="productSearch" name="search" value="{{ request('search') }}" placeholder="Search for products...">
                                        <button class="btn search-btn" type="button">
                                            <i class="bi bi-search"></i>
                                        </button>
                                    </div>
                                </div>
                            </div>



                            <div class="col-12 col-md-6 col-lg-2">
                                <div class="filter-item">
                                    <label for="sortBy" class="form-label">Sort By</label>
                                    <select class="form-select" id="sortBy" name="sort">
                                        <option value="newest" {{ request('sort') == 'newest' ? 'selected' : '' }}>Newest Arrivals</option>
                                        <option value="price_asc" {{ request('sort') == 'price_asc' ? 'selected' : '' }}>Price: Low to High</option>
                                        <option value="price_desc" {{ request('sort') == 'price_desc' ? 'selected' : '' }}>Price: High to Low</option>
                                    </select>
                                </div>
                            </div>

                            <div class="col-12 col-md-6 col-lg-2">
                                <div class="filter-item">
                                    <label for="itemsPerPage" class="form-label">Items</label>
                                    <select class="form-select" id="itemsPerPage" name="c">
                                        <option value="12" {{ request('c') == 12 ? 'selected' : '' }}>12 per page</option>
                                        <option value="24" {{ request('c') == 24 ? 'selected' : '' }}>24 per page</option>
                                        <option value="48" {{ request('c') == 48 ? 'selected' : '' }}>48 per page</option>
                                        <option value="96" {{ request('c') == 96 ? 'selected' : '' }}>96 per page</option>
                                    </select>
                                </div>
                            </div>

                        </div>
                    </div>
                </section>

                <section id="category-product-list" class="category-product-list section">
                    <div class="row gy-4">
                        @foreach($products as $product)
                            @include('pages.shop.product-box', ['product' => $product])
                        @endforeach
                    </div>

                    <div class="mt-4">
                        {{ $products->links() }}
                    </div>
                </section>

            </div>
        </div>
    </div>
    <script>
        // Do not include price parameters in the URL unless the minimum or maximum
        // price has been changed by the user.
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
    <script>
        // This code updates the URL query parameters when a filter value changes,
        // allowing the filters to work correctly and remain visible in the address bar.
        document.addEventListener('change', function (e) {
            if (!e.target.closest('.filter-container')) return;
            if (!e.target.matches('input, select')) return;

            const url = new URL(window.location.href);
            const name = e.target.name || e.target.id;
            const value = e.target.value.trim();

            value ? url.searchParams.set(name, value) : url.searchParams.delete(name);

            window.location.href = url;
        });
    </script>

@endsection
