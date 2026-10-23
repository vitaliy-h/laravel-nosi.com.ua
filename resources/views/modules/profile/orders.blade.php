@extends('modules.profile.layouts.profile')

@section('profile_content')
    <div class="account-head">
        <h2>{{ t('profile.orders') }}</h2>

        <form method="GET" action="{{ save_route('profile.orders') }}">
            <div class="search-input">
                <input type="text" name="search" class="input" value="{{ $filter->search }}" placeholder="{{ t('profile.orders_search') }}" aria-label="{{ t('profile.orders_search') }}">
                <button type="submit" aria-label="{{ t('profile.orders_search') }}" style="top: 3px;"><i class="bi bi-search"></i></button>
            </div>
            <select name="status" class="input" onchange="this.form.submit()" aria-label="{{ t('profile.order_status') }}">
                <option value="">{{ t('profile.orders_all') }}</option>
                @foreach(\App\Modules\ShopOrder\Enums\PaymentStatusEnum::cases() as $case)
                    <option value="{{ $case->value }}" @selected($filter->status === $case)>{{ $case->label() }}</option>
                @endforeach
            </select>
        </form>
    </div>

    <div class="orders">
        @forelse($orders as $order)
            <article class="order-card">
                <div class="order-card-head">
                    <b>#{{ $order->order_number }}</b>
                    <span class="muted">{{ $order->created_at_formatted }}</span>
                </div>

                @if($order->items->isNotEmpty())
                    <div class="order-thumbs">
                        @foreach($order->items as $item)
                            @if($item->image)
                                <img src="{{ $item->image }}" alt="{{ $item->name }}" loading="lazy">
                            @endif
                        @endforeach
                    </div>
                @endif

                <div class="order-meta">
                    <div><small>{{ t('profile.order_status') }}</small><span class="badge {{ $order->payment_status }}">{{ $order->payment_status_label }}</span></div>
                    <div><small>{{ t('profile.order_items') }}</small><span>{{ $order->items_count }} {{ t('profile.order_items_unit') }}</span></div>
                    <div><small>{{ t('profile.order_total') }}</small><span>{{ $order->total_amount_formatted }}</span></div>
                </div>

                <div class="order-card-foot">
                    <a href="{{ save_route('profile.orders.show', $order->id) }}" class="btn btn-line btn-sm">{{ t('profile.order_view_details') }}</a>
                </div>
            </article>
        @empty
            <div class="empty-state">
                <i class="bi bi-bag-x"></i>
                {{ t('profile.orders_empty') }}
            </div>
        @endforelse
    </div>

    {{ $orders->links() }}
@endsection
