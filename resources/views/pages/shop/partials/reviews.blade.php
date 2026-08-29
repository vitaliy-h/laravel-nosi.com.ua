<div class="product-reviews">

    <div class="reviews-summary">

        <div class="overall-rating">

            <div class="rating-number">
                {{ $rating->average_formatted }}
            </div>

            <div class="rating-stars">
                <x-rating-stars :value="$rating->average" />
            </div>

            <div class="rating-count">
                {{ t('shop.based_on_product_reviews', ['count' => $rating->total]) }}
            </div>

        </div>

        <div class="rating-breakdown">

            @for($stars = 5; $stars >= 1; $stars--)
                <div class="rating-bar">

                    <div class="rating-label">
                        {{ $stars }} {{ $stars === 1 ? t('shop.star') : t('shop.stars') }}
                    </div>

                    <div class="progress">
                        <div
                            class="progress-bar"
                            role="progressbar"
                            style="width: {{ $rating->percentFor($stars) }}%;"
                            aria-valuenow="{{ $rating->percentFor($stars) }}"
                            aria-valuemin="0"
                            aria-valuemax="100"
                        ></div>
                    </div>

                    <div class="rating-count">
                        {{ $rating->countFor($stars) }}
                    </div>

                </div>
            @endfor

        </div>

    </div>

    @include('pages.shop.partials.review-form')

    <div class="reviews-list mt-5">

        <h4>
            {{ t('shop.customer_product_reviews') }}
        </h4>

        <div id="reviews-list">
            @forelse($reviews as $review)
                @include('pages.shop.partials.review-item')
            @empty
                <p class="text-muted">
                    {{ t('shop.no_product_reviews') }}
                </p>
            @endforelse
        </div>

        @include('pages.shop.partials.reviews-load-more')

    </div>

</div>
