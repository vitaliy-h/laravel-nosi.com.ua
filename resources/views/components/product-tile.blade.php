{{-- Product card of the Nosi design: two photos (second on hover), badges, wishlist,
     add-to-cart through abc-response, brand and price (old price crossed out
     when the second price differs). `$new` adds the «Новинка» badge. --}}
@props(['product', 'new' => false])

<article class="card">
    <div class="card-media">
        @if($product->url)
            <a href="{{ $product->url }}" aria-label="{{ $product->title }}"></a>
        @endif
        <img class="img-main" src="{{ $product->image }}" alt="{{ $product->title }}" loading="lazy">
        <img class="img-hover" src="{{ $product->hover_image ?? $product->image }}" alt="" loading="lazy">

        <div class="card-badges">
            @if($product->discount_percent)
                <span class="card-badge sale">−{{ $product->discount_percent }}%</span>
            @endif
            @if($new)
                <span class="card-badge new">{{ t('home.badge_new') }}</span>
            @elseif($product->is_hit)
                <span class="card-badge">{{ t('home.badge_hit') }}</span>
            @endif
        </div>

        <button class="card-fav" type="button" aria-label="{{ t('home.to_favorites') }}"><i class="bi bi-heart"></i></button>

        <form class="card-add-form" data-abcr="{{ save_route('change_product_count') }}" action="{{ save_route('change_product_count') }}" method="POST">
            @csrf
            <input type="hidden" name="id" value="{{ $product->id }}">
            <input type="hidden" name="count" value="1">
            <input type="hidden" name="action" value="{{ \App\Modules\ShopCart\Enums\CartAction::ChangeCount->value }}">
            <button class="card-add" type="submit"><i class="bi bi-bag-plus"></i> {{ t('home.add_to_cart') }}</button>
        </form>
    </div>
    <div class="card-body">
        @if($product->brand_title)
            <div class="card-brand">{{ $product->brand_title }}</div>
        @endif
        <h3 class="card-title"><a href="{{ $product->url ?? '#' }}">{{ $product->title }}</a></h3>
        <div class="card-meta">
            <div class="card-price">
                @if($product->has_discount)
                    <ins>{{ $product->price_formatted }}</ins><del>{{ $product->old_price_formatted }}</del>
                @else
                    {{ $product->price_formatted }}
                @endif
            </div>
        </div>
    </div>
</article>
