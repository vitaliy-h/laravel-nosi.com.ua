@php($authUser = auth()->user())

<div class="panel-white">
    <h3>{{ t('shop.write_product_review') }}</h3>
    <p class="muted" style="margin: -8px 0 20px; font-size: 14px;">{{ t('shop.product_review_form_hint') }}</p>

    <form
        class="form-stack"
        id="review-form"
        data-abcr="{{ save_route('shop.product.review.store') }}"
        action="{{ save_route('shop.product.review.store') }}"
        method="POST"
    >
        @csrf
        <input type="hidden" name="product_id" value="{{ $product->id }}">

        <div class="field">
            <span class="label">{{ t('shop.your_rating') }}</span>
            <div class="star-input">
                @for($stars = 5; $stars >= 1; $stars--)
                    <input type="radio" id="star{{ $stars }}" name="rating" value="{{ $stars }}" @checked(old('rating') == $stars)>
                    <label for="star{{ $stars }}" aria-label="{{ $stars }}"><i class="bi bi-star-fill"></i></label>
                @endfor
            </div>
        </div>

        <div class="form-grid">
            {{-- Readonly (not disabled) for logged-in users: disabled fields are not
                 submitted. The server overwrites both from the account anyway. --}}
            <div class="field">
                <label for="review-name">{{ t('shop.your_name') }}</label>
                <input type="text" class="input" id="review-name" name="name" value="{{ old('name', $authUser?->name) }}" @readonly($authUser !== null) required>
            </div>
            <div class="field">
                <label for="review-email">{{ t('shop.your_email') }}</label>
                <input type="email" class="input" id="review-email" name="email" value="{{ old('email', $authUser?->email) }}" @readonly($authUser !== null) required>
            </div>
        </div>

        <div class="field">
            <label for="review-content">{{ t('shop.your_product_review') }}</label>
            <textarea class="input" id="review-content" name="text" rows="4" required>{{ old('text') }}</textarea>
        </div>

        <button type="submit" class="btn btn-dark">{{ t('shop.submit_product_review') }}</button>
    </form>

    <div id="review-message" style="margin-top: 16px;"></div>
</div>
