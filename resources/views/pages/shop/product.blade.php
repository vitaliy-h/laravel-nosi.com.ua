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

                                {{--todo app/Modules/Core/Traits/Models/HasImage.php already returns "/assets/images/no_image.svg" in image when no image is set--}}
                                @if($product->image)
                                    <img
                                        src="{{ $product->image }}"
                                        alt="{{ $product->title ?? $product->name }}"
                                        class="img-fluid main-image"
                                    >
                                @else
                                    <img
                                        src="/assets/images/no_image.svg"
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
                                <i class="bi bi-star-fill"></i>
                                <i class="bi bi-star-fill"></i>
                                <i class="bi bi-star-fill"></i>
                                <i class="bi bi-star-fill"></i>
                                <i class="bi bi-star"></i>
                                <span class="rating-count">(0)</span>
                            </div>

                        </div>

                        <h1 class="product-title">
                            {{ $product->title ?? $product->name }}
                        </h1>

                        <div class="product-price-container mb-4">

                            @if($product->price2)
                                <span class="current-price">
                                    {{ $product->price2 }}
                                </span>

                                <span class="original-price">
                                    {{ $product->price }}
                                </span>
                            @elseif($product->price)
                                <span class="current-price">
                                    {{ $product->price }}
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
                            <span>In Stock</span>
                        </div>

                        <div class="product-quantity mb-4">

                            <h6 class="option-title">
                                Quantity:
                            </h6>

                            <div class="quantity-selector">

                                <button class="quantity-btn decrease">
                                    <i class="bi bi-dash"></i>
                                </button>

                                <input
                                    type="number"
                                    class="quantity-input"
                                    value="{{ session('basket.products.' . $product->id . '.count', 1) }}"
                                    min="1"
                                >

                                <button class="quantity-btn increase">
                                    <i class="bi bi-plus"></i>
                                </button>

                            </div>

                        </div>

                        <div class="product-actions">

                            {{-- todo: consider a form handled via abc_response — on click, request hits the backend, adds product to basket, response opens the modal with BasketItemData passed from the backend. Less custom JS per button. --}}
                            <button class="btn btn-primary add-to-cart-btn" data-id="{{ $product->id }}" data-count="{{ session('basket.products.' . $product->id . '.count', 1) }}">
                                <i class="bi bi-cart-plus"></i>
                                Add to Cart
                            </button>

                            <button class="btn btn-outline-primary buy-now-btn">
                                <i class="bi bi-lightning-fill"></i>
                                Buy Now
                            </button>

                            <button class="btn btn-outline-secondary wishlist-btn">
                                <i class="bi bi-heart"></i>
                            </button>

                        </div>

                        <div class="additional-info mt-4">

                            <div class="info-item">
                                <i class="bi bi-truck"></i>
                                <span>Free shipping on orders over $50</span>
                            </div>

                            <div class="info-item">
                                <i class="bi bi-arrow-repeat"></i>
                                <span>30-day return policy</span>
                            </div>

                            <div class="info-item">
                                <i class="bi bi-shield-check"></i>
                                <span>2-year warranty</span>
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
                                    Description
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
                                    Specifications
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
                                        Product Overview
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
                                            Product Information
                                        </h4>

                                        <div class="specs-table">

                                            <div class="specs-row">
                                                <div class="specs-label">
                                                    Article
                                                </div>

                                                <div class="specs-value">
                                                    {{ $product->article ?? '-' }}
                                                </div>
                                            </div>

                                            <div class="specs-row">
                                                <div class="specs-label">
                                                    Category
                                                </div>

                                                <div class="specs-value">
                                                    {{ $category->title ?? $category->name }}
                                                </div>
                                            </div>

                                            <div class="specs-row">
                                                <div class="specs-label">
                                                    Price
                                                </div>

                                                <div class="specs-value">
                                                    {{ $product->price ?? '-' }}
                                                </div>
                                            </div>

                                        </div>

                                    </div>

                                </div>

                            </div>

                        </div>

                    </div>

                </div>

            </div>

        </div>

    </section>

@endsection
