@extends('layouts.app')

@section('content')
    <div class="page-title light-background">
        <div class="container d-lg-flex justify-content-between align-items-center">
            <h1 class="mb-2 mb-lg-0">{{ t('profile.order_id') }}: #{{ $order->order_number }}</h1>
            <div class="text-muted">{{ $order->created_at_formatted }}</div>
        </div>
    </div>

    <section class="cart section">
        <div class="container" data-aos="fade-up" data-aos-delay="100">
            <div class="row g-4">

                {{-- Items --}}
                <div class="col-lg-8">
                    <div class="cart-items">
                        <div class="cart-header">
                            <h5>{{ t('profile.order_items') }} ({{ $order->items_count }})</h5>
                        </div>

                        @foreach($order->items as $item)
                            <div class="cart-item">
                                <div class="d-flex align-items-center">
                                    @if($item->image)
                                        <div class="product-info">
                                            <div class="product-image">
                                                <img src="{{ $item->image }}" alt="{{ $item->name }}">
                                            </div>
                                        </div>
                                    @endif
                                    <div class="product-info flex-grow-1 {{ $item->image ? '' : 'ms-0' }}">
                                        <div class="product-details">
                                            @if($item->product_url)
                                                <a href="{{ $item->product_url }}"
                                                   class="product-title" target="_blank">
                                                    {{ $item->name }}
                                                </a>
                                            @else
                                                <div class="product-title">{{ $item->name }}</div>
                                            @endif
                                            <div class="product-meta">
                                                @if($item->article)
                                                    <span>{{ t('profile.sku') }}: {{ $item->article }}</span>
                                                @endif
                                                <span>{{ t('profile.qty') }}: {{ $item->quantity }}</span>
                                            </div>
                                        </div>
                                    </div>
                                    <div class="item-total ms-3">
                                        <span>{{ $item->total_formatted }}</span>
                                    </div>
                                </div>
                            </div>
                        @endforeach
                    </div>
                </div>

                {{-- Summary --}}
                <div class="col-lg-4">
                    <div class="cart-summary">
                        <h4 class="summary-title">{{ t('profile.order_information') }}</h4>

                        <div class="summary-item">
                            <span class="summary-label">{{ t('profile.order_status') }}</span>
                            <span class="summary-value">
                                <span class="badge rounded-pill
                                    @if($order->payment_status === 'paid') bg-success
                                    @elseif($order->payment_status === 'processing') bg-warning text-dark
                                    @else bg-secondary @endif">
                                    {{ $order->payment_status_label }}
                                </span>
                            </span>
                        </div>

                        @if($order->customer_name)
                            <div class="summary-item">
                                <span class="summary-label">{{ t('profile.user_name') }}</span>
                                <span class="summary-value">{{ $order->customer_name }}</span>
                            </div>
                        @endif

                        @if($order->customer_email)
                            <div class="summary-item">
                                <span class="summary-label">{{ t('profile.user_email') }}</span>
                                <span class="summary-value">{{ $order->customer_email }}</span>
                            </div>
                        @endif

                        @if($order->customer_phone)
                            <div class="summary-item">
                                <span class="summary-label">{{ t('profile.user_phone') }}</span>
                                <span class="summary-value">{{ $order->customer_phone }}</span>
                            </div>
                        @endif

                        @if($order->comment)
                            <div class="summary-item">
                                <span class="summary-label">{{ t('profile.order_comment') }}</span>
                                <span class="summary-value">{!! nl2br(e($order->comment)) !!}</span>
                            </div>
                        @endif

                        @if($order->has_discount)
                            <div class="summary-item">
                                <span class="summary-label">{{ t('shop.subtotal') }}</span>
                                <span class="summary-value">{{ $order->subtotal_amount_formatted }}</span>
                            </div>
                            <div class="summary-item discount">
                                <span class="summary-label">{{ t('shop.discount') }} ({{ $order->promocode_code }})</span>
                                <span class="summary-value">-{{ $order->discount_amount_formatted }}</span>
                            </div>
                        @endif

                        <div class="summary-total">
                            <span class="summary-label">{{ t('profile.order_total') }}</span>
                            <span class="summary-value">{{ $order->total_amount_formatted }}</span>
                        </div>
                    </div>
                </div>

            </div>
        </div>
    </section>
@endsection
