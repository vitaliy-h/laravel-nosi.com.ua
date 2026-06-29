<div class="site-modal__title">
    {{$product->title}}
</div>
<div class="">
    <div class="">
        <div class="cart-item aos-init aos-animate" data-aos="fade-up" data-aos-delay="100">
            <div class="row align-items-center gy-4">
                <div class="col-lg-6 col-12 mb-3 mb-lg-0">
                    <div class="product-info d-flex align-items-center">
                        <div class="product-image">
                            @if($product->image)
                                <img
                                    src="{{ $product->image }}"
                                    alt="{{ $product->title ?? $product->name }}"
                                    class="img-fluid" loading="lazy">
                            @endif
                        </div>
                        <div class="product-details">
                            <h6 class="product-title">{{$product->title}}</h6>
                            <button data-product="{{$product->id}}" class="remove-item js_delete_product_modal" type="button">
                                <i class="bi bi-trash"></i> Remove
                            </button>
                        </div>
                    </div>
                </div>
                <div class="col-12 col-lg-2 text-center">
                    <div class="price-tag">
                        <span class="current-price">${{$product->price}}</span>
                    </div>
                </div>
                <div class="col-12 col-lg-2 text-center">
                    <div class="quantity-selector">
                        <button class="quantity-btn decrease">
                            <i class="bi bi-dash"></i>
                        </button>
                        <input type="number" data-product="{{$product->id}}" class="quantity-input" value="{{ $product->_count }}" min="1" >
                        <button class="quantity-btn increase">
                            <i class="bi bi-plus"></i>
                        </button>
                    </div>
                </div>
                <div class="col-12 col-lg-2 text-center mt-3 mt-lg-0">
                    <div class="item-total">
                        <span>$<span class="js_product_total">{{ $product->_sum }}</span></span>
                    </div>
                </div>
            </div>
        </div>

    </div>
</div>
