<div class="product-reviews">

    <div class="panel rv-summary">
        <div class="rv-score">
            <strong>{{ $rating->average_formatted }}</strong>
            <div class="stars"><x-rating-stars :value="$rating->average" /></div>
            <small>{{ t('shop.based_on_product_reviews', ['count' => $rating->total]) }}</small>
        </div>

        <div class="rv-bars">
            @for($stars = 5; $stars >= 1; $stars--)
                <div class="rv-bar">
                    <span>{{ $stars }} {{ $stars === 1 ? t('shop.star') : t('shop.stars') }}</span>
                    <div class="track"><div class="fill" style="width: {{ $rating->percentFor($stars) }}%;"></div></div>
                    <span>{{ $rating->countFor($stars) }}</span>
                </div>
            @endfor
        </div>
    </div>

    <div class="rv-columns">
        <div>
            <h3>{{ t('shop.customer_product_reviews') }}</h3>

            <div id="reviews-list" style="margin-top: 16px;">
                @forelse($reviews as $review)
                    @include('modules.shop.partials.review_item')
                @empty
                    <p class="muted">{{ t('shop.no_product_reviews') }}</p>
                @endforelse
            </div>

            @include('modules.shop.partials.reviews_load_more')
        </div>

        @include('modules.shop.partials.review_form')
    </div>

</div>
