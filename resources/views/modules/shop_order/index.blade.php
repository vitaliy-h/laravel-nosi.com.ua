@extends('layouts.app')

@section('body-class', 'order-page')

@section('content')
    @include('partials.page_title', [
        'title' => t('profile.order_id') . ': #' . $order->order_number,
        'lead' => $order->created_at_formatted,
    ])

    <div class="container page">
        <div class="cart-grid">
            <div class="panel-white">
                <h3>{{ t('profile.order_items') }} ({{ $order->items_count }})</h3>
                @include('modules.shop_order.partials.order_lines')
            </div>

            <aside class="summary">
                <h3>{{ t('profile.order_information') }}</h3>

                <div class="summary-row">
                    <span>{{ t('profile.order_status') }}</span>
                    <span class="badge {{ $order->payment_status }}">{{ $order->payment_status_label }}</span>
                </div>
                @if($order->customer_name)
                    <div class="summary-row"><span>{{ t('profile.user_name') }}</span><span>{{ $order->customer_name }}</span></div>
                @endif
                @if($order->customer_email)
                    <div class="summary-row"><span>{{ t('profile.user_email') }}</span><span>{{ $order->customer_email }}</span></div>
                @endif
                @if($order->customer_phone)
                    <div class="summary-row"><span>{{ t('profile.user_phone') }}</span><span>{{ $order->customer_phone }}</span></div>
                @endif
                @if($order->comment)
                    <div class="summary-row"><span>{{ t('profile.order_comment') }}</span><span>{!! nl2br(e($order->comment)) !!}</span></div>
                @endif
                @if($order->has_discount)
                    <div class="summary-row"><span>{{ t('shop.subtotal') }}</span><span>{{ $order->subtotal_amount_formatted }}</span></div>
                    <div class="summary-row discount"><span>{{ t('shop.discount') }} ({{ $order->promocode_code }})</span><span>−{{ $order->discount_amount_formatted }}</span></div>
                @endif

                <div class="summary-total">
                    <span>{{ t('profile.order_total') }}</span>
                    <span>{{ $order->total_amount_formatted }}</span>
                </div>

                <a href="{{ save_route('shop.index') }}" class="btn btn-line btn-block"><i class="bi bi-arrow-left"></i> {{ t('shop.continue_shopping') }}</a>
            </aside>
        </div>
    </div>
@endsection
