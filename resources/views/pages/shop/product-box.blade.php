<div class="col-lg-6">
    <div class="product-box">
        <div class="product-thumb">
            @if($product->price2)
                <span class="product-label product-label-sale">{{ t('shop.sale') }}</span>
            @endif

            @if($product->img)
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
                        href="{{ save_route('shop.product', [$product->category->slug, $product->slug. '-' . $product->id]) }}"
                        class="quick-action-btn"
                    >
                        <i class="bi bi-eye"></i>
                    </a>
                    --}}

                </div>

                <div class="add-to-cart-container">
                    <form data-abcr="{{ route('change_product_count') }}" action="{{ route('change_product_count') }}" method="POST">
                        @csrf
                        <input type="hidden" name="id" value="{{ $product->id }}">
                        <input type="hidden" name="count" value="1">
                        <input type="hidden" name="action" value="{{ \App\Modules\ShopBasket\Enums\BasketAction::ChangeCount->value }}">
                        <button type="submit" class="add-to-cart-btn">{{ t('shop.add_to_cart') }}</button>
                    </form>
                </div>
            </div>
        </div>

        <div class="product-content">
            <div class="product-details">
                <h3 class="product-title">
                    <a href="{{ save_route('shop.product', [$product->category->slug, $product->slug_with_id]) }}">
                        {{ $product->title ?? $product->name }}
                    </a>
                </h3>

                <div class="product-price">
                    @if($product->price2)
                        <span class="original">{{ t('shop.currency') }}{{ $product->price }}</span>
                        <span class="sale">{{ t('shop.currency') }}{{ $product->price2 }}</span>
                    @elseif($product->price)
                        <span>{{ t('shop.currency') }}{{ $product->price }}</span>
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
