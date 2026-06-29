@extends('layouts.app')

@section('content')
    <div class="page-title light-background">
        <div class="container d-lg-flex justify-content-between align-items-center">
            <h1 class="mb-2 mb-lg-0">{{ t('profile.order_id') }}: #{{ $order->order_number }}</h1>
            <div class="text-muted">{{ $order->created_at->format('d M, Y') }}</div>
        </div>
    </div>

    <section class="cart section">
        <div class="container" data-aos="fade-up" data-aos-delay="100">
            <div class="row g-4">

                {{-- Items --}}
                <div class="col-lg-8">
                    <div class="cart-items">
                        <div class="cart-header">
                            <h5>{{ t('profile.order_items') }} ({{ $order->items->count() }})</h5>
                        </div>

                        @foreach($order->items as $item)
                            <div class="cart-item">
                                <div class="d-flex align-items-center">
                                    @if($item->product?->image)
                                        <div class="product-info">
                                            <div class="product-image">
                                                <img src="{{ $item->product->image }}" alt="{{ $item->product_name }}">
                                            </div>
                                        </div>
                                    @endif
                                    <div class="product-info flex-grow-1 {{ $item->product?->image ? '' : 'ms-0' }}">
                                        <div class="product-details">
                                            @if($item->product && $item->product->category)
                                                <a href="{{ module_url('shop', [$item->product->category->slug, $item->product->slug_with_id]) }}"
                                                   class="product-title" target="_blank">
                                                    {{ $item->product_name }}
                                                </a>
                                            @else
                                                <div class="product-title">{{ $item->product_name }}</div>
                                            @endif
                                            <div class="product-meta">
                                                @if($item->product_sku)
                                                    <span>{{ t('profile.sku') }}: {{ $item->product_sku }}</span>
                                                @endif
                                                <span>{{ t('profile.qty') }}: {{ (int)$item->quantity }}</span>
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
                                    @if($order->payment_status->value === 'paid') bg-success
                                    @elseif($order->payment_status->value === 'processing') bg-warning text-dark
                                    @else bg-secondary @endif">
                                    {{ $order->payment_status->label() }}
                                </span>
                            </span>
                        </div>

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
