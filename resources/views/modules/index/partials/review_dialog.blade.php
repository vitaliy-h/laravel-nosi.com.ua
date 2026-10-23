{{-- «Leave a review» dialog of the home page reviews block. Submitted through
     abc-response: validation errors land under the fields, success swaps
     #review-dialog-body for the thank-you message. --}}
@php($authUser = auth()->user())

<dialog class="modal review-dialog" id="review-dialog">
    <div id="review-dialog-body">
        <h3>{{ t('home.review_form_title') }}</h3>
        @if(t('home.review_form_hint') !== '')
            <p>{{ t('home.review_form_hint') }}</p>
        @endif

        <form
            class="form-stack"
            id="review-form"
            data-abcr="{{ save_route('review.store') }}"
            action="{{ save_route('review.store') }}"
            method="POST"
        >
            @csrf

            <div class="field">
                <span class="label">{{ t('home.review_rating') }}</span>
                <div class="star-input">
                    @for($stars = 5; $stars >= 1; $stars--)
                        <input type="radio" id="review-star{{ $stars }}" name="rating" value="{{ $stars }}" @checked(old('rating', 5) == $stars)>
                        <label for="review-star{{ $stars }}" aria-label="{{ $stars }}"><i class="bi bi-star-fill"></i></label>
                    @endfor
                </div>
            </div>

            <div class="form-grid">
                {{-- Readonly (not disabled) for logged-in users: disabled fields are not
                     submitted. The server overwrites both from the account anyway. --}}
                <div class="field">
                    <label for="review-name">{{ t('home.review_name') }}</label>
                    <input type="text" class="input" id="review-name" name="name" value="{{ old('name', $authUser?->name) }}" @readonly($authUser !== null) required>
                </div>
                <div class="field">
                    <label for="review-city">{{ t('home.review_city') }}</label>
                    <input type="text" class="input" id="review-city" name="city" value="{{ old('city') }}">
                </div>
            </div>

            <div class="field">
                <label for="review-email">{{ t('home.review_email') }}</label>
                <input type="email" class="input" id="review-email" name="email" value="{{ old('email', $authUser?->email) }}" @readonly($authUser !== null) required>
            </div>

            <div class="field">
                <label for="review-text">{{ t('home.review_text') }}</label>
                <textarea class="input" id="review-text" name="text" rows="4" required>{{ old('text') }}</textarea>
            </div>

            <div class="modal-actions">
                <button type="button" class="btn btn-line btn-sm" data-dialog-close>{{ t('buttons.cancel') }}</button>
                <button type="submit" class="btn btn-dark btn-sm">{{ t('home.review_submit') }}</button>
            </div>
        </form>
    </div>
</dialog>
