<ul class="nav nav-tabs nav-tabs-bordered justify-content-center mb-4" role="tablist">
    <li class="nav-item" role="presentation">
        <a href="{{module_url(\App\Modules\Core\Enums\PageModule::AUTH,[],['login'=>null])}}"
           class="nav-link {{$mode == 'login' ? 'active' :''}}">
            <i class="bi bi-box-arrow-in-right me-1"></i>{{t('auth.login')}}
        </a>
    </li>
    <li class="nav-item" role="presentation">
        <a href="{{module_url(\App\Modules\Core\Enums\PageModule::AUTH,[],['register'=>null])}}"
           class="nav-link {{$mode == 'register' ? 'active' :''}}">
            <i class="bi bi-person-plus me-1"></i>{{t('auth.register')}}
        </a>
    </li>
</ul>
