{{-- One review. Also rendered standalone by ProductReviewController::list() (load more). --}}
<div class="review-item">

    <div class="review-header">

        <div class="reviewer-info">

            <div>
                <h5 class="reviewer-name">
                    {{ $review->author_name }}
                </h5>

                <div class="review-date">
                    {{ $review->created_at?->format('m/d/Y') }}
                </div>
            </div>

        </div>

        <div class="review-rating">
            <x-rating-stars :value="$review->rating" />
        </div>

    </div>

    @if($review->text)
        <div class="review-content">
            {{-- nl2br over an escaped value: line breaks typed into the form survive, markup does not. --}}
            <p>{!! nl2br(e($review->text)) !!}</p>
        </div>
    @endif

</div>
