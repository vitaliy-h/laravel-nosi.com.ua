<nav class="tabs" aria-label="{{ t('auth.login') }} / {{ t('auth.register') }}">
    <a href="{{ save_route('auth.index') }}?login" @class(['is-active' => $is_login])>{{ t('auth.login') }}</a>
    <a href="{{ save_route('auth.index') }}?register" @class(['is-active' => $is_register])>{{ t('auth.register') }}</a>
</nav>
