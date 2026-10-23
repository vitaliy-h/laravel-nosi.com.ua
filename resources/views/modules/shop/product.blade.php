@extends('layouts.app')

@section('body-class', 'product-page')

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

                @if($product->reviews_count > 0)
                    <div class="pd-rating">
                        <x-rating-stars :value="$product->rating" />
                        <a href="#reviews">{{ t('shop.product_reviews_tab', ['count' => $product->reviews_count]) }}</a>
                    </div>
                @endif

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
            </div>

        </div>

        <div class="pd-tabs" data-tabs>
            <div class="tabs" role="tablist">
                <button type="button" class="is-active" role="tab" data-tab-target="description">{{ t('shop.description') }}</button>
                <button type="button" role="tab" data-tab-target="specifications">{{ t('shop.specifications') }}</button>
                @if(\App\Modules\Core\Support\ModuleGate::isEnabled('shop_product_reviews'))
                    <button type="button" role="tab" data-tab-target="reviews">{{ t('shop.product_reviews_tab', ['count' => $rating->total]) }}</button>
                @endif
            </div>

            <div data-tab-panel="description">
                <div class="content page-narrow">
                    @if($product->text)
                        {!! $product->text !!}
                    @else
                        <p class="muted">{{ $product->title }}</p>
                    @endif
                </div>
            </div>

            <div data-tab-panel="specifications" hidden>
                <dl class="specs">
                    <dt>{{ t('shop.article') }}</dt>
                    <dd>{{ $product->article ?? '—' }}</dd>
                    <dt>{{ t('shop.category') }}</dt>
                    <dd><a class="link" href="{{ $category->url }}">{{ $category->title }}</a></dd>
                    @if($product->brand_title)
                        <dt>{{ t('shop.brand') }}</dt>
                        <dd>{{ $product->brand_title }}</dd>
                    @endif
                    <dt>{{ t('shop.price') }}</dt>
                    <dd>{{ $product->price_formatted ?? '—' }}</dd>
                </dl>
            </div>

            @if(\App\Modules\Core\Support\ModuleGate::isEnabled('shop_product_reviews'))
                <div data-tab-panel="reviews" id="reviews" hidden>
                    @include('modules.shop.partials.reviews')
                </div>
            @endif
        </div>
    </div>
@endsection
