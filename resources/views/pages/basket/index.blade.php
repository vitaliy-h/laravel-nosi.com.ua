@extends('layouts.app')

{{--todo Str unused - remove--}}
@php use Illuminate\Support\Str; @endphp

@section('content')
    @include('blocks.page_title')

    <div class="container mt-4">
        @if($page->text)
            <div class="mb-4">
                {!! $page->text !!}
            </div>
        @endif

            <section id="cart" class="cart section">

                <div class="container aos-init aos-animate" data-aos="fade-up" data-aos-delay="100">

                    <div class="row g-4">
                        <div class="col-lg-8 aos-init aos-animate" data-aos="fade-up" data-aos-delay="200">
                            <div class="cart-items">
                                <div class="cart-header d-none d-lg-block">
                                    <div class="row align-items-center gy-4">
                                        <div class="col-lg-6">
                                            <h5>Product</h5>
                                            {{--todo text translation--}}
                                        </div>
                                        <div class="col-lg-2 text-center">
                                            <h5>Price</h5>
                                        </div>
                                        <div class="col-lg-2 text-center">
                                            <h5>Quantity</h5>
                                        </div>
                                        <div class="col-lg-2 text-center">
                                            <h5>Total</h5>
                                        </div>
                                    </div>
                                </div>

                                        @foreach($basket['products'] as $product)
                                            <div class="cart-item aos-init aos-animate checkout_item" data-product="{{$product['id']}}" data-aos="fade-up" data-aos-delay="100">
                                                <div class="row align-items-center gy-4">
                                                    <div class="col-lg-6 col-12 mb-3 mb-lg-0">
                                                        <div class="product-info d-flex align-items-center">
                                                            <div class="product-image">
                                                                <img src="{{$product['img']}}" alt="Product" class="img-fluid" loading="lazy">
                                                            </div>
                                                            <div class="product-details">
                                                                <h6 class="product-title">{{$product['name']}}</h6>
                                                                {{-- todo: these actions could be handled via a form processed as abc_response with action as a hidden field — less custom JS --}}
                                                                <button class="remove-item js_remove_checkout_product" data-product="{{$product['id']}}" type="button">
                                                                    <i class="bi bi-trash"></i> Remove
                                                                </button>
                                                            </div>
                                                        </div>
                                                    </div>
                                                    <div class="col-12 col-lg-2 text-center">
                                                        <div class="price-tag">
                                                            <span class="current-price">${{$product['price']}}</span>
                                                        </div>
                                                    </div>
                                                    <div class="col-12 col-lg-2 text-center">
                                                        <div class="quantity-selector">
                                                            <button class="quantity-btn decrease">
                                                                <i class="bi bi-dash"></i>
                                                            </button>
                                                            <input type="number" class="quantity-input js_change_checkout_products" data-product="{{$product['id']}}" value="{{$product['count']}}" min="1">
                                                            <button class="quantity-btn increase">
                                                                <i class="bi bi-plus"></i>
                                                            </button>
                                                        </div>
                                                    </div>
                                                    <div class="col-12 col-lg-2 text-center mt-3 mt-lg-0">
                                                        <div class="item-total">
                                                            <span>$<span class="js_product_checkout_total_{{$product['id']}}">{{$product['summ']}}</span></span>
                                                        </div>
                                                    </div>
                                                </div>
                                            </div><!-- End Cart Item -->


                                        @endforeach


                                            {{--
                                <div class="cart-actions">
                                    <div class="row g-3">
                                        <div class="col-lg-6 col-md-6">

                                            <div class="coupon-form">
                                                <div class="input-group">
                                                    <input type="text" class="form-control" placeholder="Coupon code">
                                                    <button class="btn btn-accent" type="button">Apply</button>
                                                </div>
                                            </div>
                                        </div>
                                        <div class="col-lg-6 col-md-6 text-md-end">
                                            <button class="btn btn-outline-accent me-2">
                                                <i class="bi bi-arrow-clockwise"></i> Update
                                            </button>

                                            <button class="btn btn-outline-danger">
                                                <i class="bi bi-trash"></i> Clear
                                            </button>

                                        </div>
                                    </div>
                                </div>

                                            --}}
                                            {{--
                                            --}}
                            </div>
                        </div>

                        <div class="col-lg-4 aos-init aos-animate" data-aos="fade-up" data-aos-delay="300">
                            <div class="cart-summary">
                                <h4 class="summary-title">Order Summary</h4>
                                {{--
                                <div class="summary-item">
                                    <span class="summary-label">Subtotal</span>
                                    <span class="summary-value">$269.96</span>
                                </div>

                                <div class="summary-item shipping-item">
                                    <span class="summary-label">Shipping</span>
                                    <div class="shipping-options">
                                        <div class="form-check">
                                            <input class="form-check-input" type="radio" name="shipping" id="standard" checked="">
                                            <label class="form-check-label" for="standard">
                                                Standard Delivery - $4.99
                                            </label>
                                        </div>
                                        <div class="form-check">
                                            <input class="form-check-input" type="radio" name="shipping" id="express">
                                            <label class="form-check-label" for="express">
                                                Express Delivery - $12.99
                                            </label>
                                        </div>
                                        <div class="form-check">
                                            <input class="form-check-input" type="radio" name="shipping" id="free">
                                            <label class="form-check-label" for="free">
                                                Free Shipping (Orders over $300)
                                            </label>
                                        </div>
                                    </div>
                                </div>

                                <div class="summary-item">
                                    <span class="summary-label">Tax</span>
                                    <span class="summary-value">$27.00</span>
                                </div>

                                <div class="summary-item discount">
                                    <span class="summary-label">Discount</span>
                                    <span class="summary-value">-$0.00</span>
                                </div>
                                --}}

                                <div class="summary-total">
                                    <span class="summary-label">Total</span>
                                    <span class="summary-value ">$<span class="js_checkout_total">{{$basket['total']}}</span></span>
                                </div>

                                <div class="checkout-button">
                                    <a href="{{module_url('basket',['checkout'])}}" class="btn btn-accent w-100">
                                        Proceed to Checkout <i class="bi bi-arrow-right"></i>
                                    </a>
                                </div>

                                <div class="continue-shopping">
                                    <a href="{{module_url('shop')}}" class="btn btn-link w-100">
                                        <i class="bi bi-arrow-left"></i> Continue Shopping
                                    </a>
                                </div>
                                {{--
                                <div class="payment-methods">
                                    <p class="payment-title">We Accept</p>
                                    <div class="payment-icons">
                                        <i class="bi bi-credit-card-2-front"></i>
                                        <i class="bi bi-paypal"></i>
                                        <i class="bi bi-wallet2"></i>
                                        <i class="bi bi-apple"></i>
                                        <i class="bi bi-google"></i>
                                    </div>
                                </div>
                                --}}
                            </div>
                        </div>
                    </div>

                </div>

            </section>







    </div>
@endsection
