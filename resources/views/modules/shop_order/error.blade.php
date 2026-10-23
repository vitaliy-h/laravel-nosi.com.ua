@extends('layouts.app')

@section('body-class', 'order-result-page')

@section('content')
    <div class="container page">
        <div class="result">
            <div class="result-icon fail"><i class="bi bi-x-lg"></i></div>
            <h2>{{ t('shop.payment_error_title') }}</h2>
            <p>{{ t('shop.payment_error_description') }}</p>

            <div class="summary-list">
                <div class="summary-row"><span>{{ t('profile.order_id') }}</span><span>#{{ $order->order_number }}</span></div>
                <div class="summary-row"><span>{{ t('profile.order_status') }}</span><span class="badge {{ $order->payment_status }}">{{ $order->payment_status_label }}</span></div>
                <div class="summary-row"><span>{{ t('profile.order_total') }}</span><span>{{ $order->total_amount_formatted }}</span></div>
            </div>

            <a href="{{ $order->url }}" class="btn btn-line"><i class="bi bi-receipt"></i> {{ t('shop.view_order') }}</a>
        </div>
    </div>
@endsection
