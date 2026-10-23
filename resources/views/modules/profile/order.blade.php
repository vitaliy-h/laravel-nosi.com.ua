@extends('modules.profile.layouts.profile')

@section('profile_content')
    <a href="{{ save_route('profile.orders') }}" class="back-link"><i class="bi bi-arrow-left"></i> {{ t('profile.orders') }}</a>

    <div class="account-head">
        <h2>{{ t('profile.order_id') }}: #{{ $order->order_number }}</h2>
        <span class="muted">{{ $order->created_at_formatted }}</span>
    </div>

    <div class="settings">
        <section class="panel-white">
            <h3>{{ t('profile.order_information') }}</h3>
            <div class="order-meta" style="border-top: 0; padding-top: 0;">
                <div><small>{{ t('profile.order_status') }}</small><span class="badge {{ $order->payment_status }}">{{ $order->payment_status_label }}</span></div>
                <div><small>{{ t('profile.order_payment_type') }}</small><span>{{ $order->payment_type_label }}</span></div>
                <div><small>{{ t('profile.order_total') }}</small><span>{{ $order->total_amount_formatted }}</span></div>
            </div>
        </section>

        <section class="panel-white">
            <h3>{{ t('profile.order_items') }} ({{ $order->items_count }})</h3>
            @include('modules.shop_order.partials.order_lines')
        </section>

        <section class="panel-white">
            <h3>{{ t('profile.order_price_details') }}</h3>
            @if($order->has_discount)
                <div class="summary-row"><span>{{ t('shop.subtotal') }}</span><span>{{ $order->subtotal_amount_formatted }}</span></div>
                <div class="summary-row discount"><span>{{ t('shop.discount') }} ({{ $order->promocode_code }})</span><span>−{{ $order->discount_amount_formatted }}</span></div>
            @endif
            <div class="summary-total" style="margin-bottom: 0;">
                <span>{{ t('profile.order_total') }}</span>
                <span>{{ $order->total_amount_formatted }}</span>
            </div>
        </section>
    </div>
@endsection
