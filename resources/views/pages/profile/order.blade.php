@extends('pages.profile.layouts.profile')

@section('profile_content')
    <div class="section-header" data-aos="fade-up">
        <a href="{{ module_url('profile', 'orders') }}" class="back-link">
            <i class="bi bi-arrow-left"></i>
            {{ t('profile.orders') }}
        </a>
        <h2>{{ t('profile.order_id') }}: #{{ $order->order_number }}</h2>
        <div class="order-date">{{ $order->created_at->format('d M, Y') }}</div>
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
                                            <span class="value status {{ $order->payment_status->value }}">
                                        {{ $order->payment_status->label() }}
                                    </span>
                                        </div>
                                        <div class="info-item">
                                            <span class="label">{{ t('profile.order_payment_type') }}</span>
                                            <span class="value">{{ $order->payment_type->label() }}</span>
                                        </div>
                                    </div>
                                </div>

                                <div class="detail-section">
                                    <h5>{{ t('profile.order_items') }} ({{ $order->items->count() }})</h5>
                                    <div class="order-items">
                                        @foreach($order->items as $item)
                                            <a href="{{module_url('shop',[$item->product->category->slug,$item->product->slug_with_id])}}"
                                               target="_blank">
                                                <div class="item">
                                                    @if($item->product->image)
                                                        <img src="{{ $item->product->image }}"
                                                             alt="{{ $item->product_name }}"
                                                             loading="lazy">
                                                    @endif
                                                    <div class="item-info">
                                                        <h6>{{ $item->product_name }}</h6>
                                                        <div class="item-meta">
                                                            <span
                                                                class="sku">{{ t('profile.sku') }}: {{ $item->product_sku }}</span>
                                                            <span
                                                                class="qty">{{ t('profile.qty') }}: {{ (int)$item->quantity }}</span>
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
                    <a href="#"
                       role="button"
                       class="btn btn-track">
                        {{ t('profile.order_pay') }}
                    </a>
                    <a href="#"
                       role="button"
                       class="btn btn-review">
                        {{ t('profile.order_review') }}
                    </a>
                    <a href="#"
                       role="button"
                       class="btn btn-reorder">
                        {{ t('profile.order_reorder') }}
                    </a>

                </div>
            </div>
        </div>
    </div>

@endsection
