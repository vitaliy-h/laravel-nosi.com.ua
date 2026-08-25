@extends('layouts.app')

@section('body-class', 'category-page')

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
                            <h3 class="widget-title">{{ t('shop.categories') }}</h3>
                            @include('pages.shop.partials.category-tree', [
                                'categories' => $categories,
                                'level' => 0,
                            ])
                        </div>
                        <div class="pricing-range-widget widget-item">
                            <h3 class="widget-title">{{ t('shop.price_range') }}</h3>

                            <div class="price-range-container">
                                <div class="current-range mb-3">
                                    <span class="min-price">{{ $filter->price->fromSlider() }}</span>
                                    <span class="max-price float-end">{{ $filter->price->toSlider() }}</span>
                                </div>

                                <div class="range-slider">
                                    <div class="slider-track"></div>
                                    <div class="slider-progress"></div>

                                    <input
                                        type="range"
                                        name=""
                                        class="min-range"
                                        min="{{ $filter->price->min }}"
                                        max="{{ $filter->price->max }}"
                                        value="{{ $filter->price->fromSlider() }}"
                                        step="1"
                                    >

                                    <input
                                        type="range"
                                        name=""
                                        class="max-range"
                                        min="{{ $filter->price->min }}"
                                        max="{{ $filter->price->max }}"
                                        value="{{ $filter->price->toSlider() }}"
                                        step="1"
                                    >
                                </div>

                                <div class="price-inputs mt-3">
                                    <div class="row g-2">
                                        <div class="col-6">
                                            <div class="input-group input-group-sm">
                                                <span class="input-group-text">{{ t('shop.currency') }}</span>
                                                <input
                                                    type="number"
                                                    data-name="price_from"
                                                    class="form-control min-price-input"
                                                    value="{{ $filter->price->fromValue() }}"
                                                    {{ request()->has('price_from') ? 'name=price_from' : '' }}
                                                >
                                            </div>
                                        </div>

                                        <div class="col-6">
                                            <div class="input-group input-group-sm">
                                                <span class="input-group-text">{{ t('shop.currency') }}</span>
                                                <input
                                                    type="number"
                                                    data-name="price_to"
                                                    class="form-control max-price-input"
                                                    value="{{ $filter->price->toValue() }}"
                                                    {{ request()->has('price_to') ? 'name=price_to' : '' }}
                                                >
                                            </div>
                                        </div>
                                    </div>
                                </div>

                            </div>
                        </div>

                        {{-- The apply/clear buttons live inside this widget, so only the
                             brand heading and list go away when there are no brands. --}}
                        <div class="brand-filter-widget widget-item">
                            @if($filter->brand->items->isNotEmpty())
                                <h3 class="widget-title">{{ t('shop.filter_by_brand') }}</h3>
                            @endif

                            <div class="brand-filter-content">
                                {{--
                                <div class="brand-search">
                                    <input type="text" class="form-control" placeholder="Search brands...">
                                    <i class="bi bi-search"></i>
                                </div>
                                --}}
                                @if($filter->brand->items->isNotEmpty())
                                <div class="brand-list">
                                    @foreach($filter->brand->items as $brand)
                                        <div class="brand-item">
                                            <div class="form-check">
                                                <input
                                                    class="form-check-input"
                                                    type="checkbox"
                                                    name="brand[]"
                                                    value="{{ $brand->id }}"
                                                    id="brand{{ $brand->id }}"
                                                    {{ in_array($brand->id, $filter->brand->selected, true) ? 'checked' : '' }}
                                                >

                                                <label class="form-check-label" for="brand{{ $brand->id }}">
                                                    {{ $brand->title }}
                                                    <span class="brand-count">({{ $brand->products_count }})</span>
                                                </label>
                                            </div>
                                        </div>
                                    @endforeach
                                </div>
                                @endif
                                {{--
                                --}}
                                <div class="brand-actions">
                                    {{--
                                    <button type="submit" class="btn btn-sm btn-outline-primary">Apply Filter</button>
                                    --}}
                                    <div class="filter-actions mt-3" style="width: 70% !important">
                                        <button type="submit" class="btn btn-sm btn-primary w-100">{{ t('shop.apply_filter') }}</button>
                                    </div>

                                    <a href="{{ url()->current() }}" class="btn btn-sm btn-link">{{ t('shop.clear_all') }}</a>
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
                                    <label for="productSearch" class="form-label">{{ t('shop.search_products') }}</label>
                                    <div class="input-group">
                                        <input type="text" class="form-control" id="productSearch" name="search" value="{{ request('search') }}" placeholder="{{ t('shop.search_placeholder') }}">
                                        <button class="btn search-btn" type="button">
                                            <i class="bi bi-search"></i>
                                        </button>
                                    </div>
                                </div>
                            </div>



                            <div class="col-12 col-md-6 col-lg-2">
                                <div class="filter-item">
                                    <label for="sortBy" class="form-label">{{ t('shop.sort_by') }}</label>
                                    <select class="form-select" id="sortBy" name="sort">
                                        @foreach(\App\Modules\Shop\Enums\SortOptionEnum::cases() as $option)
                                            <option value="{{ $option->value }}" @selected(request('sort') === $option->value)>{{ $option->label() }}</option>
                                        @endforeach
                                    </select>
                                </div>
                            </div>

                            <div class="col-12 col-md-6 col-lg-2">
                                <div class="filter-item">
                                    <label for="itemsPerPage" class="form-label">{{ t('shop.items') }}</label>
                                    <select class="form-select" id="itemsPerPage" name="c">
                                        @foreach(\App\Modules\Shop\Enums\PerPageOptionEnum::cases() as $option)
                                            <option value="{{ $option->value }}" @selected((int)request('c') === $option->value)>{{ $option->label() }}</option>
                                        @endforeach
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
