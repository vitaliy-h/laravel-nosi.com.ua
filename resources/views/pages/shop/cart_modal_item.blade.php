<div class="site-modal__title">
    {{$item->product->title}}
</div>
<div class="">
    <div class="">
        <div class="cart-item aos-init aos-animate" data-aos="fade-up" data-aos-delay="100">
            <div class="row align-items-center gy-4">
                <div class="col-lg-6 col-12 mb-3 mb-lg-0">
                    <div class="product-info d-flex align-items-center">
                        <div class="product-image">
                            @if($item->product->img)
                                <img
                                    src="{{ $item->product->image }}"
                                    alt="{{ $item->product->title }}"
                                    class="img-fluid" loading="lazy">
                            @endif
                        </div>
                        <div class="product-details">
                            <h6 class="product-title">{{$item->product->title}}</h6>
                            <form data-abcr="{{ save_route('change_product_count') }}" action="{{ save_route('change_product_count') }}" method="POST">
                                @csrf
                                <input type="hidden" name="id" value="{{$item->product->id}}">
                                <input type="hidden" name="count" value="0">
                                <input type="hidden" name="action" value="{{ \App\Modules\ShopCart\Enums\CartAction::DeleteModal->value }}">
                                <button type="submit" class="remove-item">
                                    <i class="bi bi-trash"></i> {{ t('shop.remove') }}
                                </button>
                            </form>
                        </div>
                    </div>
                </div>
                <div class="col-12 col-lg-2 text-center">
                    <div class="price-tag">
                        <span class="current-price">${{$item->selling_price}}</span>
                    </div>
                </div>
                <div class="col-12 col-lg-2 text-center">
                    <form data-abcr="{{ save_route('change_product_count') }}" action="{{ save_route('change_product_count') }}" method="POST">
                        @csrf
                        <input type="hidden" name="id" value="{{$item->product->id}}">
                        <input type="hidden" name="action" value="{{ \App\Modules\ShopCart\Enums\CartAction::ChangeCountModal->value }}">
                        <div class="quantity-selector">
                            <button type="button" class="quantity-btn decrease">
                                <i class="bi bi-dash"></i>
                            </button>
                            <input type="number" name="count" data-product="{{$item->product->id}}" class="quantity-input" value="{{ $item->quantity }}" min="1" >
                            <button type="button" class="quantity-btn increase">
                                <i class="bi bi-plus"></i>
                            </button>
                        </div>
                    </form>
                </div>
                <div class="col-12 col-lg-2 text-center mt-3 mt-lg-0">
                    <div class="item-total">
                        <span>$<span class="js_product_total">{{ $item->total }}</span></span>
                    </div>
                </div>
            </div>
        </div>

    </div>
</div>
