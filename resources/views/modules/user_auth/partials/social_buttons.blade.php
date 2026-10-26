@if(!empty(config('social.providers')))
    <div class="social-divider">{{ t('auth.or_continue_with') }}</div>

    <div class="social-buttons">
        @foreach(config('social.providers') as $provider)
            <a href="{{ route('social.redirect', $provider) }}" class="btn btn-line">
                <i class="bi bi-{{ $provider }}"></i>
                {{ __('site.social.sign_in_with', ['provider' => __('site.social.' . $provider)]) }}
            </a>
        @endforeach
    </div>
@endif
