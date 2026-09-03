{{--
    Load-more button for the reviews list. Rendered standalone by
    ProductReviewController::list() and swapped in via `replaceWith`, so the wrapper
    always stays in the DOM even when there is nothing more to load.

    abc-response.js turns data-* attributes into query params in camelCase,
    hence the short `data-product` / `data-page` names.
--}}
<div class="text-center mt-4" id="reviews-load-more">

    @if($reviews->hasMorePages())
        <button
            class="btn btn-outline-primary load-more-btn"
            data-abcr="{{ save_route('shop.product.review.list') }}"
            data-product="{{ $product->id }}"
            data-page="{{ $reviews->currentPage() + 1 }}"
        >
            {{ t('shop.load_more_product_reviews') }}
        </button>
    @endif

</div>
