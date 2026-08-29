@extends('pages.profile.layouts.profile')

@section('profile_content')
    <div class="section-header" data-aos="fade-up">
        <a href="{{ save_route('profile.orders') }}" class="back-link">
            <i class="bi bi-arrow-left"></i>
            {{ t('profile.orders') }}
        </a>
        <h2>{{ t('profile.order_id') }}: #{{ $order->order_number }}</h2>
        <div class="order-date">{{ $order->created_at_formatted }}</div>
    </div>


    <div class="orders-grid">
        <div class="order-card">
            <div class="order-content">
                <div class="order-details">
                    <div class="order-info">
                        <div class="order-details">
                            <div class="details-content" data-aos="fade-up">

                                <div class="detail-section">
                                    <h5>{{ t('profile.order_information') }}</h5>
                                    <div class="info-grid info-row">
                                        <div class="info-item">
                                            <span class="label">{{ t('profile.order_status') }}</span>
                                            <span class="value status {{ $order->payment_status }}">
                                        {{ $order->payment_status_label }}
                                    </span>
                                        </div>
                                        <div class="info-item">
                                            <span class="label">{{ t('profile.order_payment_type') }}</span>
                                            <span class="value">{{ $order->payment_type_label }}</span>
                                        </div>
                                    </div>
                                </div>

                                <div class="detail-section">
                                    <h5>{{ t('profile.order_items') }} ({{ $order->items_count }})</h5>
                                    <div class="order-items">
                                        @foreach($order->items as $item)
                                            <a href="{{ $item->product_url }}"
                                               target="_blank">
                                                <div class="item">
                                                    @if($item->image)
                                                        <img src="{{ $item->image }}"
                                                             alt="{{ $item->name }}"
                                                             loading="lazy">
                                                    @endif
                                                    <div class="item-info">
                                                        <h6>{{ $item->name }}</h6>
                                                        <div class="item-meta">
                                                            <span
                                                                class="sku">{{ t('profile.sku') }}: {{ $item->article }}</span>
                                                            <span
                                                                class="qty">{{ t('profile.qty') }}: {{ $item->quantity }}</span>
                                                        </div>
                                                    </div>
                                                    <div class="item-price">{{ $item->total_formatted }}</div>
                                                </div>
                                            </a>
                                        @endforeach
                                    </div>
                                </div>

                                <div class="detail-section">
                                    <h5>{{ t('profile.order_price_details') }}</h5>
                                    <div class="price-breakdown">
                                        @if($order->has_discount)
                                            <div class="price-row">
                                                <span>{{ t('shop.subtotal') }}</span>
                                                <span>{{ $order->subtotal_amount_formatted }}</span>
                                            </div>
                                            <div class="price-row discount">
                                                <span>{{ t('shop.discount') }} ({{ $order->promocode_code }})</span>
                                                <span>-{{ $order->discount_amount_formatted }}</span>
                                            </div>
                                        @endif
                                        <div class="price-row total">
                                            <span>{{ t('profile.order_total') }}</span>
                                            <span>{{ $order->total_amount_formatted }}</span>
                                        </div>
                                    </div>
                                </div>

                            </div>
                        </div>
                    </div>
                </div>
                <div class="order-footer">
                    @if($order->can_pay)
                        <a href="#"
                           role="button"
                           class="btn btn-track">
                            {{ t('profile.order_pay') }}
                        </a>
                    @endif
                </div>
            </div>
        </div>
    </div>

@endsection
