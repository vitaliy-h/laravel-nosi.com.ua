{{-- One review. Also rendered standalone by ProductReviewController::list() (load more). --}}
<article class="rv-item">
    <div class="rv-item-head">
        <div>
            <b>{{ $review->author_name }}</b>
            <small>{{ $review->created_at_formatted }}</small>
        </div>
        <div class="stars"><x-rating-stars :value="$review->rating" /></div>
    </div>

    @if($review->text)
        {{-- nl2br over an escaped value: line breaks typed into the form survive, markup does not. --}}
        <p>{!! nl2br(e($review->text)) !!}</p>
    @endif
</article>
