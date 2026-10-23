{{-- Lines of an order — the order page and the order in the account. --}}
<div class="order-lines">
    @foreach($order->items as $item)
        <div class="order-line">
            @if($item->image)
                <img src="{{ $item->image }}" alt="{{ $item->name }}" loading="lazy">
            @else
                <span></span>
            @endif
            <div>
                @if($item->product_url)
                    <a href="{{ $item->product_url }}">{{ $item->name }}</a>
                @else
                    {{ $item->name }}
                @endif
                <small>
                    @if($item->article){{ t('profile.sku') }}: {{ $item->article }} · @endif{{ t('profile.qty') }}: {{ $item->quantity }}
                </small>
            </div>
            <b>{{ $item->total_formatted }}</b>
        </div>
    @endforeach
</div>
