<ul class="nav nav-tabs nav-tabs-bordered justify-content-center mb-4" role="tablist">
    <li class="nav-item" role="presentation">
        <a href="{{save_route('auth.index')}}?login"
           class="nav-link {{ $is_login ? 'active' : '' }}">
            <i class="bi bi-box-arrow-in-right me-1"></i>{{t('auth.login')}}
        </a>
    </li>
    <li class="nav-item" role="presentation">
        <a href="{{save_route('auth.index')}}?register"
           class="nav-link {{ $is_register ? 'active' : '' }}">
            <i class="bi bi-person-plus me-1"></i>{{t('auth.register')}}
        </a>
    </li>
</ul>
