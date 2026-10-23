<aside class="account-menu">
    <h4>{{ $user->formatted_name }}</h4>

    <nav>
        <a href="{{ save_route('profile.edit') }}" @class(['is-active' => $active_section === 'edit'])>
            <i class="bi bi-gear"></i> {{ t('profile.account_settings') }}
        </a>
        <a href="{{ save_route('profile.orders') }}" @class(['is-active' => $active_section === 'orders'])>
            <i class="bi bi-bag"></i> {{ t('profile.orders') }}
        </a>
    </nav>

    <form method="POST" action="{{ route('logout') }}">
        @csrf
        <button type="submit"><i class="bi bi-box-arrow-right"></i> {{ t('buttons.logout') }}</button>
    </form>
</aside>
