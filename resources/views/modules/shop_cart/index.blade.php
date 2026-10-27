@extends('layouts.app')

@section('body-class', 'cart-page')

@section('content')
    @include('partials.page_title')

    <div class="container page">
        @include('partials.page_content')

        <div class="cart-grid">
            <div>
                <div class="cart-list">
                    @foreach($cart->items as $item)
                        <div class="cart-row checkout_item" data-product="{{ $item->product_id }}" data-line="{{ $item->line_key }}">
                            <img src="{{ $item->image }}" alt="{{ $item->name }}" loading="lazy">

                            <div>
                                <div class="cart-row-title">{{ $item->name }}</div>
                                <div class="cart-row-price">{{ $item->selling_price_formatted }}</div>
                                <form data-abcr="{{ save_route('change_product_count') }}" action="{{ save_route('change_product_count') }}" method="POST">
                                    @csrf
                                    <input type="hidden" name="id" value="{{ $item->product_id }}">
                                    <input type="hidden" name="pack" value="{{ $item->pack_quantity }}">
                                    <input type="hidden" name="count" value="0">
                                    <input type="hidden" name="cart_uuid" value="{{ $cart->uuid }}">
                                    <input type="hidden" name="action" value="{{ \App\Modules\ShopCart\Enums\CartAction::DeleteCheckout->value }}">
                                    <button type="submit" class="btn-text"><i class="bi bi-trash3"></i> {{ t('shop.remove') }}</button>
                                </form>
                            </div>

                            <form data-abcr="{{ save_route('change_product_count') }}" action="{{ save_route('change_product_count') }}" method="POST">
                                @csrf
                                <input type="hidden" name="id" value="{{ $item->product_id }}">
                                <input type="hidden" name="pack" value="{{ $item->pack_quantity }}">
                                <input type="hidden" name="cart_uuid" value="{{ $cart->uuid }}">
                                <input type="hidden" name="action" value="{{ \App\Modules\ShopCart\Enums\CartAction::ChangeCountCheckout->value }}">
                                <div class="qty">
                                    <button type="button" data-qty="-1" aria-label="−"><i class="bi bi-dash"></i></button>
                                    <input type="number" name="count" class="js_change_checkout_products" data-product="{{ $item->product_id }}" value="{{ $item->quantity }}" min="1" aria-label="{{ t('shop.quantity') }}">
                                    <button type="button" data-qty="1" aria-label="+"><i class="bi bi-plus"></i></button>
                                </div>
                            </form>

                            <div class="cart-row-total"><span class="js_product_checkout_total_{{ $item->line_key }}">{{ $item->total_formatted }}</span></div>
                        </div>
                    @endforeach
                </div>

                @php($user = auth()->user())
                <form id="checkout-form" class="panel-white cart-form" action="{{ route('order.create') }}" method="POST">
                    @csrf
                    <input type="hidden" name="cart_uuid" value="{{ $cart->uuid }}">

                    <h3>{{ t('shop.customer_information') }}</h3>

                    <div class="form-grid">
                        <div class="field">
                            <label for="user_name">{{ t('profile.user_name') }}</label>
                            <input type="text" name="user_name" @class(['input', 'is-invalid' => $errors->has('user_name')]) id="user_name" placeholder="{{ t('shop.placeholder_first_name') }}" value="{{ old('user_name', $user?->name) }}">
                            @error('user_name')<div class="field-error">{{ $message }}</div>@enderror
                        </div>
                        <div class="field">
                            <label for="user_surname">{{ t('profile.user_surname') }}</label>
                            <input type="text" name="user_surname" @class(['input', 'is-invalid' => $errors->has('user_surname')]) id="user_surname" placeholder="{{ t('shop.placeholder_last_name') }}" value="{{ old('user_surname', $user?->surname) }}">
                            @error('user_surname')<div class="field-error">{{ $message }}</div>@enderror
                        </div>
                        <div class="field">
                            <label for="user_email">{{ t('profile.user_email') }}</label>
                            <input type="email" name="user_email" @class(['input', 'is-invalid' => $errors->has('user_email')]) id="user_email" placeholder="{{ t('shop.placeholder_email') }}" value="{{ old('user_email', $user?->email) }}" pattern="[^@\s]+@[^@\s]+\.[^@\s]+" title="{{ t('errors.email_invalid') }}" required>
                            @error('user_email')<div class="field-error">{{ $message }}</div>@enderror
                        </div>
                        <div class="field">
                            <label for="user_phone">{{ t('profile.user_phone') }}</label>
                            <input type="tel" name="user_phone" @class(['input', 'is-invalid' => $errors->has('user_phone')]) id="user_phone" placeholder="{{ t('shop.placeholder_phone') }}" value="{{ old('user_phone', $user?->phone) }}" inputmode="tel" pattern="\+?(38)?[\s\-\(\)]*0([\s\-\(\)]*\d){9}[\s\-\(\)]*" title="{{ t('errors.phone_invalid') }}" required>
                            @error('user_phone')<div class="field-error">{{ $message }}</div>@enderror
                        </div>
                        <div class="field full">
                            <label for="comment">{{ t('profile.order_comment') }}</label>
                            <textarea name="comment" id="comment" @class(['input', 'is-invalid' => $errors->has('comment')]) rows="3">{{ old('comment') }}</textarea>
                            @error('comment')<div class="field-error">{{ $message }}</div>@enderror
                        </div>
                    </div>
                </form>
            </div>

            <aside class="summary">
                <h3>{{ t('shop.order_summary') }}</h3>

                @if(\App\Modules\Core\Support\ModuleGate::isEnabled('shop_promocodes'))
                    @include('modules.shop_cart.partials.promocode', ['cart' => $cart, 'result' => null])
                @endif
                @include('modules.shop_cart.partials.summary_discount', ['cart' => $cart])

                <div class="summary-total">
                    <span>{{ t('shop.total') }}</span>
                    <span class="js_checkout_total">{{ $cart->total_amount_formatted }}</span>
                </div>

                <button type="submit" form="checkout-form" class="btn btn-accent btn-block">{{ t('shop.place_order') }} <i class="bi bi-arrow-right"></i></button>
                <a href="{{ save_route('shop.index') }}" class="btn btn-line btn-block"><i class="bi bi-arrow-left"></i> {{ t('shop.continue_shopping') }}</a>
            </aside>
        </div>
    </div>
@endsection
