@extends('pages.profile.layouts.profile')

@section('profile_content')
    <div class="section-header" data-aos="fade-up">
        <h2>{{ t('profile.orders') }}</h2>
        <div class="header-actions">
            <form method="GET"
                  action="{{ save_route('profile.orders') }}"
                  class="search-box">
                @if($filter->status)
                    <input type="hidden" name="status" value="{{ $filter->status->value }}">
                @endif
                <i class="bi bi-search"></i>
                <input type="text"
                       name="search"
                       value="{{ $filter->search }}"
                       placeholder="{{ t('profile.orders_search') }}">
            </form>

            <div class="dropdown">
                <button class="filter-btn" data-bs-toggle="dropdown">
                    <i class="bi bi-funnel"></i>
                    <span>{{ $filter->status ? $filter->status->label() : t('profile.orders_all') }}</span>
                </button>
                <ul class="dropdown-menu">
                    <li>
                        <a class="dropdown-item {{ !$filter->status ? 'active' : '' }}"
                           href="{{ save_route('profile.orders') }}{{ $filter->search ? '?search=' . urlencode($filter->search) : '' }}">
                            {{ t('profile.orders_all') }}
                        </a>
                    </li>
                    @foreach(\App\Modules\Orders\Enums\PaymentStatusEnum::cases() as $case)
                        <li>
                            @php
                                $statusUrl = save_route('profile.orders')
                                    . '?status=' . $case->value
                                    . ($filter->search ? '&search=' . urlencode($filter->search) : '');
                            @endphp
                            <a class="dropdown-item {{ $filter->status === $case ? 'active' : '' }}"
                               href="{{ $statusUrl }}">
                                {{ $case->label() }}
                            </a>
                        </li>
                    @endforeach
                </ul>
            </div>
        </div>
    </div>

    <div class="orders-grid">
        @forelse($orders as $order)
            <div class="order-card" data-aos="fade-up">
                <div class="order-header">
                    <div class="order-id">
                        <span class="label">{{ t('profile.order_id') }}:</span>
                        <span class="value">#{{ $order->order_number }}</span>
                    </div>
                    <div class="order-date">{{ $order->created_at->format('d M, Y') }}</div>
                </div>

                <div class="order-content">
                    @if($order->items->isNotEmpty())
                        <div class="product-grid">
                            @foreach($order->items as $item)
                                @if($item->product)
                                    <img src="{{ $item->product->image }}" alt="{{ $item->product_name }}" loading="lazy">
                                @endif
                            @endforeach
                        </div>
                    @endif

                    <div class="order-info">
                        <div class="info-row">
                            <span>{{ t('profile.order_status') }}</span>
                            <span class="status {{ $order->payment_status->value }}">
                                {{ $order->payment_status->label() }}
                            </span>
                        </div>
                        <div class="info-row">
                            <span>{{ t('profile.order_items') }}</span>
                            <span>{{ $order->items->count() }} {{ t('profile.order_items_unit') }}</span>
                        </div>
                        <div class="info-row">
                            <span>{{ t('profile.order_total') }}</span>
                            <span class="price">{{ $order->total_amount_formatted }}</span>
                        </div>
                    </div>
                </div>

                <div class="order-footer">
                    @if($order->permissions->canPay())
                        <a href="#"
                           role="button"
                           class="btn btn-track">
                            {{ t('profile.order_pay') }}
                        </a>
                    @endif
                    <a href="{{ save_route('profile.orders.show', $order->id) }}"
                       role="button"
                       class="btn btn-details">
                        {{ t('profile.order_view_details') }}
                    </a>
                </div>
            </div>
        @empty
            <div class="empty-state" data-aos="fade-up">
                <i class="bi bi-bag-x"></i>
                <p>{{ t('profile.orders_empty') }}</p>
            </div>
        @endforelse
    </div>

    {{ $orders->links('components.pagination.profile') }}
@endsection
