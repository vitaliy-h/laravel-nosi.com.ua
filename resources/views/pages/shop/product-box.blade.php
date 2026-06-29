<div class="col-lg-6">
    <div class="product-box">
        <div class="product-thumb">
            @if($product->price2)
                <span class="product-label product-label-sale">Sale</span>
            @endif

            @if($product->image)
                <img
                    src="{{ $product->imageUrl('thumb') }}"
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
                    {{--
                    <button type="button" class="quick-action-btn">
                        <i class="bi bi-arrow-repeat"></i>
                    </button>
                    <a
                        href="{{ module_url('shop', [$product->category->slug, $product->slug. '-' . $product->id]) }}"
                        class="quick-action-btn"
                    >
                        <i class="bi bi-eye"></i>
                    </a>
                    --}}

                </div>

                {{-- todo: same note as in resources/views/pages/shop/product.blade.php:138 --}}
                <div class="add-to-cart-container">
                    <button type="button" class="add-to-cart-btn" data-id="{{$product->id}}" data-count="{{ session('basket.products.' . $product->id . '.count', 1) }}">Add to Cart</button>
                </div>
            </div>
        </div>

        <div class="product-content">
            <div class="product-details">
                <h3 class="product-title">
                    <a href="{{ module_url('shop', [$product->category->slug, $product->slug_with_id]) }}">
                        {{ $product->title ?? $product->name }}
                    </a>
                </h3>

                <div class="product-price">
                    @if($product->price2)
                        <span class="original">${{ $product->price }}</span>
                        <span class="sale">${{ $product->price2 }}</span>
                    @elseif($product->price)
                        <span>${{ $product->price }}</span>
                    @endif
                </div>
            </div>

            {{--
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
            --}}
        </div>
    </div>
</div>
