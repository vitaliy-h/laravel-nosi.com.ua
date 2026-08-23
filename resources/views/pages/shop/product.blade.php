@extends('layouts.app')

@section('body-class', 'product-details-page')

@section('content')

    @include('blocks.page_title')

    <section id="product-details" class="product-details section">

        <div class="container">

            <div class="row">

                <div class="col-lg-6 mb-5 mb-lg-0">

                    <div class="product-images">

                        <div class="main-image-container mb-3">

                            <div class="image-zoom-container">

                                @if($product->img)
                                    <img
                                        src="{{ $product->image }}"
                                        alt="{{ $product->title ?? $product->name }}"
                                        class="img-fluid main-image"
                                    >
                                @endif

                            </div>

                        </div>

                    </div>

                </div>

                <div class="col-lg-6">

                    <div class="product-info">

                        <div class="product-meta mb-2">

                            <span class="product-category">
                                {{ $category->title ?? $category->name }}
                            </span>

                            <div class="product-rating">
                                <x-rating-stars :value="$product->rating" />

                                <span class="rating-count">
                                    ({{ $product->reviews_count }})
                                </span>
                            </div>

                        </div>

                        <h1 class="product-title">
                            {{ $product->title ?? $product->name }}
                        </h1>

                        <div class="product-price-container mb-4">

                            @if($product->price2)
                                <span class="current-price">
                                    {{ t('shop.currency') }}{{ $product->price2 }}
                                </span>

                                <span class="original-price">
                                    {{ t('shop.currency') }}{{ $product->price }}
                                </span>
                            @elseif($product->price)
                                <span class="current-price">
                                    {{ t('shop.currency') }}{{ $product->price }}
                                </span>
                            @endif

                        </div>

                        @if($product->text)
                            <div class="product-short-description mb-4">
                                {!! $product->text !!}
                            </div>
                        @endif

                        <div class="product-availability mb-4">
                            <i class="bi bi-check-circle-fill text-success"></i>
                            <span>{{ t('shop.in_stock') }}</span>
                        </div>

                        <form data-abcr="{{ save_route('change_product_count') }}" action="{{ save_route('change_product_count') }}" method="POST">
                            @csrf
                            <input type="hidden" name="id" value="{{ $product->id }}">
                            <input type="hidden" name="action" value="{{ \App\Modules\ShopCart\Enums\CartAction::ChangeCount->value }}">

                            <div class="product-quantity mb-4">

                                <h6 class="option-title">
                                    {{ t('shop.quantity') }}:
                                </h6>

                                <div class="quantity-selector">

                                    <button type="button" class="quantity-btn decrease">
                                        <i class="bi bi-dash"></i>
                                    </button>

                                    <input
                                        type="number"
                                        name="count"
                                        class="quantity-input"
                                        value="{{ $cartQuantity }}"
                                        min="1"
                                    >

                                    <button type="button" class="quantity-btn increase">
                                        <i class="bi bi-plus"></i>
                                    </button>

                                </div>

                            </div>

                            <div class="product-actions">

                                <button type="submit" class="btn btn-primary add-to-cart-btn">
                                    <i class="bi bi-cart-plus"></i>
                                    {{ t('shop.add_to_cart') }}
                                </button>

                                <button type="button" class="btn btn-outline-primary buy-now-btn">
                                    <i class="bi bi-lightning-fill"></i>
                                    {{ t('shop.buy_now') }}
                                </button>

                                <button type="button" class="btn btn-outline-secondary wishlist-btn">
                                    <i class="bi bi-heart"></i>
                                </button>

                            </div>
                        </form>

                        <div class="additional-info mt-4">

                            <div class="info-item">
                                <i class="bi bi-truck"></i>
                                <span>{{ t('shop.free_shipping') }}</span>
                            </div>

                            <div class="info-item">
                                <i class="bi bi-arrow-repeat"></i>
                                <span>{{ t('shop.return_policy') }}</span>
                            </div>

                            <div class="info-item">
                                <i class="bi bi-shield-check"></i>
                                <span>{{ t('shop.warranty') }}</span>
                            </div>

                        </div>

                    </div>

                </div>

            </div>

            <div class="row mt-5">

                <div class="col-12">

                    <div class="product-details-tabs">

                        <ul class="nav nav-tabs" id="productTabs" role="tablist">

                            <li class="nav-item" role="presentation">
                                <button
                                    class="nav-link active"
                                    id="description-tab"
                                    data-bs-toggle="tab"
                                    data-bs-target="#description"
                                    type="button"
                                >
                                    {{ t('shop.description') }}
                                </button>
                            </li>

                            <li class="nav-item" role="presentation">
                                <button
                                    class="nav-link"
                                    id="specifications-tab"
                                    data-bs-toggle="tab"
                                    data-bs-target="#specifications"
                                    type="button"
                                >
                                    {{ t('shop.specifications') }}
                                </button>
                            </li>

                            <li class="nav-item" role="presentation">
                                <button
                                    class="nav-link"
                                    id="reviews-tab"
                                    data-bs-toggle="tab"
                                    data-bs-target="#reviews"
                                    type="button"
                                >
                                    {{ t('shop.reviews_tab', ['count' => $rating->total]) }}
                                </button>
                            </li>

                        </ul>

                        <div class="tab-content" id="productTabsContent">

                            <div
                                class="tab-pane fade active show"
                                id="description"
                                role="tabpanel"
                            >

                                <div class="product-description">

                                    <h4>
                                        {{ t('shop.product_overview') }}
                                    </h4>

                                    @if($product->text)
                                        {!! $product->text !!}
                                    @endif

                                </div>

                            </div>

                            <div
                                class="tab-pane fade"
                                id="specifications"
                                role="tabpanel"
                            >

                                <div class="product-specifications">

                                    <div class="specs-group">

                                        <h4>
                                            {{ t('shop.product_information') }}
                                        </h4>

                                        <div class="specs-table">

                                            <div class="specs-row">
                                                <div class="specs-label">
                                                    {{ t('shop.article') }}
                                                </div>

                                                <div class="specs-value">
                                                    {{ $product->article ?? '-' }}
                                                </div>
                                            </div>

                                            <div class="specs-row">
                                                <div class="specs-label">
                                                    {{ t('shop.category') }}
                                                </div>

                                                <div class="specs-value">
                                                    {{ $category->title ?? $category->name }}
                                                </div>
                                            </div>

                                            <div class="specs-row">
                                                <div class="specs-label">
                                                    {{ t('shop.price') }}
                                                </div>

                                                <div class="specs-value">
                                                    {{ $product->price ? t('shop.currency') . $product->price : '-' }}
                                                </div>
                                            </div>

                                        </div>

                                    </div>

                                </div>

                            </div>

                            <div
                                class="tab-pane fade"
                                id="reviews"
                                role="tabpanel"
                            >

                                @include('pages.shop.partials.reviews')

                            </div>

                        </div>

                    </div>

                </div>

            </div>

        </div>

    </section>

@endsection
