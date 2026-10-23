@extends('layouts.app')

@section('body-class', 'product-page')

@if($product->url)
    @section('canonical', url($product->url))
@endif

@php
    $productPrice = $product->has_discount ? $product->price2 : $product->price;

    $productSchema = array_filter([
        '@context'    => 'https://schema.org',
        '@type'       => 'Product',
        'name'        => $product->title,
        'image'       => array_map(fn (string $src) => url($src), $product->gallery ?: [$product->image]),
        'description' => $product->text ? Str::limit(trim(strip_tags($product->text)), 500) : null,
        'sku'         => $product->article,
        'category'    => $category->title ?? null,
        'brand'       => $product->brand_title ? ['@type' => 'Brand', 'name' => $product->brand_title] : null,
        'offers'      => $productPrice ? [
            '@type'         => 'Offer',
            'url'           => url($product->url ?? request()->url()),
            'price'         => number_format($productPrice, 2, '.', ''),
            'priceCurrency' => 'UAH',
            'availability'  => 'https://schema.org/InStock',
        ] : null,
    ]);
@endphp

@push('schema')
    <script type="application/ld+json">@json($productSchema, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES | JSON_HEX_TAG)</script>
@endpush

@section('content')
    @include('partials.page_title', ['hideTitle' => true])

    <div class="container page" style="padding-top: 0;">
        <div class="pd">

            <div class="pd-gallery" data-gallery>
                <div class="pd-main">
                    <img src="{{ $product->gallery[0] ?? $product->image }}" alt="{{ $product->title }}" data-gallery-main>
                    <div class="card-badges">
                        @if($product->discount_percent)
                            <span class="card-badge sale">−{{ $product->discount_percent }}%</span>
                        @endif
                        @if($product->is_hit)
                            <span class="card-badge">{{ t('home.badge_hit') }}</span>
                        @endif
                    </div>
                </div>

                @if(count($product->gallery) > 1)
                    <div class="pd-thumbs">
                        @foreach($product->gallery as $src)
                            <button type="button" @class(['is-active' => $loop->first]) data-gallery-thumb="{{ $src }}" aria-label="{{ $product->title }} {{ $loop->iteration }}">
                                <img src="{{ $src }}" alt="" loading="lazy">
                            </button>
                        @endforeach
                    </div>
                @endif
            </div>

            <div class="pd-info">
                <div class="pd-brand">{{ $product->brand_title ?? $category->title }}</div>
                <h1>{{ $product->title }}</h1>

                <div class="pd-price">
                    @if($product->has_discount)
                        <ins>{{ $product->price_formatted }}</ins><del>{{ $product->old_price_formatted }}</del>
                    @else
                        <span>{{ $product->price_formatted }}</span>
                    @endif
                </div>

                <div class="pd-stock"><i class="bi bi-check-circle-fill"></i> {{ t('shop.in_stock') }}</div>

                <form class="js-add-form" data-abcr="{{ save_route('change_product_count') }}" action="{{ save_route('change_product_count') }}" method="POST">
                    @csrf
                    <input type="hidden" name="id" value="{{ $product->id }}">
                    <input type="hidden" name="action" value="{{ \App\Modules\ShopCart\Enums\CartAction::ChangeCount->value }}">

                    <div class="pd-buy">
                        <div class="qty">
                            <button type="button" data-qty="-1" aria-label="−"><i class="bi bi-dash"></i></button>
                            <input type="number" name="count" value="{{ $cart_quantity }}" min="1" aria-label="{{ t('shop.quantity') }}">
                            <button type="button" data-qty="1" aria-label="+"><i class="bi bi-plus"></i></button>
                        </div>
                        <button type="submit" class="btn btn-dark" data-added="{{ t('shop.added_to_cart') }}"><i class="bi bi-bag-plus"></i> {{ t('shop.add_to_cart') }}</button>
                    </div>
                </form>

                <ul class="pd-perks">
                    @foreach([1, 2, 3, 4] as $i)
                        @if(t("benefits.benefit_{$i}_title") !== '')
                            <li><i class="bi {{ t("benefits.benefit_{$i}_icon") ?: 'bi-check2-circle' }}"></i> <span><b>{{ t("benefits.benefit_{$i}_title") }}</b> — {{ t("benefits.benefit_{$i}_text") }}</span></li>
                        @endif
                    @endforeach
                </ul>

                <div class="pd-section">
                    <h2>{{ t('shop.description') }}</h2>
                    <div class="content">
                        @if($product->text)
                            {!! $product->text !!}
                        @else
                            <p class="muted">{{ $product->title }}</p>
                        @endif
                    </div>
                </div>

                <div class="pd-section">
                    <h2>{{ t('shop.specifications') }}</h2>
                    <dl class="specs">
                        <dt>{{ t('shop.article') }}</dt>
                        <dd>{{ $product->article ?? '—' }}</dd>
                        <dt>{{ t('shop.category') }}</dt>
                        <dd><a class="link" href="{{ $category->url }}">{{ $category->title }}</a></dd>
                        @if($product->brand_title)
                            <dt>{{ t('shop.brand') }}</dt>
                            <dd>{{ $product->brand_title }}</dd>
                        @endif
                    </dl>
                </div>
            </div>

        </div>
    </div>
@endsection
