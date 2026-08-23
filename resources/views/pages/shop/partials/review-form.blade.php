@php($authUser = auth()->user())

<div class="review-form-container">

    <h4>
        {{ t('shop.write_review') }}
    </h4>

    <p class="form-text mb-3">
        {{ t('shop.review_form_hint') }}
    </p>

    <form
        class="review-form"
        id="review-form"
        data-abcr="{{ save_route('shop.review.store') }}"
        action="{{ save_route('shop.review.store') }}"
        method="POST"
    >
        @csrf

        <input type="hidden" name="product_id" value="{{ $product->id }}">

        <div class="rating-select mb-4">

            <label class="form-label">
                {{ t('shop.your_rating') }}
            </label>

            <div class="star-rating">
                @for($stars = 5; $stars >= 1; $stars--)
                    <input
                        type="radio"
                        id="star{{ $stars }}"
                        name="rating"
                        value="{{ $stars }}"
                        @checked(old('rating') == $stars)
                    ><label for="star{{ $stars }}"><i class="bi bi-star-fill"></i></label>
                @endfor
            </div>

        </div>

        <div class="row g-3 mb-3">

            <div class="col-md-6">
                <label for="review-name" class="form-label">
                    {{ t('shop.your_name') }}
                </label>

                {{-- Readonly (not disabled) for logged-in users: disabled fields
                     are not submitted. The server overwrites both values from the
                     account anyway, this is only for convenience. --}}
                <input
                    type="text"
                    class="form-control"
                    id="review-name"
                    name="name"
                    value="{{ old('name', $authUser?->name) }}"
                    @readonly($authUser !== null)
                    required
                >
            </div>

            <div class="col-md-6">
                <label for="review-email" class="form-label">
                    {{ t('shop.your_email') }}
                </label>

                <input
                    type="email"
                    class="form-control"
                    id="review-email"
                    name="email"
                    value="{{ old('email', $authUser?->email) }}"
                    @readonly($authUser !== null)
                    required
                >
            </div>

        </div>

        <div class="mb-4">
            <label for="review-content" class="form-label">
                {{ t('shop.your_review') }}
            </label>

            <textarea
                class="form-control"
                id="review-content"
                name="text"
                rows="4"
                required
            >{{ old('text') }}</textarea>
        </div>

        <div class="d-grid">
            <button type="submit" class="btn btn-primary">
                {{ t('shop.submit_review') }}
            </button>
        </div>

    </form>

    <div id="review-message" class="mt-3"></div>

</div>
