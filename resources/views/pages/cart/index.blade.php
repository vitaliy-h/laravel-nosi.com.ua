@extends('layouts.app')

@section('body-class', 'cart-page checkout-page')

@section('content')
    @include('blocks.page_title')

    <div class="container mt-4">
        @if($page->text)
            <div class="mb-4">
                {!! $page->text !!}
            </div>
        @endif

            <section id="cart" class="cart section checkout">

                <div class="checkout-container container aos-init aos-animate" data-aos="fade-up" data-aos-delay="100">

                    <div class="row g-4">
                        <div class="col-lg-8 aos-init aos-animate" data-aos="fade-up" data-aos-delay="200">
                            <div class="cart-items">
                                <div class="cart-header d-none d-lg-block">
                                    <div class="row align-items-center gy-4">
                                        <div class="col-lg-6">
                                            <h5>{{ t('shop.table_header_product') }}</h5>
                                        </div>
                                        <div class="col-lg-2 text-center">
                                            <h5>{{ t('shop.price') }}</h5>
                                        </div>
                                        <div class="col-lg-2 text-center">
                                            <h5>{{ t('shop.quantity') }}</h5>
                                        </div>
                                        <div class="col-lg-2 text-center">
                                            <h5>{{ t('shop.total') }}</h5>
                                        </div>
                                    </div>
                                </div>

                                @foreach($cart->items as $item)
                                    <div class="cart-item aos-init aos-animate checkout_item" data-product="{{$item->product->id}}" data-aos="fade-up" data-aos-delay="100">
                                        <div class="row align-items-center gy-4">
                                            <div class="col-lg-6 col-12 mb-3 mb-lg-0">
                                                <div class="product-info d-flex align-items-center">
                                                    <div class="product-image">
                                                        <img src="{{$item->product->image}}" alt="{{ $item->product_name }}" class="img-fluid" loading="lazy">
                                                    </div>
                                                    <div class="product-details">
                                                        <h6 class="product-title">{{$item->product_name}}</h6>

                                                        <form data-abcr="{{ route('change_product_count') }}" action="{{ route('change_product_count') }}" method="POST">
                                                            @csrf
                                                            <input type="hidden" name="id" value="{{$item->product->id}}">
                                                            <input type="hidden" name="count" value="0">
                                                            <input type="hidden" name="cart_uuid" value="{{$cart->uuid}}">
                                                            <input type="hidden" name="action" value="{{ \App\Modules\ShopCart\Enums\CartAction::DeleteCheckout->value }}">
                                                            <button type="submit" class="remove-item">
                                                                <i class="bi bi-trash"></i> {{ t('shop.remove') }}
                                                            </button>
                                                        </form>
                                                    </div>
                                                </div>
                                            </div>
                                            <div class="col-12 col-lg-2 text-center">
                                                <div class="price-tag">
                                                    <span class="current-price">{{ t('shop.currency') }}{{$item->selling_price}}</span>
                                                </div>
                                            </div>
                                            <div class="col-12 col-lg-2 text-center">
                                                <form data-abcr="{{ route('change_product_count') }}" action="{{ route('change_product_count') }}" method="POST">
                                                    @csrf
                                                    <input type="hidden" name="id" value="{{$item->product->id}}">
                                                    <input type="hidden" name="cart_uuid" value="{{$cart->uuid}}">
                                                    <input type="hidden" name="action" value="{{ \App\Modules\ShopCart\Enums\CartAction::ChangeCountCheckout->value }}">
                                                    <div class="quantity-selector">
                                                        <button type="button" class="quantity-btn decrease">
                                                            <i class="bi bi-dash"></i>
                                                        </button>
                                                        <input type="number" name="count" class="quantity-input js_change_checkout_products" data-product="{{$item->product->id}}" value="{{$item->quantity}}" min="1">
                                                        <button type="button" class="quantity-btn increase">
                                                            <i class="bi bi-plus"></i>
                                                        </button>
                                                    </div>
                                                </form>
                                            </div>
                                            <div class="col-12 col-lg-2 text-center mt-3 mt-lg-0">
                                                <div class="item-total">
                                                    <span>{{ t('shop.currency') }}<span class="js_product_checkout_total_{{$item->product->id}}">{{$item->total}}</span></span>
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

                            <form id="checkout-form" class="checkout-form checkout-section" action="{{ route('order.create') }}" method="POST">
                                @csrf
                                <input type="hidden" name="cart_uuid" value="{{ $cart->uuid }}">
                                <!-- Customer Information -->
                                <div class="checkout-section" id="customer-info">
                                    <div class="section-header">
                                        <h3>{{ t('shop.customer_information') }}</h3>
                                    </div>
                                    <div class="section-content">
                                        @php $user = auth()->user(); @endphp
                                        <div class="row">
                                            <div class="col-md-6 form-group">
                                                <label for="user_name">{{t('profile.user_name')}}</label>
                                                <input type="text" name="user_name" class="form-control" id="user_name" placeholder="{{ t('shop.placeholder_first_name') }}" value="{{ $user?->name }}">
                                            </div>
                                            <div class="col-md-6 form-group">
                                                <label for="user_surname">{{t('profile.user_surname')}}</label>
                                                <input type="text" name="user_surname" class="form-control" id="user_surname" placeholder="{{ t('shop.placeholder_last_name') }}" value="{{ $user?->surname }}">
                                            </div>
                                        </div>
                                        <div class="row">
                                            <div class="col-md-6 form-group">
                                                <label for="user_email">{{t('profile.user_email')}}</label>
                                                <input type="email" class="form-control" name="user_email" id="user_email" placeholder="{{ t('shop.placeholder_email') }}" required="" value="{{ $user?->email }}">
                                            </div>
                                            <div class="col-md-6 form-group">
                                                <label for="user_phone">{{t('profile.user_phone')}}</label>
                                                <input type="tel" class="form-control" name="user_phone" id="user_phone" placeholder="{{ t('shop.placeholder_phone') }}" value="{{ $user?->phone }}">
                                            </div>
                                        </div>

                                        <div class="form-group">
                                            <label for="comment">{{t('profile.order_comment')}}</label>
                                            <textarea class="form-control" name="comment"></textarea>
                                        </div>

                                        <div class="place-order-container">
                                            <button type="submit" class="btn btn-primary place-order-btn">
                                                <span class="btn-text">{{ t('shop.place_order') }}</span>
                                            </button>
                                        </div>
                                    </div>
                                </div>

                            </form>

                        </div>


                        <div class="col-lg-4 aos-init aos-animate" data-aos="fade-up" data-aos-delay="300">
                            <div class="cart-summary">
                                <h4 class="summary-title">{{ t('shop.order_summary') }}</h4>
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
                                    <span class="summary-label">{{ t('shop.total') }}</span>
                                    <span class="summary-value ">{{ t('shop.currency') }}<span class="js_checkout_total">{{$cart->total_amount}}</span></span>
                                </div>

                                <div class="checkout-button">
                                    <button type="submit" form="checkout-form" class="btn btn-accent w-100">
                                        {{ t('shop.place_order') }} <i class="bi bi-arrow-right"></i>
                                    </button>
                                </div>

                                <div class="continue-shopping">
                                    <a href="{{save_route('shop.index')}}" class="btn btn-link w-100">
                                        <i class="bi bi-arrow-left"></i> {{ t('shop.continue_shopping') }}
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

