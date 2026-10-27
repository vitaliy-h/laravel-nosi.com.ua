@extends('layouts.app')

@section('body-class', 'category-page')

@section('content')
    @include('partials.page_title')

    <div class="container page">
        <div class="shop-layout">

            <aside class="shop-aside is-collapsed">
                <button type="button" class="btn btn-line filter-toggle"><i class="bi bi-sliders"></i> {{ t('shop.categories') }} / {{ t('shop.apply_filter') }}</button>

                <div class="filter-block">
                    <h3 class="filter-title">{{ t('shop.categories') }}</h3>
                    @include('modules.shop.partials.category_tree', ['categories' => $categories, 'level' => 0])
                </div>

                <form method="GET" action="{{ url()->current() }}" class="filter-block">
                    <input type="hidden" name="search" value="{{ request('search') }}">
                    <input type="hidden" name="sort" value="{{ request('sort') }}">
                    <input type="hidden" name="c" value="{{ request('c') }}">

                    <div class="price-filter">
                        <h3 class="filter-title">{{ t('shop.price_range') }}</h3>
                        <div class="range">
                            <span class="range-fill"></span>
                            <input type="range" class="min-range" min="{{ $filter->price->min }}" max="{{ $filter->price->max }}" value="{{ $filter->price->fromSlider() }}" step="1" aria-label="Min">
                            <input type="range" class="max-range" min="{{ $filter->price->min }}" max="{{ $filter->price->max }}" value="{{ $filter->price->toSlider() }}" step="1" aria-label="Max">
                        </div>
                        {{-- The price inputs get their name only once the customer touches the
                             filter, so an untouched range stays out of the URL. --}}
                        <div class="price-fields">
                            <input type="number" class="input min-price-input" data-name="price_from" value="{{ $filter->price->fromValue() }}" @if(request()->has('price_from')) name="price_from" @endif aria-label="{{ t('shop.price_range') }} min">
                            <input type="number" class="input max-price-input" data-name="price_to" value="{{ $filter->price->toValue() }}" @if(request()->has('price_to')) name="price_to" @endif aria-label="{{ t('shop.price_range') }} max">
                        </div>
                    </div>

                    @if($filter->brand->items->isNotEmpty())
                        <h3 class="filter-title" style="margin-top: 22px;">{{ t('shop.filter_by_brand') }}</h3>
                        <div class="brand-list">
                            @foreach($filter->brand->items as $brand)
                                <label class="check">
                                    <input type="checkbox" name="brand[]" value="{{ $brand->id }}" @checked(in_array($brand->id, $filter->brand->selected, true))>
                                    <span>{{ $brand->title }} <span class="muted">({{ $brand->products_count }})</span></span>
                                </label>
                            @endforeach
                        </div>
                    @endif

                    {{-- Product parameters (admin → Catalog → Parameters), each under its code --}}
                    @foreach($filter->attributes ?? [] as $attributeFilter)
                        @if($attributeFilter->items->isNotEmpty())
                            <h3 class="filter-title" style="margin-top: 22px;">{{ $attributeFilter->title }}</h3>
                            <div class="brand-list">
                                @foreach($attributeFilter->items as $option)
                                    <label class="check">
                                        <input type="checkbox" name="{{ $attributeFilter->key }}[]" value="{{ $option->id }}" @checked(in_array($option->id, $attributeFilter->selected, true))>
                                        <span>{{ $option->title }} <span class="muted">({{ $option->products_count }})</span></span>
                                    </label>
                                @endforeach
                            </div>
                        @endif
                    @endforeach

                    <div class="filter-actions" style="margin-top: 22px;">
                        <button type="submit" class="btn btn-dark btn-sm">{{ t('shop.apply_filter') }}</button>
                        <a href="{{ url()->current() }}" class="btn btn-line btn-sm">{{ t('shop.clear_all') }}</a>
                    </div>
                </form>
            </aside>

            <div class="shop-main">
                @if($category?->text)
                    <div class="content category-text">{!! $category->text !!}</div>
                @endif

                <div class="shop-toolbar filter-container">
                    <div class="field">
                        <label for="productSearch">{{ t('shop.search_products') }}</label>
                        <div class="search-input">
                            <input type="text" class="input" id="productSearch" name="search" value="{{ request('search') }}" placeholder="{{ t('shop.search_placeholder') }}">
                            <button type="button" aria-label="{{ t('shop.search_products') }}"><i class="bi bi-search"></i></button>
                        </div>
                    </div>
                    <div class="field">
                        <label for="sortBy">{{ t('shop.sort_by') }}</label>
                        <select class="input" id="sortBy" name="sort">
                            @foreach(\App\Modules\Shop\Enums\SortOptionEnum::cases() as $option)
                                <option value="{{ $option->value }}" @selected(request('sort') === $option->value)>{{ $option->label() }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="field">
                        <label for="itemsPerPage">{{ t('shop.items') }}</label>
                        <select class="input" id="itemsPerPage" name="c">
                            @foreach(\App\Modules\Shop\Enums\PerPageOptionEnum::cases() as $option)
                                <option value="{{ $option->value }}" @selected((int) request('c') === $option->value)>{{ $option->label() }}</option>
                            @endforeach
                        </select>
                    </div>
                </div>

                @if($products->isEmpty())
                    <div class="empty-state">
                        <i class="bi bi-search"></i>
                        {{ t('shop.no_products') }}
                    </div>
                @else
                    <div class="product-grid">
                        @foreach($products as $product)
                            <x-product-tile :product="$product" />
                        @endforeach
                    </div>
                @endif

                {{ $products->links() }}
            </div>

        </div>
    </div>
@endsection

@push('scripts')
    <script>
        // Price range: one line, two thumbs that cannot cross; the slider and the
        // inputs follow each other, and only a touched filter gets its name.
        document.querySelectorAll('.price-filter').forEach(function (box) {
            var range = box.querySelector('.range');
            var minRange = box.querySelector('.min-range'), maxRange = box.querySelector('.max-range');
            var minInput = box.querySelector('.min-price-input'), maxInput = box.querySelector('.max-price-input');
            var lo = +minRange.min, hi = +minRange.max;
            function activate() { minInput.name = minInput.dataset.name; maxInput.name = maxInput.dataset.name; }
            function percent(value) { return hi > lo ? (value - lo) / (hi - lo) * 100 : 0; }
            function paint() {
                range.style.setProperty('--from', percent(+minRange.value));
                range.style.setProperty('--to', percent(+maxRange.value));
                // Both thumbs at the right end: the lower one must stay grabbable
                minRange.style.zIndex = +minRange.value >= hi - (hi - lo) / 2 ? 3 : '';
            }
            minRange.addEventListener('input', function () {
                if (+minRange.value > +maxRange.value) minRange.value = maxRange.value;
                activate(); minInput.value = minRange.value; paint();
            });
            maxRange.addEventListener('input', function () {
                if (+maxRange.value < +minRange.value) maxRange.value = minRange.value;
                activate(); maxInput.value = maxRange.value; paint();
            });
            minInput.addEventListener('input', function () {
                activate();
                if (minInput.value !== '') minRange.value = Math.min(+minInput.value, +maxRange.value);
                paint();
            });
            maxInput.addEventListener('input', function () {
                activate();
                if (maxInput.value !== '') maxRange.value = Math.max(+maxInput.value, +minRange.value);
                paint();
            });
            paint();
        });

        // Search / sort / per page apply at once through the URL.
        function applyToolbar(el) {
            var url = new URL(window.location.href);
            var value = el.value.trim();
            value ? url.searchParams.set(el.name, value) : url.searchParams.delete(el.name);
            url.searchParams.delete('page');
            window.location.href = url;
        }
        document.querySelectorAll('.filter-container select, .filter-container input').forEach(function (el) {
            el.addEventListener('change', function () { applyToolbar(el); });
        });
        document.querySelector('.search-input button').addEventListener('click', function () {
            applyToolbar(document.getElementById('productSearch'));
        });
    </script>
@endpush
